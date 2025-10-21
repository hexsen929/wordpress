<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2025-09-18
 * @LastEditTime: 2025-09-18
 * MixAuth QQ登录处理文件
 */

require_once dirname(__FILE__) . '/../oauth.php';

// 获取配置
$config = get_oauth_config('mixauthqq');
if (!$config['server_url']) {
    zib_oauth_die('MixAuth QQ登录未设置服务地址');
}

// 启用session
@session_start();

// 保存回调地址
if (!empty($_REQUEST['rurl'])) {
    $_SESSION['oauth_rurl'] = $_REQUEST['rurl'];
}

// 生成状态码
$state = md5(uniqid(rand(), true));
$_SESSION['mixauthqq_state'] = $state;

// 构建回调URL
$callback_url = home_url('/oauth/mixauthqq/callback');

// 构建MixAuth登录URL参数
$server_url = rtrim($config['server_url'], '/');
$params = array(
    'callback' => $callback_url,
    'state' => $state,
    'type' => 'qq'
);

// 如果MixAuth服务支持直接iframe接入，可以直接重定向
$mixauth_url = $server_url . '?' . http_build_query($params);

// 重定向到MixAuth服务
header('Location: ' . $mixauth_url);
exit;