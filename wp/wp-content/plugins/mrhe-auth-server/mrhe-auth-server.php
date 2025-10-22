<?php
/*
Plugin Name: mrhe主题授权服务端
Plugin URI: https://zibll.com
Description: mrhe主题授权系统服务端功能，包含完整的授权管理、用户中心、后台管理等功能
Version: 1.0.0
Author: Qinver
Author URI: https://zibll.com
License: GPL v2 or later
Text Domain: mrhe-auth-server
*/

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('MRHE_AUTH_SERVER_VERSION', '1.0.0');
define('MRHE_AUTH_SERVER_DIR', plugin_dir_path(__FILE__));
define('MRHE_AUTH_SERVER_URL', plugin_dir_url(__FILE__));

// 检查子主题是否激活
function mrhe_auth_server_check_theme() {
    $theme = wp_get_theme();
    if ($theme->get('Name') !== 'zibll_child' && $theme->get('Template') !== 'zibll') {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>mrhe主题授权服务端插件需要 zibll_child 子主题才能正常工作。</p></div>';
        });
        return false;
    }
    return true;
}

// 插件激活时执行
function mrhe_auth_server_activate() {
    // 检查主题
    if (!mrhe_auth_server_check_theme()) {
        return;
    }
    
    // 创建数据库表
    mrhe_auth_server_create_tables();
    
    // 刷新重写规则
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'mrhe_auth_server_activate');

// 插件停用时执行
function mrhe_auth_server_deactivate() {
    // 刷新重写规则
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'mrhe_auth_server_deactivate');

// 创建数据库表
function mrhe_auth_server_create_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // 授权记录表 - 统一使用 mrhe_theme_aut 表名
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='mrhe主题授权表' $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// 加载服务端模块
function mrhe_auth_server_load_modules() {
    // 检查主题
    if (!mrhe_auth_server_check_theme()) {
        return;
    }
    
    // 1. 加载工具函数
    require_once MRHE_AUTH_SERVER_DIR . 'functions.php';
    
    // 2. 加载数据库模块（统一管理）
    if (file_exists(MRHE_AUTH_SERVER_DIR . 'database/database.php')) {
        require_once MRHE_AUTH_SERVER_DIR . 'database/database.php';
    }
    
    // 3. 加载服务端 API
    if (file_exists(MRHE_AUTH_SERVER_DIR . 'server/class-auth-server.php')) {
        require_once MRHE_AUTH_SERVER_DIR . 'server/class-auth-server.php';
    }
    
    // 4. 加载订单钩子
    if (file_exists(MRHE_AUTH_SERVER_DIR . 'hooks/order-hooks.php')) {
        require_once MRHE_AUTH_SERVER_DIR . 'hooks/order-hooks.php';
    }
    
    // 5. 加载用户中心授权页面
    if (file_exists(MRHE_AUTH_SERVER_DIR . 'user-center/auth-management.php')) {
        require_once MRHE_AUTH_SERVER_DIR . 'user-center/auth-management.php';
    }
    
    // 6. 加载用户中心 AJAX
    if (file_exists(MRHE_AUTH_SERVER_DIR . 'admin/ajax.php')) {
        require_once MRHE_AUTH_SERVER_DIR . 'admin/ajax.php';
    }
    
    // 7. 加载管理后台（仅管理员）
    if (is_admin() && current_user_can('manage_options')) {
        if (file_exists(MRHE_AUTH_SERVER_DIR . 'admin/menu.php')) {
            require_once MRHE_AUTH_SERVER_DIR . 'admin/menu.php';
        }
        if (file_exists(MRHE_AUTH_SERVER_DIR . 'admin/ajax-admin.php')) {
            require_once MRHE_AUTH_SERVER_DIR . 'admin/ajax-admin.php';
        }
    }
}

// 在插件加载时执行
add_action('plugins_loaded', 'mrhe_auth_server_load_modules', 1);

// 提供钩子给子主题客户端调用
add_action('init', function() {
    // 定义钩子函数
    if (!function_exists('mrhe_auth_server_verify')) {
        function mrhe_auth_server_verify($auth_code, $domain) {
            // 验证授权码和域名
            return apply_filters('mrhe_auth_server_verify', false, $auth_code, $domain);
        }
    }
    
    if (!function_exists('mrhe_auth_server_get_info')) {
        function mrhe_auth_server_get_info($user_id) {
            // 获取用户授权信息
            return apply_filters('mrhe_auth_server_get_info', array(), $user_id);
        }
    }
    
    if (!function_exists('mrhe_auth_server_update')) {
        function mrhe_auth_server_update($user_id, $data) {
            // 更新授权信息
            return apply_filters('mrhe_auth_server_update', false, $user_id, $data);
        }
    }
}, 1);

// 注册页面模板（仅服务端功能页面）
function mrhe_auth_server_add_page_templates($templates) {
    $templates['mrhe_pay.php'] = 'mrhe主题-推广产品页面';
    return $templates;
}
add_filter('theme_page_templates', 'mrhe_auth_server_add_page_templates');

// 加载模板文件（仅服务端功能页面）
function mrhe_auth_server_load_template($template) {
    global $post;
    
    // 检查是否是服务端页面模板
    if (is_page_template('mrhe_pay.php')) {
        $plugin_template = MRHE_AUTH_SERVER_DIR . 'templates/mrhe_pay.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    
    return $template;
}
add_filter('page_template', 'mrhe_auth_server_load_template');
