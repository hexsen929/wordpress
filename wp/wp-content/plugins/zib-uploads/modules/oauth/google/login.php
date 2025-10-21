<?php
/**
 * Google OAuth 登录页面
 * 基于子比主题官方机制实现
 */

// 安全检测
if (!defined('ABSPATH')) {
    exit;
}

// 检查功能是否启用
if (!zibll_plugin_option('google_enable', false)) {
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('Google登录功能未启用');
    } else {
        wp_die('Google登录功能未启用');
    }
}

// 获取配置 - 使用统一配置函数
$config = zibll_plugin_get_oauth_config('google');
if (!$config['appid'] || !$config['appkey']) {
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('Google登录配置不完整', 'Client ID或Client Secret未设置');
    } else {
        wp_die('Google登录配置不完整');
    }
}

// 启用session
@session_start();

// 生成state参数防止CSRF攻击
$state = wp_create_nonce('google_oauth_' . time());
$_SESSION['google_oauth_state'] = $state;

// 保存回调地址
if (!empty($_REQUEST['rurl'])) {
    $_SESSION['oauth_rurl'] = $_REQUEST['rurl'];
}

// 构造Google OAuth授权URL
$params = array(
    'client_id'     => $config['appid'],
    'redirect_uri'  => $config['backurl'],
    'response_type' => 'code',
    'scope'         => 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email',
    'state'         => $state,
    'access_type'   => 'offline',
    'prompt'        => 'consent'
);

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

// 重定向到Google授权页面
wp_redirect($auth_url);
exit;
