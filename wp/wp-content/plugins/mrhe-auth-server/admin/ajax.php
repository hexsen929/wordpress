<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|用户中心AJAX处理
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 添加授权域名
 * 参数：new_aut_url, post_id, product_id, order_num, nonce
 */
function mrhe_user_add_aut()
{
    // 验证 nonce
    zib_ajax_verify_nonce();
    
    // 获取用户ID
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_send_json_error('请先登录');
    }
    
    // 获取参数
    $domains = isset($_POST['domains']) ? $_POST['domains'] : array();
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $product_id = isset($_POST['product_id']) ? trim($_POST['product_id']) : '';
    $order_num = isset($_POST['order_num']) ? trim($_POST['order_num']) : '';
    
    // 参数验证
    if (empty($domains) || !is_array($domains) || empty($post_id)) {
        zib_send_json_error('参数错误');
    }
    
    // 过滤空域名
    $domains = array_filter(array_map('trim', $domains));
    if (empty($domains)) {
        zib_send_json_error('请输入至少一个域名');
    }
    
    // 验证每个域名格式
    foreach ($domains as $domain) {
        $clean_domain = mrhe_get_replace_url($domain);
        if (!mrhe_is_valid_domain($clean_domain)) {
            zib_send_json_error('域名格式不正确：' . $domain);
        }
    }
    
    // 获取授权记录（只使用 user_id 和 post_id 查询，product_id 是动态的）
    global $wpdb;
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';
    $auth_record = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d AND post_id = %d",
        $user_id, $post_id
    ), ARRAY_A);
    
    if (!$auth_record) {
        zib_send_json_error('授权记录不存在');
    }
    
    // 获取现有域名
    $existing_domains = maybe_unserialize($auth_record['domain']);
    if (!is_array($existing_domains)) {
        $existing_domains = array();
    }
    
    // 检查域名配额
    $domain_count = mrhe_count_used_domains($existing_domains);
    $max_domains = (int)$auth_record['aut_max_url'];
    
    if ($max_domains > 0 && ($domain_count + count($domains)) > $max_domains) {
        zib_send_json_error('域名配额不足，最多可添加 ' . ($max_domains - $domain_count) . ' 个域名');
    }
    
    // 处理每个域名
    $new_domains = array();
    foreach ($domains as $domain) {
        $clean_domain = mrhe_get_replace_url($domain);
        
        // 检查是否已存在
        if (mrhe_domain_exists_in_list($clean_domain, $existing_domains)) {
            zib_send_json_error('域名已存在：' . $clean_domain);
        }
        
        // 添加域名和www版本
        $new_domains[] = $clean_domain;
        $new_domains[] = 'www.' . $clean_domain;
    }
    
    // 合并到现有域名列表
    $existing_domains = array_merge($existing_domains, $new_domains);
    
    // 更新数据库
    $updated = $wpdb->update(
        $table_name,
        array('domain' => serialize($existing_domains), 'updated_at' => current_time('mysql')),
        array('id' => $auth_record['id']),
        array('%s', '%s'),
        array('%d')
    );
    
    if ($updated === false) {
        zib_send_json_error('添加失败');
    }
    
    // 触发更新钩子
    do_action('mrhe_auth_updated', $user_id);
    do_action('mrhe_domain_updated', $user_id);
    
    zib_send_json_success(array(
        'msg' => '成功添加 ' . count($domains) . ' 个域名',
        'reload' => 1
    ));
}
add_action('wp_ajax_mrhe_user_add_aut', 'mrhe_user_add_aut');

/**
 * 更换授权域名
 * 参数：aut_url, new_aut_url, post_id, product_id, order_num, nonce
 */
function mrhe_user_replace_aut()
{
    // 验证 nonce
    zib_ajax_verify_nonce();
    
    // 获取用户ID
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_send_json_error('请先登录');
    }
    
    // 获取参数
    $aut_url = isset($_POST['aut_url']) ? trim($_POST['aut_url']) : '';
    $new_aut_url = isset($_POST['new_aut_url']) ? trim($_POST['new_aut_url']) : '';
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $product_id = isset($_POST['product_id']) ? trim($_POST['product_id']) : '';
    
    // 参数验证
    if (empty($aut_url) || empty($new_aut_url) || empty($post_id)) {
        zib_send_json_error('参数错误');
    }
    
    // 标准化域名
    $clean_old = mrhe_get_replace_url($aut_url);
    $clean_new = mrhe_get_replace_url($new_aut_url);
    
    // 验证新域名格式（旧域名可能无效，允许更换）
    if (!mrhe_is_valid_domain($clean_new)) {
        zib_send_json_error('新域名格式不正确');
    }
    
    // 获取授权记录（只使用 user_id 和 post_id 查询，product_id 是动态的）
    global $wpdb;
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';
    $auth_record = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d AND post_id = %d",
        $user_id, $post_id
    ), ARRAY_A);
    
    if (!$auth_record) {
        zib_send_json_error('授权记录不存在');
    }
    
    // 获取现有域名
    $domains = maybe_unserialize($auth_record['domain']);
    if (!is_array($domains)) {
        $domains = array();
    }
    
    // 检查旧域名是否存在
    if (!mrhe_domain_exists_in_list($clean_old, $domains)) {
        zib_send_json_error('旧域名不存在');
    }
    
    // 检查新域名是否已存在
    if (mrhe_domain_exists_in_list($clean_new, $domains)) {
        zib_send_json_error('新域名已存在');
    }
    
    // 替换域名
    $new_domains = array();
    foreach ($domains as $domain) {
        if ($domain === $clean_old || $domain === 'www.' . $clean_old) {
            continue;
        }
        $new_domains[] = $domain;
    }
    $new_domains[] = $clean_new;
    $new_domains[] = 'www.' . $clean_new;
    
    // 更新数据库
    $updated = $wpdb->update(
        $table_name,
        array('domain' => serialize($new_domains), 'updated_at' => current_time('mysql')),
        array('id' => $auth_record['id']),
        array('%s', '%s'),
        array('%d')
    );
    
    if ($updated === false) {
        zib_send_json_error('更换失败');
    }
    
    // 触发更新钩子
    do_action('mrhe_auth_updated', $user_id);
    do_action('mrhe_domain_updated', $user_id);
    
    zib_send_json_success(array(
        'msg' => '域名更换成功',
        'reload' => 1
    ));
}
add_action('wp_ajax_mrhe_user_replace_aut', 'mrhe_user_replace_aut');

/**
 * 刷新授权信息
 * 参数：post_id, product_id, order_num, nonce
 */
function mrhe_user_refresh_aut()
{
    // 验证 nonce
    zib_ajax_verify_nonce();
    
    // 获取用户ID
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_send_json_error('请先登录');
    }
    
    // 函数节流 - 60秒限制
    zib_ajax_debounce('mrhe_refresh_aut', $user_id, 60, '刷新过于频繁，请%time%秒后再试');
    
    // 触发更新钩子
    do_action('mrhe_auth_updated', $user_id);
    do_action('mrhe_domain_updated', $user_id);
    
    zib_send_json_success(array(
        'msg' => '授权信息已刷新',
        'reload' => 1
    ));
}
add_action('wp_ajax_mrhe_user_refresh_aut', 'mrhe_user_refresh_aut');