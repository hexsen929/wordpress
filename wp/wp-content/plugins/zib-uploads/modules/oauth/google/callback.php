<?php
/**
 * Google OAuth 回调处理页面
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

// 启用session
@session_start();

// 验证state参数
if (empty($_GET['state']) || empty($_SESSION['google_oauth_state']) || $_GET['state'] !== $_SESSION['google_oauth_state']) {
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('安全验证失败', 'state参数验证失败');
    } else {
        wp_die('安全验证失败');
    }
}

// 清除state
unset($_SESSION['google_oauth_state']);

// 检查是否有错误
if (!empty($_GET['error'])) {
    $error_msg = !empty($_GET['error_description']) ? $_GET['error_description'] : $_GET['error'];
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('Google登录失败', $error_msg);
    } else {
        wp_die('Google登录失败: ' . $error_msg);
    }
}

// 获取授权码
if (empty($_GET['code'])) {
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('获取授权码失败');
    } else {
        wp_die('获取授权码失败');
    }
}

$code = sanitize_text_field($_GET['code']);
$config = zibll_plugin_get_oauth_config('google');

// 获取访问令牌
$token_url = 'https://oauth2.googleapis.com/token';
$token_data = array(
    'code'          => $code,
    'client_id'     => $config['appid'],
    'client_secret' => $config['appkey'],
    'redirect_uri'  => $config['backurl'],
    'grant_type'    => 'authorization_code'
);

$response = wp_remote_post($token_url, array(
    'body' => $token_data,
    'timeout' => 30,
    'sslverify' => true,
));

if (is_wp_error($response)) {
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('获取访问令牌失败', $response->get_error_message());
    } else {
        wp_die('获取访问令牌失败: ' . $response->get_error_message());
    }
}

$token_result = json_decode(wp_remote_retrieve_body($response), true);
if (empty($token_result['access_token'])) {
    $error_msg = !empty($token_result['error_description']) ? $token_result['error_description'] : '未知错误';
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('获取访问令牌失败', $error_msg);
    } else {
        wp_die('获取访问令牌失败: ' . $error_msg);
    }
}

// 获取用户信息
$user_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
$user_response = wp_remote_get($user_url, array(
    'headers' => array(
        'Authorization' => 'Bearer ' . $token_result['access_token']
    ),
    'timeout' => 30,
    'sslverify' => true,
));

if (is_wp_error($user_response)) {
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('获取用户信息失败', $user_response->get_error_message());
    } else {
        wp_die('获取用户信息失败: ' . $user_response->get_error_message());
    }
}

$user_data = json_decode(wp_remote_retrieve_body($user_response), true);
if (empty($user_data['id'])) {
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('获取用户信息失败', '用户数据格式错误');
    } else {
        wp_die('获取用户信息失败');
    }
}

// 检查是否为绑定模式
$is_bind_mode = !empty($_GET['bind']) && $_GET['bind'] === 'google';

// 检查该Google账号是否已被其他用户绑定
if ($is_bind_mode) {
    $existing_users = get_users(array(
        'meta_key' => 'oauth_google_openid',
        'meta_value' => $user_data['id'],
        'number' => 1,
        'count_total' => false
    ));
    
    if (!empty($existing_users)) {
        if (function_exists('zib_oauth_die')) {
            zib_oauth_die('绑定失败', '该Google账号已被其他用户绑定');
        } else {
            wp_die('该Google账号已被其他用户绑定');
        }
    }
}

// 准备用户数据
$oauth_data = array(
    'type'        => 'google',
    'openid'      => $user_data['id'],
    'name'        => !empty($user_data['name']) ? $user_data['name'] : (!empty($user_data['email']) ? $user_data['email'] : 'Google用户'),
    'avatar'      => !empty($user_data['picture']) ? $user_data['picture'] : '',
    'description' => 'Google登录用户',
    'getUserInfo' => $user_data,
);

// 使用子比主题的OAuth更新用户函数
if (function_exists('zib_oauth_update_user')) {
    $result = zib_oauth_update_user($oauth_data);
    
    if ($result['error']) {
        if (function_exists('zib_oauth_die')) {
            zib_oauth_die($result['msg']);
        } else {
            wp_die($result['msg']);
        }
    }
    
    // 获取返回URL
    $rurl = !empty($_SESSION['oauth_rurl']) ? $_SESSION['oauth_rurl'] : home_url();
    unset($_SESSION['oauth_rurl']);
    
    // 重定向
    wp_redirect($rurl);
    exit;
} else {
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('系统错误', '请确保使用的是子比主题');
    } else {
        wp_die('系统错误: 请确保使用的是子比主题');
    }
}
