<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|模块加载器
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

//定义授权模块根目录
define('MRHE_AUTH_DIR', get_stylesheet_directory() . '/mrhe-auth/');
define('MRHE_AUTH_URL', get_stylesheet_directory_uri() . '/mrhe-auth/');

//加载核心文件（仅客户端功能）
function mrhe_load_auth_modules()
{
    //1. 加载客户端核心类（加密文件）
    if (file_exists(MRHE_AUTH_DIR . 'client/class-auth-client.php')) {
        require_once MRHE_AUTH_DIR . 'client/class-auth-client.php';
    }
}

//在主题初始化时加载模块
add_action('after_setup_theme', 'mrhe_load_auth_modules', 1);

// 为了确保在 options.php 执行时类已经加载，也在 init 钩子中加载
add_action('init', 'mrhe_load_auth_modules', 1);

// 立即加载模块（确保在 functions.php 加载时就开始加载）
mrhe_load_auth_modules();