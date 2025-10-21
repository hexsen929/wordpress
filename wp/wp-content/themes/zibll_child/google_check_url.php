<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
header('Content-Type:application/json; charset=utf-8');

function doCurl($url, $data=array(), $header=array(), $referer='', $timeout=30){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 添加 SSL 验证设置
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    
    if($error = curl_error($ch)){
        curl_close($ch);
        return json_encode([
            'url' => $_GET['url'] ?? '',
            'type' => 'error',
            'message' => $error
        ]);
    }
    
    curl_close($ch);
    return $response;
}

$preg = "/\./i";
$checkurl = $_GET['url'] ?? '';

if(!empty($checkurl) && preg_match($preg, $checkurl)){ 
    // 检查 _mrhe 函数是否存在，以及是否能获取到 token
    $token = '';
    if (function_exists('_mrhe')) {
        $token = _mrhe('google_check_url');
    }
    
    // 如果没有 token，返回错误
    if (empty($token)) {
        $json = [
            'url' => $checkurl,
            'type' => 'error',
            'message' => 'Google API token 未配置或函数 _mrhe 不存在'
        ];
        exit(json_encode($json, JSON_UNESCAPED_UNICODE));
    }
    
    $googlecheck_url = 'https://safebrowsing.googleapis.com/v4/threatMatches:find?key=' . $token;
    
    $data = [
        'client' => [
            'clientId' => 'armxmod',
            'clientVersion' => '7.2.0'
        ],
        'threatInfo' => [
            'threatTypes' => ['MALWARE', 'SOCIAL_ENGINEERING'],
            'platformTypes' => ['WINDOWS'],
            'threatEntryTypes' => ['URL'],
            'threatEntries' => [
                [ 'url' => $checkurl ]
            ]
        ]
    ];
    
    $headers = array(
        "cache-control: no-cache",
        "content-type: application/json"
    );
    
    $response = doCurl($googlecheck_url, $data, $headers);
    
    // 解析响应
    $responseData = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        // JSON 解析失败
        $json = [
            'url' => $checkurl,
            'type' => 'error',
            'message' => 'API 响应解析失败: ' . json_last_error_msg()
        ];
    } else {
        // 检查响应内容
        if (isset($responseData['error']['message'])) {
            $type = 'error';
            $message = $responseData['error']['message'];
        } elseif (isset($responseData['matches']) && !empty($responseData['matches'])) {
            $type = 'danger';
            $message = '检测到威胁: ' . ($responseData['matches'][0]['threatType'] ?? '未知威胁');
        } else {
            $type = 'safe';
            $message = '网站安全';
        }
        
        $json = [
            'url' => $checkurl,
            'type' => $type,
            'message' => $message ?? ''
        ];
    }
    
    exit(json_encode($json, JSON_UNESCAPED_UNICODE));
    
} else {
    $json = [
        'warning' => '请填写正确的网址！以下是 api 使用方法',
        'url' => '检测的网址',
        'type' => '网址类型：为"safe"，则网址安全；为"danger"，则网址有风险；为"error"，则检测出错'
    ];
    exit(json_encode($json, JSON_UNESCAPED_UNICODE));
}
?>