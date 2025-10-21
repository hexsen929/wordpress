<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|Vue.js管理后台主页面
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die('您不能访问此页面', '权限不足');
    exit;
}

// 插件使用 MRHE_AUTH_SERVER_URL 常量

// Vue.js 数据结构
$vue_data = [
    'config' => [
        'admin_url' => admin_url(),
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mrhe_admin_auth_nonce'),
    ],
    'status_name' => [
        0 => '未授权',
        1 => '已授权',
    ],
    'colors' => ['#ff4747', '#ee5307', '#1e8608', '#1a8a65', '#0c9cc8', '#086ae8', '#3353fd', '#4641e8', '#853bf2', '#e94df7', '#ca2b7d', '#d7354c', '#ff4747', '#8e24ac'],
];

// 应用 Vue 数据过滤器
function mrhe_auth_admin_page_vue_data_filter($vue_data) {
    add_filter('admin_auth_page_vue_data', function ($__vue_data) use ($vue_data) {
        return array_merge($__vue_data, $vue_data);
    });
}

mrhe_auth_admin_page_vue_data_filter($vue_data);

// 加载 Vue.js 资源
function mrhe_auth_admin_page_start() {
    // 加载父主题的 Vue.js 资源
    wp_enqueue_style('element-plus', get_template_directory_uri() . '/zibpay/assets/css/element-plus.min.css', array(), THEME_VERSION);
    wp_enqueue_script('vue', get_template_directory_uri() . '/zibpay/assets/js/vue.global.min.js', array(), THEME_VERSION, true);
    wp_enqueue_script('vue-router', get_template_directory_uri() . '/zibpay/assets/js/vue-router.global.min.js', array('vue'), THEME_VERSION, true);
    wp_enqueue_script('element-plus', get_template_directory_uri() . '/zibpay/assets/js/element-plus.min.js', array('vue'), THEME_VERSION, true);
    wp_enqueue_script('element-plus-zh-cn', get_template_directory_uri() . '/zibpay/assets/js/element-plus-zh-cn.min.js', array('element-plus'), THEME_VERSION, true);
    
    // 加载自定义样式和脚本
    $auth_css_file = MRHE_AUTH_SERVER_DIR . 'admin/assets/css/auth-admin.css';
    $auth_js_file = MRHE_AUTH_SERVER_DIR . 'admin/assets/js/auth-admin.js';
    $css_version = file_exists($auth_css_file) ? filemtime($auth_css_file) : THEME_VERSION;
    $js_version = file_exists($auth_js_file) ? filemtime($auth_js_file) : THEME_VERSION;
    
    wp_enqueue_style('mrhe-auth-admin', MRHE_AUTH_SERVER_URL . 'admin/assets/css/auth-admin.css', array('element-plus'), $css_version);
    wp_enqueue_script('mrhe-auth-admin', MRHE_AUTH_SERVER_URL . 'admin/assets/js/auth-admin.js', array('vue', 'vue-router', 'element-plus'), $js_version, true);
    
    // 传递数据到前端
    wp_localize_script('mrhe-auth-admin', 'mrheAuthAdmin', apply_filters('admin_auth_page_vue_data', []));
}

// 直接加载资源（因为此时admin_enqueue_scripts钩子已经过了）
mrhe_auth_admin_page_start();

?>
<style>
    #wpbody-content .notice{
        display: none;
    }
    .loading-mask {
        position: fixed;
        inset: 0;
        background: #fff;
        z-index: 10;
    }
</style>

<div class="mrhe-auth-admin admin-container" id="mrhe_auth_app">
    <?php require MRHE_AUTH_SERVER_DIR . 'admin/template/header.php'; ?>
    <div class="mrhe-auth-content">
        <transition name="slide-down" mode="out-in" tag="div">
            <div v-if="$route.path == '/list'" key="list">
                <?php require_once MRHE_AUTH_SERVER_DIR . 'admin/template/auth-list.php'; ?>
            </div>
            <div v-if="$route.path == '/add'" key="add">
                <?php require_once MRHE_AUTH_SERVER_DIR . 'admin/template/auth-add.php'; ?>
            </div>
            <div v-else key="dashboard">
                <?php require_once MRHE_AUTH_SERVER_DIR . 'admin/template/dashboard.php'; ?>
            </div>
        </transition>
    </div>
    <?php require_once MRHE_AUTH_SERVER_DIR . 'admin/template/dialogs/auth-detail.php'; ?>
    <?php require_once MRHE_AUTH_SERVER_DIR . 'admin/template/dialogs/auth-edit.php'; ?>
</div>

<div class="flex jc loading-mask auth-page-loading"><div class="loading"></div></div>
