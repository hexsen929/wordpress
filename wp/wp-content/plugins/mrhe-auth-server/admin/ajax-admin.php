<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|管理后台AJAX处理器
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 验证管理员权限
 */
function mrhe_admin_verify_permission()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }
}

/**
 * 统一返回格式
 */
function mrhe_admin_send_json($code = 0, $data = null, $msg = 'success')
{
    wp_send_json([
        'code' => $code,
        'data' => $data,
        'msg' => $msg
    ]);
}

/**
 * 获取统计数据
 */
function mrhe_admin_get_auth_stats()
{
    mrhe_admin_verify_permission();
    
    if (!wp_verify_nonce($_POST['nonce'], 'mrhe_admin_auth_nonce')) {
        mrhe_admin_send_json(1, null, '安全验证失败');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';

    $stats = [
        'total' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name"),
        'banned' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE is_banned = 1"),
        'inactive' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE is_authorized = 0"),
        'with_domains' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE domain IS NOT NULL AND domain != '' AND domain != 'a:0:{}'")
    ];

    mrhe_admin_send_json(0, $stats);
}
add_action('wp_ajax_mrhe_admin_get_auth_stats', 'mrhe_admin_get_auth_stats');

/**
 * 获取授权列表
 */
function mrhe_admin_get_auth_list()
{
    mrhe_admin_verify_permission();
    
    if (!wp_verify_nonce($_POST['nonce'], 'mrhe_admin_auth_nonce')) {
        mrhe_admin_send_json(1, null, '安全验证失败');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';

    // 分页参数
    $page = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? max(1, (int)$_POST['per_page']) : 20;
    $offset = ($page - 1) * $per_page;

    // 搜索条件
    $where_conditions = ['1=1'];
    $where_values = [];

    if (!empty($_POST['keyword'])) {
        $keyword = '%' . $wpdb->esc_like($_POST['keyword']) . '%';
        $where_conditions[] = "(u.user_login LIKE %s OR u.user_email LIKE %s OR a.auth_code LIKE %s OR p.post_title LIKE %s)";
        $where_values = array_merge($where_values, [$keyword, $keyword, $keyword, $keyword]);
    }

    if (isset($_POST['status']) && $_POST['status'] !== '') {
        $where_conditions[] = "a.is_authorized = %d";
        $where_values[] = (int)$_POST['status'];
    }

    // 产品ID筛选
    if (!empty($_POST['product_id'])) {
        $where_conditions[] = "a.post_id = %d";
        $where_values[] = $_POST['product_id'];
    }

    $where_clause = implode(' AND ', $where_conditions);

    // 获取总数
    $count_sql = "SELECT COUNT(a.id) 
                  FROM $table_name a 
                  LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID 
                  LEFT JOIN {$wpdb->posts} p ON a.post_id = p.ID 
                  WHERE $where_clause";
    
    if (!empty($where_values)) {
        $count_sql = $wpdb->prepare($count_sql, $where_values);
    }
    $total = $wpdb->get_var($count_sql);

    // 获取数据
    $data_sql = "SELECT a.*, u.user_login, u.user_email, p.post_title as product_name
                 FROM $table_name a
                 LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID
                 LEFT JOIN {$wpdb->posts} p ON a.post_id = p.ID
                 WHERE $where_clause
                 ORDER BY a.created_at DESC
                 LIMIT %d OFFSET %d";

    $data_values = array_merge($where_values, [$per_page, $offset]);
    $data_sql = $wpdb->prepare($data_sql, $data_values);
    $results = $wpdb->get_results($data_sql, ARRAY_A);

    // 处理域名数据
    foreach ($results as &$item) {
        // 动态获取 product_id：优先从订单表，后备从产品元数据
        $item['product_id'] = mrhe_get_dynamic_product_id($item['post_id'], $item['user_id']);
        
        // 转换布尔字段为整数类型
        $item['is_authorized'] = (int)$item['is_authorized'];
        $item['is_banned'] = (int)$item['is_banned'];
        
        $domains = maybe_unserialize($item['domain']);
        if (is_array($domains)) {
            $valid_domains = array_filter($domains, function($domain) {
                return !empty($domain) && strpos($domain, 'www.') !== 0;
            });
            $item['domain_list'] = array_values($valid_domains);
            $item['domain_count'] = count($valid_domains);
        } else {
            $item['domain_list'] = [];
            $item['domain_count'] = 0;
        }
        $item['avatar'] = get_avatar_url($item['user_id'], ['size' => 32]);
    }

    mrhe_admin_send_json(0, [
        'list' => $results,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page
    ]);
}
add_action('wp_ajax_mrhe_admin_get_auth_list', 'mrhe_admin_get_auth_list');

/**
 * 获取产品列表
 */
function mrhe_admin_get_products()
{
    mrhe_admin_verify_permission();
    
    if (!wp_verify_nonce($_POST['nonce'], 'mrhe_admin_auth_nonce')) {
        mrhe_admin_send_json(1, null, '安全验证失败');
    }

    // 获取使用 mrhe_pay.php 模板的页面
    $products = get_posts([
        'post_type' => 'page',
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => '_wp_page_template',
                'value' => 'mrhe_pay.php',
                'compare' => '='
            ]
        ],
        'posts_per_page' => 50,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    $result = [];
    foreach ($products as $product) {
        // 检查是否启用了域名授权
        $pay_meta = get_post_meta($product->ID, 'posts_zibpay', true);
        $auth_enabled = false;
        
        if ($pay_meta && is_array($pay_meta)) {
            // 检查是否启用了域名授权功能
            $auth_enabled = !empty($pay_meta['auth_enabled']) || !empty($pay_meta['domain_auth_enabled']);
        }
        
        // 只有启用了域名授权的产品才显示
        if ($auth_enabled) {
            $result[] = [
                'ID' => $product->ID,
                'post_title' => $product->post_title,
                'meta' => [
                    'product_id' => 'post_' . $product->ID,
                    'auth_enabled' => true
                ]
            ];
        }
    }

    mrhe_admin_send_json(0, $result);
}
add_action('wp_ajax_mrhe_admin_get_products', 'mrhe_admin_get_products');

/**
 * 搜索用户
 */
function mrhe_admin_search_users()
{
    mrhe_admin_verify_permission();
    
    if (!wp_verify_nonce($_POST['nonce'], 'mrhe_admin_auth_nonce')) {
        mrhe_admin_send_json(1, null, '安全验证失败');
    }

    $keyword = sanitize_text_field($_POST['keyword']);
    if (empty($keyword)) {
        mrhe_admin_send_json(0, []);
    }

    $users = get_users([
        'search' => '*' . $keyword . '*',
        'search_columns' => ['user_login', 'user_email', 'display_name'],
        'number' => 20
    ]);

    $result = [];
    foreach ($users as $user) {
        $result[] = [
            'ID' => $user->ID,
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            'display_name' => $user->display_name,
            'avatar' => get_avatar_url($user->ID, ['size' => 32])
        ];
    }

    mrhe_admin_send_json(0, $result);
}
add_action('wp_ajax_mrhe_admin_search_users', 'mrhe_admin_search_users');

/**
 * 获取操作记录
 */
function mrhe_admin_get_operation_records()
{
    mrhe_admin_verify_permission();
    
    if (!wp_verify_nonce($_POST['nonce'], 'mrhe_admin_auth_nonce')) {
        mrhe_admin_send_json(1, null, '安全验证失败');
    }

    $auth_id = (int)$_POST['auth_id'];
    if (!$auth_id) {
        mrhe_admin_send_json(1, null, '参数错误');
    }

    // 模拟操作记录（实际项目中可以创建操作记录表）
    $records = [
        [
            'title' => '授权创建',
            'description' => '授权记录已创建',
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 'success'
        ],
        [
            'title' => '状态更新',
            'description' => '授权状态已更新',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'type' => 'info'
        ]
    ];

    mrhe_admin_send_json(0, $records);
}
add_action('wp_ajax_mrhe_admin_get_operation_records', 'mrhe_admin_get_operation_records');

/**
 * 添加授权
 */
function mrhe_admin_add_auth()
{
    mrhe_admin_verify_permission();
    
    if (!wp_verify_nonce($_POST['nonce'], 'mrhe_admin_auth_nonce')) {
        mrhe_admin_send_json(1, null, '安全验证失败');
    }

    $user_id = (int)$_POST['user_id'];
    $post_id = (int)$_POST['post_id'];
    $product_id = sanitize_text_field($_POST['product_id']);
    $aut_max_url = (int)$_POST['aut_max_url'];
    $is_authorized = (int)$_POST['is_authorized'];
    $auth_code = sanitize_text_field($_POST['auth_code']);
    $domains = isset($_POST['domains']) ? (array)$_POST['domains'] : [];

    if (!$user_id || !$post_id || !$aut_max_url) {
        mrhe_admin_send_json(1, null, '参数错误');
    }

    // 生成授权码
    if (empty($auth_code)) {
        $auth_code = mrhe_generate_auth_code($user_id, $post_id);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';

    // 检查是否已存在（使用 user_id 和 post_id 组合）
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE user_id = %d AND post_id = %d",
        $user_id, $post_id
    ));

    if ($exists) {
        mrhe_admin_send_json(1, null, '该用户已存在此产品的授权记录');
    }

    // 插入记录（不再包含 product_id 字段）
    $result = $wpdb->insert(
        $table_name,
        [
            'user_id' => $user_id,
            'post_id' => $post_id,
            'auth_code' => $auth_code,
            'domain' => maybe_serialize($domains),
            'is_authorized' => $is_authorized,
            'aut_max_url' => $aut_max_url,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ],
        ['%d', '%d', '%s', '%s', '%d', '%d', '%s', '%s']
    );

    if ($result === false) {
        mrhe_admin_send_json(1, null, '创建失败');
    }

    mrhe_admin_send_json(0, ['id' => $wpdb->insert_id], '创建成功');
}
add_action('wp_ajax_mrhe_admin_add_auth', 'mrhe_admin_add_auth');

/**
 * 更新授权
 */
function mrhe_admin_update_auth()
{
    mrhe_admin_verify_permission();
    
    if (!wp_verify_nonce($_POST['nonce'], 'mrhe_admin_auth_nonce')) {
        mrhe_admin_send_json(1, null, '安全验证失败');
    }

    $id = (int)$_POST['id'];
    $is_authorized = (int)$_POST['is_authorized'];
    $aut_max_url = (int)$_POST['aut_max_url'];
    $auth_code = sanitize_text_field($_POST['auth_code']);
    $domains = isset($_POST['domains']) ? (array)$_POST['domains'] : [];

    if (!$id) {
        mrhe_admin_send_json(1, null, '参数错误');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';

    $result = $wpdb->update(
        $table_name,
        [
            'is_authorized' => $is_authorized,
            'aut_max_url' => $aut_max_url,
            'auth_code' => $auth_code,
            'domain' => maybe_serialize($domains),
            'updated_at' => current_time('mysql')
        ],
        ['id' => $id],
        ['%d', '%d', '%s', '%s', '%s'],
        ['%d']
    );

    if ($result === false) {
        mrhe_admin_send_json(1, null, '更新失败');
    }

    mrhe_admin_send_json(0, null, '更新成功');
}
add_action('wp_ajax_mrhe_admin_update_auth', 'mrhe_admin_update_auth');

/**
 * 获取授权详情
 */
function mrhe_admin_get_auth_detail()
{
    mrhe_admin_verify_permission();
    
    if (!wp_verify_nonce($_POST['nonce'], 'mrhe_admin_auth_nonce')) {
        mrhe_admin_send_json(1, null, '安全验证失败');
    }

    $auth_id = isset($_POST['auth_id']) ? (int)$_POST['auth_id'] : 0;
    if (!$auth_id) {
        mrhe_admin_send_json(1, null, '参数错误');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';
    
    $auth_record = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $auth_id
    ), ARRAY_A);

    if (!$auth_record) {
        mrhe_admin_send_json(1, null, '授权记录不存在');
    }

    // 动态获取 product_id：优先从订单表，后备从产品元数据
    $auth_record['product_id'] = mrhe_get_dynamic_product_id($auth_record['post_id'], $auth_record['user_id']);

    // 处理域名数据
    $domains = maybe_unserialize($auth_record['domain']);
    // 添加类型转换
    $auth_record['is_authorized'] = (int)$auth_record['is_authorized'];
    $auth_record['is_banned'] = (int)$auth_record['is_banned'];
    
    if (is_array($domains)) {
        $valid_domains = array_filter($domains, function($domain) {
            return !empty($domain) && strpos($domain, 'www.') !== 0;
        });
        $auth_record['domain_list'] = array_values($valid_domains);
        $auth_record['domain_count'] = count($valid_domains);
    } else {
        $auth_record['domain_list'] = [];
        $auth_record['domain_count'] = 0;
    }

    // 添加用户和产品信息
    $user = get_user_by('id', $auth_record['user_id']);
    $product = get_post($auth_record['post_id']);
    
    $auth_record['user_login'] = $user ? $user->user_login : '';
    $auth_record['user_email'] = $user ? $user->user_email : '';
    $auth_record['product_name'] = $product ? $product->post_title : '';
    $auth_record['avatar'] = get_avatar_url($auth_record['user_id'], ['size' => 64]);

    mrhe_admin_send_json(0, $auth_record);
}
add_action('wp_ajax_mrhe_admin_get_auth_detail', 'mrhe_admin_get_auth_detail');

/**
 * 删除授权
 */
function mrhe_admin_delete_auth()
{
    mrhe_admin_verify_permission();
    
    if (!wp_verify_nonce($_POST['nonce'], 'mrhe_admin_auth_nonce')) {
        mrhe_admin_send_json(1, null, '安全验证失败');
    }

    $id = (int)$_POST['id'];
    if (!$id) {
        mrhe_admin_send_json(1, null, '参数错误');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';

    $result = $wpdb->delete($table_name, ['id' => $id], ['%d']);

    if ($result === false) {
        mrhe_admin_send_json(1, null, '删除失败');
    }

    mrhe_admin_send_json(0, null, '删除成功');
}
add_action('wp_ajax_mrhe_admin_delete_auth', 'mrhe_admin_delete_auth');

/**
 * 批量删除授权
 */
function mrhe_admin_batch_delete_auth()
{
    mrhe_admin_verify_permission();
    
    if (!wp_verify_nonce($_POST['nonce'], 'mrhe_admin_auth_nonce')) {
        mrhe_admin_send_json(1, null, '安全验证失败');
    }

    $ids = isset($_POST['ids']) ? (array)$_POST['ids'] : [];
    if (empty($ids)) {
        mrhe_admin_send_json(1, null, '参数错误');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';

    $placeholders = implode(',', array_fill(0, count($ids), '%d'));
    $result = $wpdb->query($wpdb->prepare(
        "DELETE FROM $table_name WHERE id IN ($placeholders)",
        $ids
    ));

    if ($result === false) {
        mrhe_admin_send_json(1, null, '批量删除失败');
    }

    mrhe_admin_send_json(0, null, "成功删除 {$result} 条记录");
}
add_action('wp_ajax_mrhe_admin_batch_delete_auth', 'mrhe_admin_batch_delete_auth');

/**
 * 封禁授权
 */
function mrhe_admin_ban_auth()
{
    mrhe_admin_verify_permission();
    
    if (!wp_verify_nonce($_POST['nonce'], 'mrhe_admin_auth_nonce')) {
        mrhe_admin_send_json(1, null, '安全验证失败');
    }

    $id = (int)$_POST['id'];
    if (!$id) {
        mrhe_admin_send_json(1, null, '参数错误');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';

    // 封禁：只设置封禁标志，保留域名数据
    $result = $wpdb->update(
        $table_name,
        [
            'is_banned' => 1,
            'updated_at' => current_time('mysql')
        ],
        ['id' => $id],
        ['%d', '%s'],
        ['%d']
    );

    if ($result === false) {
        mrhe_admin_send_json(1, null, '封禁失败');
    }

    mrhe_admin_send_json(0, null, '授权已封禁');
}
add_action('wp_ajax_mrhe_admin_ban_auth', 'mrhe_admin_ban_auth');

/**
 * 解封授权
 */
function mrhe_admin_unban_auth()
{
    mrhe_admin_verify_permission();
    
    if (!wp_verify_nonce($_POST['nonce'], 'mrhe_admin_auth_nonce')) {
        mrhe_admin_send_json(1, null, '安全验证失败');
    }

    $id = (int)$_POST['id'];
    if (!$id) {
        mrhe_admin_send_json(1, null, '参数错误');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';

    // 解封：清除封禁标志
    $result = $wpdb->update(
        $table_name,
        [
            'is_banned' => 0,
            'updated_at' => current_time('mysql')
        ],
        ['id' => $id],
        ['%d', '%s'],
        ['%d']
    );

    if ($result === false) {
        mrhe_admin_send_json(1, null, '解封失败');
    }

    mrhe_admin_send_json(0, null, '授权已解封');
}
add_action('wp_ajax_mrhe_admin_unban_auth', 'mrhe_admin_unban_auth');