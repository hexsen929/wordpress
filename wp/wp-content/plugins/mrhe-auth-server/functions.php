<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|工具函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 计算授权签名（服务端+客户端一致）
 * @param {string} $domain 域名
 * @param {int} $token Token
 * @param {string} $randstr 随机字符串
 * @param {string} $product_id 产品ID
 * @return {string} 签名
 */
function mrhe_calculate_signature($domain, $token, $randstr, $product_id)
{
    $data = $domain . '|' . $token . '|' . $randstr . '|' . $product_id;
    $secret_key = 'mrhe_2024_secret_v1_' . $product_id . '_auth_system';
    return hash_hmac('sha256', $data, $secret_key);
}

/**
 * 标准化域名
 * @param {string} $url 原始URL
 * @return {string} 标准化后的域名
 */
function mrhe_get_replace_url($url)
{
    $url = trim($url);
    $url = str_replace(array('http://', 'https://', 'www.'), '', $url);
    $url = rtrim($url, '/');
    return $url;
}

/**
 * 检查域名是否存在于列表中
 * @param {string} $domain 要检查的域名
 * @param {array} $domain_list 域名列表
 * @return {bool} 是否存在
 */
function mrhe_domain_exists_in_list($domain, $domain_list)
{
    if (!is_array($domain_list)) {
        return false;
    }

    // 标准化要检查的域名（去掉 www）
    $clean_domain = str_replace('www.', '', $domain);

    foreach ($domain_list as $item) {
        // 标准化列表中的域名（去掉 www）
        $clean_item = str_replace('www.', '', $item);

        // 比较标准化后的域名
        if ($clean_domain === $clean_item) {
            return true;
        }
    }

    return false;
}

/**
 * 统计已使用域名数量（过滤 www 重复）
 * @param {array} $domains 域名列表
 * @return {int} 唯一域名数量
 */
function mrhe_count_used_domains($domains)
{
    if (!is_array($domains)) {
        return 0;
    }
    
    $unique_domains = array();
    foreach ($domains as $domain) {
        $clean = str_replace('www.', '', $domain);
        if (!in_array($clean, $unique_domains)) {
            $unique_domains[] = $clean;
        }
    }
    
    return count($unique_domains);
}

/**
 * 获取用于显示的域名列表（过滤 www）
 * @param {array} $domains 域名列表
 * @return {array} 显示用的域名列表
 */
function mrhe_get_display_domains($domains)
{
    if (!is_array($domains)) {
        return array();
    }
    
    $display = array();
    foreach ($domains as $domain) {
        if (strpos($domain, 'www.') !== 0) {
            $display[] = $domain;
        }
    }
    
    return $display;
}

/**
 * 验证域名格式是否合法
 * @param {string} $domain 要验证的域名
 * @return {bool} 是否合法
 */
function mrhe_is_valid_domain($domain)
{
    if (empty($domain)) {
        return false;
    }
    
    // 域名必须包含至少一个点
    if (strpos($domain, '.') === false) {
        return false;
    }
    
    // 使用正则表达式验证域名格式
    // 允许：字母、数字、连字符、点
    // 不允许：下划线开头/结尾，连续的点，特殊字符
    $pattern = '/^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/';
    
    if (!preg_match($pattern, $domain)) {
        return false;
    }
    
    // 验证顶级域名（最后一部分）至少2个字符
    $parts = explode('.', $domain);
    $tld = end($parts);
    if (strlen($tld) < 2) {
        return false;
    }
    
    return true;
}

/**
 * 动态获取 product_id
 * 优先从产品元数据获取，后备从订单表获取
 * @param int $post_id 产品ID
 * @param int $user_id 用户ID
 * @return string product_id
 */
function mrhe_get_dynamic_product_id($post_id, $user_id)
{
    global $wpdb;
    
    // 1. 优先从产品元数据获取（最新配置）
    $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    if (!empty($pay_mate['product_id'])) {
        return sanitize_key($pay_mate['product_id']);
    }
    
    // 2. 后备：从订单表获取（历史记录）
    $order = $wpdb->get_row($wpdb->prepare(
        "SELECT product_id FROM {$wpdb->zibpay_order} 
         WHERE user_id = %d AND post_id = %d AND status = 1 
         ORDER BY id DESC LIMIT 1",
        $user_id,
        $post_id
    ), ARRAY_A);
    
    if (!empty($order['product_id'])) {
        return sanitize_text_field($order['product_id']);
    }
    
    // 3. 默认：使用 post_id
    return 'post_' . $post_id;
}

/**
 * 生成唯一授权码
 * 
 * @param int $user_id 用户ID
 * @param int $post_id 产品ID
 * @return string 32位小写MD5授权码
 */
function mrhe_generate_auth_code($user_id, $post_id)
{
    return md5(uniqid() . microtime() . $user_id . $post_id);
}

/**
 * 获取用户的主题授权订单
 * @param int $user_id 用户ID
 * @param bool $only_authorized 是否只获取已授权的订单
 * @return array 订单数组
 */
function mrhe_get_user_theme_orders($user_id, $only_authorized = false)
{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';
    $order_table = $wpdb->zibpay_order;
    
    $where_clause = "WHERE a.user_id = %d";
    $params = array($user_id);
    
    if ($only_authorized) {
        // 显示已授权的记录（包括封禁的）
        $where_clause .= " AND a.is_authorized = 1";
    }
    
    $query = "SELECT a.*, o.order_num, o.pay_time, o.pay_price 
              FROM $table_name a 
              LEFT JOIN $order_table o ON a.user_id = o.user_id AND a.post_id = o.post_id 
              $where_clause 
              ORDER BY a.created_at DESC";
    
    $results = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);
    
    return $results ? $results : array();
}

/**
 * 获取用户的所有授权信息
 * @param int $user_id 用户ID
 * @return array 授权信息数组
 */
function mrhe_get_user_all_auth_info($user_id)
{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d AND is_authorized = 1",
        $user_id
    ), ARRAY_A);
    
    $auth_info = array();
    foreach ($results as $result) {
        $post_id = $result['post_id'];
        
        // 动态获取 product_id：优先从订单表，后备从产品元数据
        $product_id = mrhe_get_dynamic_product_id($post_id, $result['user_id']);
        
        // 只使用 post_id 作为唯一键
        $auth_info[$post_id] = array(
            'post_id' => $post_id,
            'product_id' => $product_id, // 使用最新的 product_id
            'auth_code' => $result['auth_code'],
            'domains' => maybe_unserialize($result['domain']),
            'max_domains' => (int) $result['aut_max_url'],
            'is_authorized' => (int) $result['is_authorized'],
            'is_banned' => (int) ($result['is_banned'] ?? 0)
        );
    }
    
    return $auth_info;
}

/**
 * 获取页面设置中的下载资源
 * 
 * 配置方式：
 * 1. 进入产品页面编辑器
 * 2. 找到"产品购买设置"部分
 * 3. 在"资源下载"中添加下载资源
 * 4. 设置下载地址、按钮文案、图标、颜色等
 * 
 * 支持的字段：
 * - link: 下载地址（必填）
 * - name: 按钮文案
 * - icon: 按钮图标（默认：fa fa-download）
 * - class: 按钮颜色样式（默认：b-theme）
 * - more: 资源备注（如密码）
 * - copy_key: 复制按钮名称
 * - copy_val: 复制内容
 */
function mrhe_get_download_resources($post_id) {
    if (empty($post_id)) {
        return array();
    }
    
    $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    if (empty($pay_mate) || !is_array($pay_mate)) {
        return array();
    }
    
    $download_resources = $pay_mate['pay_download'] ?? array();
    if (empty($download_resources) || !is_array($download_resources)) {
        return array();
    }
    
    
    // 过滤掉没有下载地址的资源，并验证数据完整性
    $valid_resources = array();
    foreach ($download_resources as $resource) {
        if (!empty($resource['link']) && is_array($resource)) {
            // 确保必要字段存在
            $valid_resource = array(
                'link' => sanitize_url($resource['link']),
                'name' => sanitize_text_field($resource['name'] ?? '资源下载'),
                'icon' => sanitize_text_field($resource['icon'] ?? 'fa fa-download'),
                'class' => sanitize_text_field($resource['class'] ?? 'b-theme'),
                'more' => sanitize_text_field($resource['more'] ?? ''),
                'copy_key' => sanitize_text_field($resource['copy_key'] ?? ''),
                'copy_val' => sanitize_text_field($resource['copy_val'] ?? ''),
            );
            
            $valid_resources[] = $valid_resource;
        }
    }
    
    return $valid_resources;
}
