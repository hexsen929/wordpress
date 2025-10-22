<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|服务端API核心类
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 服务端授权API核心类
 */
class MrheAuthServer
{
    private static $instance = null;
    
    /**
     * 获取单例实例
     * @return {MrheAuthServer} 实例
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 检查请求频率限制 - 改进版(多维度验证)
     * @param string $domain 请求的域名
     * @param string $product_id 产品ID
     * @return {array} 检查结果
     */
    private function check_rate_limit($domain = '', $product_id = '')
    {
        // 获取客户端IP
        $client_ip = $this->get_client_ip();

        // 获取User-Agent
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? md5($_SERVER['HTTP_USER_AGENT']) : 'unknown';

        // 生成多维度频率限制键
        // 1. IP级别限制 - 防止单个IP大量请求
        $ip_rate_key = 'mrhe_auth_rate_ip_' . md5($client_ip);
        // 2. IP+UA级别限制 - 防止同一客户端频繁请求
        $client_rate_key = 'mrhe_auth_rate_client_' . md5($client_ip . $user_agent);
        // 3. IP+域名级别限制 - 防止针对特定域名的攻击
        $domain_rate_key = 'mrhe_auth_rate_domain_' . md5($client_ip . $domain);

        // 检查IP级别限制（每分钟最多20次）
        $ip_requests = get_transient($ip_rate_key);
        if ($ip_requests && $ip_requests >= 20) {
            // 记录可疑行为
            error_log("MRHE授权频率限制: IP={$client_ip} 超过IP级别限制");
            return array(
                'success' => false,
                'code' => 'rate_limit',
                'message' => '请求过于频繁，请稍后再试'
            );
        }

        // 检查客户端级别限制（每分钟最多10次）
        $client_requests = get_transient($client_rate_key);
        if ($client_requests && $client_requests >= 10) {
            // 记录可疑行为
            error_log("MRHE授权频率限制: IP={$client_ip}, UA={$user_agent} 超过客户端级别限制");
            return array(
                'success' => false,
                'code' => 'rate_limit',
                'message' => '请求过于频繁，请稍后再试'
            );
        }

        // 检查域名级别限制（每分钟最多5次）
        if ($domain) {
            $domain_requests = get_transient($domain_rate_key);
            if ($domain_requests && $domain_requests >= 5) {
                // 记录可疑行为
                error_log("MRHE授权频率限制: IP={$client_ip}, Domain={$domain} 超过域名级别限制");
                return array(
                    'success' => false,
                    'code' => 'rate_limit',
                    'message' => '该域名请求过于频繁，请稍后再试'
                );
            }
            // 增加域名级别计数
            set_transient($domain_rate_key, ($domain_requests ? $domain_requests + 1 : 1), 60);
        }

        // 增加IP级别计数（60秒过期）
        set_transient($ip_rate_key, ($ip_requests ? $ip_requests + 1 : 1), 60);

        // 增加客户端级别计数（60秒过期）
        set_transient($client_rate_key, ($client_requests ? $client_requests + 1 : 1), 60);

        return array('success' => true);
    }

    /**
     * 获取客户端真实IP
     * @return {string} IP地址
     */
    private function get_client_ip()
    {
        // 优先获取代理后的真实IP
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '0.0.0.0';
        }

        return trim($ip);
    }
    
    /**
     * 注册 REST API 路由
     */
    public function register_rest_routes()
    {
        register_rest_route('mrhe/v1', '/auth/verify', array(
            'methods' => 'POST',
            'callback' => array($this, 'verify_auth'),
            'permission_callback' => '__return_true',
            'args' => array(
                'domain' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'product_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'auth_code' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
        
        register_rest_route('mrhe/v1', '/auth/reverify', array(
            'methods' => 'POST',
            'callback' => array($this, 'reverify_auth'),
            'permission_callback' => '__return_true',
            'args' => array(
                'domain' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'product_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'old_token' => array(
                    'required' => false,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
    }
    
    /**
     * 验证授权（客户端自动调用）
     * @param {WP_REST_Request|array|string} $request 请求对象、参数数组或域名
     * @param {string} $product_id 产品ID（当第一个参数是字符串时使用）
     * @param {string} $auth_code 授权码（可选）
     * @return {array} 验证结果
     */
    public function verify_auth($request, $product_id = '', $auth_code = '')
    {
        // 兼容不同的调用方式 - 先解析参数
        if (is_string($request)) {
            // 直接传递字符串参数：verify_auth($domain, $product_id, $auth_code)
            $domain = $request;
        } elseif (is_array($request)) {
            // 传递参数数组：verify_auth(['domain' => 'xxx', 'product_id' => 'xxx'])
            $domain     = $request['domain'] ?? '';
            $product_id = $request['product_id'] ?? '';
            $auth_code  = $request['auth_code'] ?? '';
        } else {
            // WP_REST_Request 对象
            $domain     = $request['domain'];
            $product_id = $request['product_id'];
            $auth_code  = isset($request['auth_code']) ? $request['auth_code'] : '';
        }

        // 频率限制：防止暴力攻击 - 传入域名和产品ID进行多维度限制
        $rate_limit_result = $this->check_rate_limit($domain, $product_id);
        if (!$rate_limit_result['success']) {
            return $rate_limit_result;
        }
        
        //标准化域名
        $clean_domain = mrhe_get_replace_url($domain);
        
        if (empty($clean_domain)) {
            return array(
                'success' => false,
                'code' => 'invalid_domain',
                'message' => '域名格式错误'
            );
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'mrhe_theme_aut';
        
        // 查询授权记录 - 修复SQL注入漏洞并优化性能
        if ($auth_code) {
            // 通过授权码查询
            $auth_record = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE auth_code = %s AND is_authorized = 1 AND is_banned = 0",
                $auth_code
            ), ARRAY_A);

            // 检查数据库错误
            if ($wpdb->last_error) {
                error_log('MRHE授权查询错误: ' . $wpdb->last_error);
                return array(
                    'success' => false,
                    'code' => 'database_error',
                    'message' => '数据库查询失败，请稍后重试'
                );
            }
        } else {
            // 通过域名查询 - 使用LIKE查询优化性能
            // 注意: 这里使用LIKE是因为domain字段存储的是序列化数组
            $auth_records = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name
                 WHERE is_authorized = 1
                 AND is_banned = 0
                 AND domain LIKE %s
                 LIMIT 50",
                '%' . $wpdb->esc_like($clean_domain) . '%'
            ), ARRAY_A);

            // 检查数据库错误
            if ($wpdb->last_error) {
                error_log('MRHE授权查询错误: ' . $wpdb->last_error);
                return array(
                    'success' => false,
                    'code' => 'database_error',
                    'message' => '数据库查询失败，请稍后重试'
                );
            }

            $auth_record = null;
            foreach ($auth_records as $record) {
                // 动态获取 product_id
                $current_product_id = mrhe_get_dynamic_product_id($record['post_id'], $record['user_id']);

                // 检查 product_id 是否匹配
                if ($current_product_id === $product_id) {
                    // 精确检查域名是否匹配（防止LIKE误匹配）
                    $domains = maybe_unserialize($record['domain']);
                    if (mrhe_domain_exists_in_list($clean_domain, $domains)) {
                        $auth_record = $record;
                        break;
                    }
                }
            }
        }
        
        // 如果找到授权记录，检查封禁状态
        if ($auth_record) {
            // 优先检查是否被封禁（封禁提示优先级高于其他错误）
            if (!empty($auth_record['is_banned'])) {
                return array(
                    'success' => false,
                    'code' => 'banned',
                    'message' => '您的授权已被封禁，请联系管理员'
                );
            }
        }
        
        if (!$auth_record) {
            return array(
                'success' => false,
                'code' => 'domain_not_found',
                'message' => '未找到授权信息，请先在用户中心添加域名'
            );
        }
        
        //生成动态 Token（6位数字，避免碰撞）
        $token = rand(100000, 999999);
        
        //生成随机字符串
        $randstr = md5(uniqid() . microtime());
        
        //计算签名
        $signature = mrhe_calculate_signature($clean_domain, $token, $randstr, $product_id);
        
        //返回授权数据
        return array(
            'success' => true,
            'message' => '授权验证成功',
            'data' => array(
                'token' => $token,
                'randstr' => $randstr,
                'time' => time() + (30 * 86400), //30天过期
                'product_id' => $product_id,
                'signature' => $signature
            )
        );
    }
    
    /**
     * 重新验证授权（30天轮换）
     * @param {WP_REST_Request} $request 请求对象
     * @return {array} 验证结果
     */
    public function reverify_auth($request)
    {
        //逻辑同 verify_auth，生成新 Token
        return $this->verify_auth($request);
    }
}

/**
 * 初始化服务端
 */
function mrhe_init_auth_server()
{
    $server = MrheAuthServer::getInstance();
    add_action('rest_api_init', array($server, 'register_rest_routes'));
}
add_action('init', 'mrhe_init_auth_server');