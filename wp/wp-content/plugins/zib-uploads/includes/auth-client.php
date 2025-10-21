<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : 子比主题·功能增强插件
 * @Description   : 插件授权客户端类（加密文件）
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 插件授权客户端类
 * 负责与授权服务器通信，验证授权状态
 */
class ZibUploadsAuthClient
{
    private static $instance = null;
    
    //硬编码配置（加密后用户无法修改）
    private const API_BASE_URL = 'http://e.hexsen.com/wp-json/mrhe/v1/auth';
    private const SECRET_KEY_PREFIX = 'mrhe_2024_secret_v1';
    private const SECRET_KEY_SUFFIX = '_auth_system';
    private const CACHE_KEY = 'zibll_plugin_auth_cache';
    private const PRODUCT_ID = 'mrhe_plugins';
    
    /**
     * 获取单例实例
     * @return ZibUploadsAuthClient 实例
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 检查插件是否已授权
     * @return bool 是否已授权
     */
    public function is_authorized()
    {
        //检查本地缓存
        $auth_data = get_option(self::CACHE_KEY);
        if (!$auth_data || !is_array($auth_data)) {
            return false;
        }
        
        //检查缓存是否过期
        if (time() > $auth_data['time']) {
            $this->clear_auth_cache();
            return false;
        }
        
        //验证 Token 签名
        $token = $auth_data['token'];
        $stored_signature = get_option('zibll_plugin_auth_signature_' . $token);
        
        if (!$stored_signature) {
            $this->clear_auth_cache();
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
            $this->clear_auth_cache();
            return false;
        }
        
        //检查是否需要重新验证（30天轮换）
        $next_verify = get_option('zibll_plugin_auth_next', 0);
        if (time() > $next_verify) {
            $this->schedule_reverify();
        }
        
        return true;
    }
    
    /**
     * 自动授权验证（通过域名）
     * @return array 验证结果
     */
    public function auto_verify()
    {
        $domain = $this->get_current_domain();
        $product_id = self::PRODUCT_ID;
        
        $response = $this->make_auth_request('verify', array(
            'domain' => $domain,
            'product_id' => $product_id
        ));
        
        if ($response && $response['success']) {
            $this->save_auth_data($response['data']);
            return array(
                'success' => true,
                'message' => '授权验证成功'
            );
        }
        
        // 根据错误码决定处理策略
        $permanent_failure_codes = ['banned', 'domain_not_found', 'invalid_auth'];
        if (isset($response['code']) && in_array($response['code'], $permanent_failure_codes)) {
            $this->clear_auth_cache();
            // 设置永久禁止自动重试标记（设置为一个很远的未来时间）
            update_option('zibll_plugin_auth_next', 9999999999);
        } else {
            // 临时失败：24小时后重试
            update_option('zibll_plugin_auth_next', time() + (24 * 3600));
        }
        
        return array(
            'success' => false,
            'message' => $response['message'] ?? '授权验证失败',
            'code' => $response['code'] ?? 'unknown'
        );
    }
    
    /**
     * 使用授权码验证
     * @param string $auth_code 授权码
     * @return array 验证结果
     */
    public function verify_auth_with_code($auth_code)
    {
        $domain = $this->get_current_domain();
        $product_id = self::PRODUCT_ID;
        
        $response = $this->make_auth_request('verify', array(
            'domain' => $domain,
            'product_id' => $product_id,
            'auth_code' => $auth_code
        ));
        
        if ($response && $response['success']) {
            $this->save_auth_data($response['data']);
            return array(
                'success' => true,
                'message' => '授权码验证成功'
            );
        }
        
        return array(
            'success' => false,
            'message' => $response['message'] ?? '授权码验证失败'
        );
    }
    
    /**
     * 撤销授权
     * @return array 撤销结果
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
     * 获取当前域名
     * @return string 当前域名
     */
    private function get_current_domain()
    {
        // 使用与子主题相同的域名获取方式
        $url = home_url();
        $url = trim($url);
        $url = str_replace(array('http://', 'https://', 'www.'), '', $url);
        $url = rtrim($url, '/');
        return $url;
    }
    
    /**
     * 计算签名（私有方法，加密后完全隐藏）
     * @param string $domain 域名
     * @param int $token Token
     * @param string $randstr 随机字符串
     * @param string $product_id 产品ID
     * @return string 签名
     */
    private function calculate_signature($domain, $token, $randstr, $product_id)
    {
        $data = $domain . '|' . $token . '|' . $randstr . '|' . $product_id;
        $secret_key = self::SECRET_KEY_PREFIX . '_' . $product_id . self::SECRET_KEY_SUFFIX;
        return hash_hmac('sha256', $data, $secret_key);
    }
    
    /**
     * 向授权服务器发送请求
     * @param string $endpoint API 端点（verify 或 reverify）
     * @param array $data 请求数据
     * @return array|false 响应数据或false
     */
    private function make_auth_request($endpoint, $data)
    {
        $url = self::API_BASE_URL . '/' . $endpoint;
        
        $args = array(
            'body' => $data,
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'ZibUploadsPlugin/2.0'
            )
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        return $result ?: false;
    }
    
    /**
     * 保存授权数据到 Options
     * @param array $data 授权数据
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
        update_option('zibll_plugin_auth_signature_' . $data['token'], $data['signature']);
        
        //设置下次验证时间
        update_option('zibll_plugin_auth_next', time() + (30 * 86400));
    }
    
    /**
     * 清理授权缓存
     */
    private function clear_auth_cache()
    {
        $auth_data = get_option(self::CACHE_KEY);
        if ($auth_data && isset($auth_data['token'])) {
            delete_option('zibll_plugin_auth_signature_' . $auth_data['token']);
        }
        delete_option(self::CACHE_KEY);
        delete_option('zibll_plugin_auth_next');
    }
    
    /**
     * 安排重新验证
     */
    private function schedule_reverify()
    {
        // 使用 WordPress Cron 安排重新验证
        if (!wp_next_scheduled('zibll_plugin_auto_reverify')) {
            wp_schedule_single_event(time() + 300, 'zibll_plugin_auto_reverify');
        }
    }
    
    /**
     * 执行重新验证
     */
    public function do_reverify()
    {
        $this->auto_verify();
    }
    
    /**
     * 获取自动授权 key（支持多产品）
     * @return string 自动授权 key
     */
    public static function get_auto_key()
    {
        return 'zibll_plugin_autoaut_' . self::PRODUCT_ID;
    }
    
    /**
     * 获取产品ID（公共方法，供外部使用）
     * @return string 产品ID
     */
    public static function get_product_id()
    {
        return self::PRODUCT_ID;
    }
}

//Cron 任务：自动重新验证
function zibll_plugin_do_auto_reverify()
{
    $client = ZibUploadsAuthClient::getInstance();
    $client->do_reverify();
}
add_action('zibll_plugin_auto_reverify', 'zibll_plugin_do_auto_reverify');
