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

// 生成state
$state = md5(uniqid(rand(), true));
@session_start();
$_SESSION['google_state'] = $state;

// 保存回调地址
if (!empty($_REQUEST['rurl'])) {
    $_SESSION['oauth_rurl'] = $_REQUEST['rurl'];
}

// 构造请求参数
$params = array(
    'client_id'     => $config['appid'],
    'redirect_uri'  => $config['backurl'],
    'response_type' => 'code',
    'scope'         => 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email',
    'state'         => $state
);

// 跳转到Google授权页面
$url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $url);
exit; 