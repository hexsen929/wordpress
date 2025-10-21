<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|客户端核心类（需加密）
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 * @Encrypt       : 此文件需使用 ionCube 加密
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 客户端授权核心类
 * 
 * 关键配置硬编码在类内部，防止被修改：
 * - API 地址
 * - 签名密钥前缀
 * - 缓存键名
 */
class MrheAuthClient
{
    private static $instance = null;
    
    //硬编码配置（加密后用户无法修改）
    private const API_BASE_URL = 'http://e.hexsen.com/wp-json/mrhe/v1/auth';
    private const SECRET_KEY_PREFIX = 'mrhe_2024_secret_v1';
    private const SECRET_KEY_SUFFIX = '_auth_system';
    private const CACHE_KEY = 'mrhe_post_autkey';
    private const PRODUCT_ID = 'mrhe_theme';
    
    /**
     * 获取单例实例
     * @return {MrheAuthClient} 实例
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 标准化域名（客户端工具函数）
     * @param {string} $url 原始URL
     * @return {string} 标准化后的域名
     */
    private function normalize_domain($url)
    {
        $url = trim($url);
        $url = str_replace(array('http://', 'https://', 'www.'), '', $url);
        $url = rtrim($url, '/');
        return $url;
    }
    
    /**
     * 计算签名（私有方法，加密后完全隐藏）
     * @param {string} $domain 域名
     * @param {int} $token Token
     * @param {string} $randstr 随机字符串
     * @param {string} $product_id 产品ID
     * @return {string} 签名
     */
    private function calculate_signature($domain, $token, $randstr, $product_id)
    {
        $data = $domain . '|' . $token . '|' . $randstr . '|' . $product_id;
        $secret_key = self::SECRET_KEY_PREFIX . '_' . $product_id . self::SECRET_KEY_SUFFIX;
        return hash_hmac('sha256', $data, $secret_key);
    }
    
    
    /**
     * 核心验证方法（参考 Zibll 机制）
     * 检查本地缓存 + 签名验证 + 30天轮换
     * @return {bool} 是否已授权
     */
    public function is_theme_authorized()
    {
        //检查本地缓存
        $auth_data = get_option(self::CACHE_KEY);
        
        if (!$auth_data || !is_array($auth_data)) {
            return false;
        }
        
        //检查必要字段
        if (!isset($auth_data['time']) || !isset($auth_data['token']) || !isset($auth_data['randstr']) || !isset($auth_data['product_id'])) {
            return false;
        }
        
        //检查授权是否过期
        if (time() > $auth_data['time']) {
            return false;
        }
        
        //验证 Token 签名
        $token = $auth_data['token'];
        $stored_signature = get_option('mrhe_post_zat_' . $token);
        
        if (!$stored_signature) {
            return false;
        }
        
        //重新计算签名验证（防篡改）
        $domain = $this->get_current_domain();
        $expected_signature = $this->calculate_signature(
            $domain,
            $token,
            $auth_data['randstr'],
            $auth_data['product_id']
        );
        
        if (!hash_equals($expected_signature, $stored_signature)) {
            //签名不匹配，清理缓存
            $this->clear_auth_cache();
            return false;
        }
        
        //检查是否需要重新验证（30天轮换）
        $next_verify = get_option('mrhe_post_zat_next', 0);
        if (time() > $next_verify) {
            $this->schedule_reverify();
        }
        
        return true;
    }
    
    /**
     * 自动授权（通过域名查询）
     * @return {array} 授权结果
     */
    public function auto_verify()
    {
        $domain = $this->get_current_domain();
        $product_id = self::PRODUCT_ID;
        
        $response = wp_remote_post(self::API_BASE_URL . '/verify', array(
            'body' => array(
                'domain' => $domain,
                'product_id' => $product_id
            ),
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'MrheAuthClient/1.0'
            )
        ));
        
        if (is_wp_error($response)) {
            // 网络错误：24小时后重试
            update_option('mrhe_post_zat_next', time() + (24 * 3600));
            return array(
                'success' => false,
                'message' => '网络请求失败：' . $response->get_error_message()
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($body['success']) {
            $this->save_auth_data($body['data']);
        } else {
            // 根据错误码决定处理策略
            $permanent_failure_codes = ['banned', 'domain_not_found', 'invalid_auth'];
            if (isset($body['code']) && in_array($body['code'], $permanent_failure_codes)) {
                $this->clear_auth_cache();
                // 设置永久禁止自动重试标记
                update_option('mrhe_post_zat_next', 9999999999);
            } else {
                // 临时失败：24小时后重试
                update_option('mrhe_post_zat_next', time() + (24 * 3600));
            }
        }
        
        return $body;
    }
    
    /**
     * 手动授权（使用授权码）
     * @param {string} $auth_code 授权码
     * @return {array} 授权结果
     */
    public function verify_auth_with_code($auth_code)
    {
        if (empty($auth_code)) {
            return array('success' => false, 'message' => '授权码不能为空');
        }
        
        $domain = $this->get_current_domain();
        $product_id = self::PRODUCT_ID;
        
        $response = wp_remote_post(self::API_BASE_URL . '/verify', array(
            'body' => array(
                'domain' => $domain,
                'product_id' => $product_id,
                'auth_code' => $auth_code
            ),
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'MrheAuthClient/1.0'
            )
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => '网络请求失败：' . $response->get_error_message()
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($body['success']) {
            $this->save_auth_data($body['data']);
        }
        
        return $body;
    }
    
    /**
     * 撤销授权
     * @return {array} 撤销结果
     */
    public function revoke_auth()
    {
        $this->clear_auth_cache();
        return array(
            'success' => true,
            'message' => '授权已撤销'
        );
    }
    
    /**
     * 保存授权数据到 Options
     * @param {array} $data 授权数据
     */
    private function save_auth_data($data)
    {
        //保存主数据
        update_option(self::CACHE_KEY, array(
            'time' => $data['time'],
            'token' => $data['token'],
            'randstr' => $data['randstr'],
            'product_id' => $data['product_id']
        ));
        
        //保存签名
        update_option('mrhe_post_zat_' . $data['token'], $data['signature']);
        
        //设置下次验证时间
        update_option('mrhe_post_zat_next', time() + (30 * 86400));
    }
    
    /**
     * 清理授权缓存
     */
    private function clear_auth_cache()
    {
        $auth_data = get_option(self::CACHE_KEY);
        if ($auth_data && isset($auth_data['token'])) {
            delete_option('mrhe_post_zat_' . $auth_data['token']);
        }
        delete_option(self::CACHE_KEY);
        delete_option('mrhe_post_zat_next');
    }
    
    /**
     * 获取当前域名
     * @return {string} 当前域名
     */
    private function get_current_domain()
    {
        return $this->normalize_domain(home_url());
    }
    
    /**
     * 安排重新验证（后台异步）
     * 30天轮换机制
     */
    private function schedule_reverify()
    {
        if (!wp_next_scheduled('mrhe_auto_reverify')) {
            wp_schedule_single_event(time() + 60, 'mrhe_auto_reverify');
        }
    }
    
    /**
     * 执行重新验证
     * @return {bool} 是否成功
     */
    public function do_reverify()
    {
        $domain = $this->get_current_domain();
        $product_id = self::PRODUCT_ID;
        
        $response = wp_remote_post(self::API_BASE_URL . '/reverify', array(
            'body' => array(
                'domain' => $domain,
                'product_id' => $product_id,
                'old_token' => $this->get_current_token()
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($body['success']) {
            $this->save_auth_data($body['data']);
            return true;
        }
        
        return false;
    }
    
    /**
     * 获取当前 Token
     * @return {int} 当前Token
     */
    private function get_current_token()
    {
        $auth_data = get_option(self::CACHE_KEY);
        return $auth_data['token'] ?? 0;
    }
    
    /**
     * 获取自动授权 key（支持多产品）
     * @return {string} 自动授权 key
     */
    public static function get_auto_key()
    {
        return 'mrhe_autoaut_' . self::PRODUCT_ID;
    }
    
    /**
     * 获取产品ID（公共方法，供外部使用）
     * @return {string} 产品ID
     */
    public static function get_product_id()
    {
        return self::PRODUCT_ID;
    }
}

//Cron 任务：自动重新验证
function mrhe_do_auto_reverify()
{
    $client = MrheAuthClient::getInstance();
    $client->do_reverify();
}
add_action('mrhe_auto_reverify', 'mrhe_do_auto_reverify');