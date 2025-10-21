<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|统一数据库管理
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 创建授权表
 */
function mrhe_create_auth_table()
{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL COMMENT '购买用户ID',
        post_id bigint(20) DEFAULT 0 COMMENT '产品页面ID',
        auth_code varchar(255) NOT NULL COMMENT '授权码',
        domain text COMMENT '授权域名列表（序列化数组）',
        is_authorized tinyint(1) DEFAULT 1 COMMENT '是否已授权',
        is_banned tinyint(1) DEFAULT 0 COMMENT '是否被封禁',
        aut_max_url int(11) DEFAULT 3 COMMENT '最大可授权域名数',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY idx_user_post (user_id, post_id),
        KEY idx_auth_code (auth_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='mrhe主题授权表';";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * 检查并创建必要的数据库表
 */
function mrhe_check_and_create_tables()
{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';
    
    //检查表是否存在
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    
    if (!$table_exists) {
        mrhe_create_auth_table();
        return true; //表已创建
    }
    
    return false; //表已存在
}

/**
 * 添加 is_banned 字段到授权表
 */
function mrhe_add_banned_field()
{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';
    
    // 检查字段是否已存在
    $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'is_banned'");
    
    if (empty($column_exists)) {
        // 添加 is_banned 字段
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN is_banned tinyint(1) DEFAULT 0 COMMENT '是否被封禁' AFTER is_authorized");
        return true;
    }
    
    return false; // 字段已存在
}

/**
 * 删除授权表的 product_id 字段和相关索引
 * 实现 Single Source of Truth 原则
 */
function mrhe_remove_product_id_redundancy()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';
    
    // 检查表是否存在
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    if (!$table_exists) {
        return;
    }
    
    // 检查 product_id 字段是否存在
    $column_exists = $wpdb->query("SHOW COLUMNS FROM `$table_name` LIKE 'product_id'");
    if (empty($column_exists)) {
        // 字段不存在，无需删除
        return;
    }
    
    // 删除复合索引 idx_user_product（如果存在）
    $index_exists = $wpdb->query("SHOW INDEX FROM `$table_name` WHERE Key_name = 'idx_user_product'");
    if ($index_exists) {
        $wpdb->query("ALTER TABLE `$table_name` DROP INDEX `idx_user_product`");
    }
    
    // 删除 product_id 字段
    $wpdb->query("ALTER TABLE `$table_name` DROP COLUMN `product_id`");
    
    // 添加新的唯一索引（只使用 user_id + post_id）
    $new_index_exists = $wpdb->query("SHOW INDEX FROM `$table_name` WHERE Key_name = 'idx_user_post'");
    if (!$new_index_exists) {
        $wpdb->query("ALTER TABLE `$table_name` ADD UNIQUE INDEX `idx_user_post` (`user_id`, `post_id`)");
    }
}

/**
 * 迁移现有授权记录（已废弃，因为删除了 product_id 字段）
 * 保留函数以避免错误，但不再执行任何操作
 */
function mrhe_migrate_auth_records()
{
    // 由于删除了 product_id 字段，此迁移不再需要
    return array(
        'success' => true,
        'updated_count' => 0,
        'total_records' => 0,
        'errors' => array()
    );
}

/**
 * 检查是否需要迁移（已废弃，因为删除了 product_id 字段）
 */
function mrhe_check_migration_needed()
{
    // 由于删除了 product_id 字段，不再需要迁移
    return false;
}

/**
 * 统一的数据库初始化函数
 */
function mrhe_init_database()
{
    // 1. 检查并创建表
    mrhe_check_and_create_tables();
    
    // 2. 添加封禁字段
    mrhe_add_banned_field();
    
    // 3. 执行数据迁移
    if (mrhe_check_migration_needed()) {
        $result = mrhe_migrate_auth_records();
        if (!$result['success']) {
            // 记录错误到日志
            error_log('MRHE数据库迁移失败: ' . $result['message']);
        }
    }
    
    // 4. 删除冗余字段（在迁移完成后）
    mrhe_remove_product_id_redundancy();
}

/**
 * AJAX处理函数：检查数据库表
 */
function mrhe_ajax_check_database_table()
{
    $created = mrhe_check_and_create_tables();
    wp_send_json_success(array(
        'table_created' => $created,
        'message' => $created ? '数据库表已创建' : '数据库表已存在'
    ));
}
add_action('wp_ajax_mrhe_check_database_table', 'mrhe_ajax_check_database_table');

/**
 * AJAX处理函数：执行数据迁移
 */
function mrhe_ajax_migrate_auth_records()
{
    // 验证权限
    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }
    
    // 验证 nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mrhe_migrate_nonce')) {
        wp_send_json_error('安全验证失败');
    }
    
    $result = mrhe_migrate_auth_records();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => "迁移完成，更新了 {$result['updated_count']} 条记录",
            'details' => $result
        ));
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_mrhe_migrate_auth_records', 'mrhe_ajax_migrate_auth_records');

/**
 * 检查并执行字段迁移
 */
function mrhe_check_and_add_banned_field()
{
    $added = mrhe_add_banned_field();
    
    if ($added) {
        // 可以在这里添加通知或其他逻辑
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>MRHE授权系统：已添加封禁字段到数据库表。</p></div>';
        });
    }
}

// 在主题激活时执行迁移
add_action('after_switch_theme', 'mrhe_check_and_add_banned_field');

// 在管理后台加载时检查（用于手动触发）
add_action('admin_init', function() {
    if (isset($_GET['mrhe_migrate_banned']) && current_user_can('manage_options')) {
        mrhe_check_and_add_banned_field();
        wp_redirect(remove_query_arg('mrhe_migrate_banned'));
        exit;
    }
});

// 在主题激活时执行迁移
add_action('after_switch_theme', 'mrhe_remove_product_id_redundancy');
// 在管理员访问时执行迁移（确保迁移完成）
add_action('admin_init', 'mrhe_remove_product_id_redundancy');

// 自动执行迁移（在主题激活时）
function mrhe_auto_migrate_on_activation()
{
    if (mrhe_check_migration_needed()) {
        $result = mrhe_migrate_auth_records();
        if (!$result['success']) {
            // 记录错误到日志，而不是静默失败
        }
    }
}
add_action('after_switch_theme', 'mrhe_auto_migrate_on_activation');

// 初始化数据库
add_action('init', 'mrhe_init_database');
