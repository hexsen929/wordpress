<?php
/**
 * MixAuth QQ OAuth 回调处理页面
 * 基于子比主题官方机制实现
 */

// 安全检测
if (!defined('ABSPATH')) {
    exit;
}

// 检查功能是否启用
if (!zibll_plugin_option('mixauthqq_enable', false)) {
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('MixAuth QQ登录功能未启用');
    } else {
        wp_die('MixAuth QQ登录功能未启用');
    }
}

// 启用session
@session_start();

// 验证state参数
if (empty($_POST['state']) || empty($_SESSION['mixauthqq_state']) || $_POST['state'] !== $_SESSION['mixauthqq_state']) {
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('安全验证失败', 'state参数验证失败');
    } else {
        wp_die('安全验证失败');
    }
}

// 获取配置 - 使用统一配置函数
$config = zibll_plugin_get_oauth_config('mixauthqq');

// 清除state
unset($_SESSION['mixauthqq_state']);
$user_data = null;

// 处理不同的数据来源
if (!empty($_POST['mixauth_result'])) {
    // API模式：直接的用户数据
    $mixauth_result = $_POST['mixauth_result'];
    if (is_string($mixauth_result)) {
        $user_data = json_decode(stripslashes($mixauth_result), true);
    }
} elseif (!empty($_POST['mixauth_data'])) {
    // iframe模式：从iframe消息传递的数据
    $mixauth_data = $_POST['mixauth_data'];
    if (is_string($mixauth_data)) {
        $user_data = json_decode(stripslashes($mixauth_data), true);
    }
} elseif (!empty($_POST['sign'])) {
    // 签名模式：需要验证签名
    $sign = sanitize_text_field($_POST['sign']);
    $user_data = verifyMixAuthSign($sign, $config['server_url']);
}

if (!$user_data) {
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('用户数据获取失败', '未接收到有效的用户数据');
    } else {
        wp_die('用户数据获取失败');
    }
}

// 提取用户信息（支持多种数据格式）
$openid = $user_data['qq'] ?? $user_data['openid'] ?? $user_data['id'] ?? '';
$nickname = $user_data['username'] ?? $user_data['nickname'] ?? $user_data['name'] ?? '';
$avatar = $user_data['avatar'] ?? $user_data['figureurl_qq_2'] ?? '';

if (empty($openid)) {
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('未获取到用户标识信息');
    } else {
        wp_die('未获取到用户标识信息');
    }
}

if (empty($nickname)) {
    $nickname = 'QQ用户' . substr($openid, -6);
}

// 检查是否为绑定模式
$is_bind_mode = !empty($_GET['bind']) && $_GET['bind'] === 'mixauthqq';

// 检查该openid是否已经绑定其他账号
if ($is_bind_mode) {
    $existing_users = get_users(array(
        'meta_key' => 'oauth_mixauthqq_openid',
        'meta_value' => $openid,
        'number' => 1,
        'count_total' => false
    ));
    
    if (!empty($existing_users)) {
        if (function_exists('zib_oauth_die')) {
            zib_oauth_die('绑定失败', '该MixAuth QQ账号已被其他用户绑定');
        } else {
            wp_die('该MixAuth QQ账号已被其他用户绑定');
        }
    }
}

// 准备用户数据
$oauth_data = array(
    'type'        => 'mixauthqq',
    'openid'      => $openid,
    'name'        => $nickname,
    'avatar'      => $avatar,
    'description' => 'MixAuth QQ登录用户',
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

/**
 * 验证MixAuth签名
 */
function verifyMixAuthSign($sign, $server_url) {
    if (empty($sign)) {
        return false;
    }
    
    $response = wp_remote_post(rtrim($server_url, '/') . '/api/verify', array(
        'body' => json_encode(array('sign' => $sign)),
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'timeout' => 15,
        'sslverify' => false,
    ));
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $verify_data = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($verify_data['code']) || $verify_data['code'] !== '0') {
        return false;
    }
    
    return isset($verify_data['data']) ? $verify_data['data'] : false;
}
