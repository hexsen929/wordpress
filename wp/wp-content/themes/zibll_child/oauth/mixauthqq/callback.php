<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2025-09-18
 * @LastEditTime: 2025-09-18
 * MixAuth QQ登录回调处理文件 - 生产版本
 */

require_once dirname(__FILE__) . '/../oauth.php';

// 获取配置
$config = get_oauth_config('mixauthqq');
if (!$config['server_url']) {
    zib_oauth_die('MixAuth QQ登录未设置服务地址');
}

$integration_mode = $config['integration_mode'] ?? 'api';

// 启用session
@session_start();

// 如果是GET请求，可能是MixAuth的重定向回调
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 检查是否有sign参数（MixAuth的签名登录结果）
    if (!empty($_GET['sign'])) {
        $sign = $_GET['sign'];
        $server_url = rtrim($config['server_url'], '/');
        
        // 验证签名
        $verify_response = wp_remote_post($server_url . '/api/verify', array(
            'body' => json_encode(array('sign' => $sign)),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 30,
        ));
        
        if (is_wp_error($verify_response)) {
            zib_oauth_die('签名验证失败：' . $verify_response->get_error_message());
        }
        
        $verify_data = json_decode(wp_remote_retrieve_body($verify_response), true);
        if (!$verify_data || !$verify_data['success']) {
            zib_oauth_die('签名验证失败：' . ($verify_data['message'] ?? '未知错误'));
        }
        
        $user_data = $verify_data['data'];
    } else {
        zib_oauth_die('未收到有效的登录数据');
    }
} else {
    // POST请求处理
    // 验证state
    if (empty($_POST['state']) || $_POST['state'] !== $_SESSION['mixauthqq_state']) {
        zib_oauth_die('状态验证失败，请重新登录');
    }

    // 获取用户数据
    $user_data_json = '';
    if (!empty($_POST['user_data'])) {
        $user_data_json = $_POST['user_data'];
    } elseif (!empty($_POST['mixauth_result'])) {
        $user_data_json = $_POST['mixauth_result'];
    } elseif (!empty($_POST['sign'])) {
        // API/iframe模式：收到签名数据，需要验证
        $sign = $_POST['sign'];
        $server_url = rtrim($config['server_url'], '/');
        
        // 验证签名
        $verify_response = wp_remote_post($server_url . '/api/verify', array(
            'body' => json_encode(array('sign' => $sign)),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 30,
        ));
        
        if (is_wp_error($verify_response)) {
            zib_oauth_die('签名验证失败：' . $verify_response->get_error_message());
        }
        
        $verify_data = json_decode(wp_remote_retrieve_body($verify_response), true);
        if (!$verify_data || !$verify_data['success']) {
            zib_oauth_die('签名验证失败：' . ($verify_data['message'] ?? '未知错误'));
        }
        
        $user_data = $verify_data['data'];
    } else {
        zib_oauth_die('未收到用户数据');
    }

    if (!empty($user_data_json)) {
        // API模式：直接接收JSON用户数据（某些情况下MixAuth可能直接返回用户数据）
        $user_data = json_decode($user_data_json, true);
        
        if (!$user_data || !is_array($user_data)) {
            zib_oauth_die('用户数据格式错误');
        }
    }
}

if (!$user_data || !is_array($user_data)) {
    zib_oauth_die('用户数据格式错误');
}

// 验证数据签名（如果MixAuth返回了签名数据）
if (isset($user_data['sign'])) {
    $sign = $user_data['sign'];
    $server_url = rtrim($config['server_url'], '/');
    
    // 验证签名
    $verify_response = wp_remote_post($server_url . '/api/verify', array(
        'body' => json_encode(array('sign' => $sign)),
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'timeout' => 30,
    ));
    
    if (is_wp_error($verify_response)) {
        zib_oauth_die('签名验证失败：' . $verify_response->get_error_message());
    }
    
    $verify_data = json_decode(wp_remote_retrieve_body($verify_response), true);
    if (!$verify_data || !$verify_data['success']) {
        zib_oauth_die('签名验证失败');
    }
    
    // 使用验证后的数据
    $verified_data = $verify_data['data'];
} else {
    // 如果没有签名，直接使用原始数据（需要验证关键字段）
    $verified_data = $user_data;
}

// 提取用户信息（优化后的数据提取）
$openid = $verified_data['qq'] ?? $verified_data['openid'] ?? '';
$nickname = $verified_data['username'] ?? $verified_data['nickname'] ?? $verified_data['name'] ?? '';
$avatar = $verified_data['avatar'] ?? $verified_data['figureurl_qq_2'] ?? '';

// 验证必需字段
if (empty($openid)) {
    zib_oauth_die('未获取到用户标识信息');
}

// 如果没有昵称，使用默认格式
if (empty($nickname)) {
    $nickname = 'QQ用户' . substr($openid, -6);
}

// 准备OAuth数据
$oauth_data = array(
    'type'        => 'mixauthqq',
    'openid'      => $openid,
    'name'        => $nickname,
    'avatar'      => $avatar,
    'description' => 'MixAuth QQ登录用户',
    'getUserInfo' => $verified_data,
);

// 处理用户登录
$oauth_result = zib_oauth_update_user($oauth_data);

if ($oauth_result['error']) {
    zib_oauth_die($oauth_result['msg']);
} else {
    // 清理session数据
    unset($_SESSION['mixauthqq_state']);
    
    // 获取重定向URL
    $rurl = !empty($_SESSION['oauth_rurl']) ? $_SESSION['oauth_rurl'] : $oauth_result['redirect_url'];
    
    // 重定向到目标页面
    wp_safe_redirect($rurl);
    exit;
}

zib_oauth_die('登录处理失败，请重试');
