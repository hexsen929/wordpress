<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime: 2025-09-30 20:42:48
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

// 引入父主题核心函数
require_once get_theme_file_path('/inc/inc.php');

// 引入mrhe主题授权系统（必须在 core.php 之前加载，因为 options-module.php 需要用到工具函数）
// 注意：core/client/ 目录下的文件将在发布时被 ionCube 加密
require_once get_theme_file_path('/mrhe-auth/loader.php');

// 在父主题和授权系统之后引入子主题核心函数
require_once get_theme_file_path('/core/core.php');

// 加载 mrhecode 功能文件（必须在 core.php 之后，因为需要 _mrhe() 函数）
require_once get_theme_file_path('/mrhecode/functions.php');

// 添加额外的授权验证点
add_action('init', function() {
    // 在主题初始化时进行授权检查
    if (class_exists('MrheAuthClient') && !MrheAuthClient::getInstance()->is_theme_authorized()) {
        // 如果未授权，可以在这里添加额外的限制
        add_action('wp_footer', function() {
            if (!is_admin()) {
                echo '<!-- mrhe主题授权检查 -->';
            }
        });
    }
}, 1);

// 在页面头部添加授权检查
add_action('wp_head', function() {
    if (class_exists('MrheAuthClient') && !MrheAuthClient::getInstance()->is_theme_authorized()) {
        echo '<!-- mrhe主题未授权 -->';
    }
}, 1);

/**
 * 如果您需要添加一些自定义的PHP代码
 * 您可以在当前目录下新建一个 func.php 的文件，然后在最顶部写上 <?php ，再写入您的php代码
 * 主题会自动判断文件进行引入
 * 使用此方式在线更新主题的时候，func.php文件的内容将不会被覆盖（手动更新仍然会覆盖）
 * 当然需要注意php的代码规范，错误代码将会引起网站严重错误！
 */
if (file_exists(get_theme_file_path('/func.php'))) {
    require_once get_theme_file_path('/func.php');
}