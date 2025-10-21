<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:31:36
 * @LastEditTime: 2024-02-13 18:41:36
 */

require_once dirname(__FILE__) . '/../oauth.php';

// 获取配置
$config = get_oauth_config('google');
if (!$config['appid'] || !$config['appkey']) {
    zib_oauth_die('Google登录未设置appid或appkey');
}

// 验证state
@session_start();
if (empty($_GET['state']) || $_GET['state'] !== $_SESSION['google_state']) {
    zib_oauth_die('state验证失败');
}

// 获取code
if (empty($_GET['code'])) {
    zib_oauth_die('code获取失败');
}

// 获取access token
$token_url = 'https://oauth2.googleapis.com/token';
$response = wp_remote_post($token_url, array(
    'body' => array(
        'code'          => $_GET['code'],
        'client_id'     => $config['appid'],
        'client_secret' => $config['appkey'],
        'redirect_uri'  => $config['backurl'],
        'grant_type'    => 'authorization_code'
    )
));

if (is_wp_error($response)) {
    zib_oauth_die('获取token失败：' . $response->get_error_message());
}

$token_data = json_decode(wp_remote_retrieve_body($response), true);
if (empty($token_data['access_token'])) {
    zib_oauth_die('获取token失败');
}

// 获取用户信息
$user_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
$response = wp_remote_get($user_url, array(
    'headers' => array(
        'Authorization' => 'Bearer ' . $token_data['access_token']
    )
));

if (is_wp_error($response)) {
    zib_oauth_die('获取用户信息失败：' . $response->get_error_message());
}

$user_data = json_decode(wp_remote_retrieve_body($response), true);
if (empty($user_data['id'])) {
    zib_oauth_die('获取用户信息失败');
}

// 准备用户数据
$oauth_data = array(
    'type'        => 'google',
    'openid'      => $user_data['id'],
    'name'        => $user_data['name'],
    'avatar'      => 'https://api.hexsen.com/api/googleimg.php?url='.$user_data['picture'],
    'description' => '',
    'getUserInfo' => $user_data
);

// 处理用户登录
$oauth_result = zib_oauth_update_user($oauth_data);

if ($oauth_result['error']) {
    zib_oauth_die($oauth_result['msg']);
} else {
    $rurl = !empty($_SESSION['oauth_rurl']) ? $_SESSION['oauth_rurl'] : $oauth_result['redirect_url'];
    wp_safe_redirect($rurl);
    exit;
}

zib_oauth_die(); 