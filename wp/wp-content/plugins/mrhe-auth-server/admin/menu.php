<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|管理后台菜单注册
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 注册授权管理菜单
 */
function mrhe_register_auth_admin_menu()
{
    // 检查权限
    if (!current_user_can('manage_options')) {
        return;
    }

    // 注册顶级菜单
    add_menu_page(
        '授权管理',                    // 页面标题
        '授权管理',                    // 菜单标题
        'manage_options',              // 权限
        'mrhe-auth-management',        // 菜单slug
        'mrhe_auth_management_page',   // 回调函数
        'dashicons-shield-alt',        // 图标
        70                             // 位置（在用户和工具之间）
    );

    // 注册子菜单
    add_submenu_page(
        'mrhe-auth-management',        // 父菜单slug
        '授权列表',                    // 页面标题
        '授权列表',                    // 菜单标题
        'manage_options',              // 权限
        'mrhe-auth-management',        // 菜单slug
        'mrhe_auth_management_page'    // 回调函数
    );

}
add_action('admin_menu', 'mrhe_register_auth_admin_menu');

/**
 * 授权管理主页面
 */
function mrhe_auth_management_page()
{
    // 检查权限
    if (!current_user_can('manage_options')) {
        wp_die('您没有权限访问此页面');
    }

    // 加载 Vue.js 主页面
    require_once MRHE_AUTH_SERVER_DIR . 'admin/page.php';
}

