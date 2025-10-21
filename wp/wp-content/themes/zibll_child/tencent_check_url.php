<?php
// require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
// header('Content-Type: application/json; charset=utf-8');
// header("Access-Control-Allow-Origin: *");

// // cURL函数
// function doCurl($url, $data = [], $header = [], $referer = '', $timeout = 30) {
//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
//     curl_setopt($ch, CURLOPT_POST, false); // 使用GET请求，与API一致
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
//     curl_setopt($ch, CURLOPT_REFERER, $referer);
//     curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
//     $response = curl_exec($ch);
//     if ($error = curl_error($ch)) {
//         curl_close($ch);
//         return json_encode(['code' => 500, 'msg' => 'cURL错误: ' . $error], JSON_UNESCAPED_UNICODE);
//     }
//     curl_close($ch);
//     return $response;
// }

// // 获取并验证URL
// $checkurl = isset($_GET["url"]) ? trim($_GET["url"]) : '';

// // Referer检查（防止盗用API，建议启用）
// /*
// if (empty($_SERVER['HTTP_REFERER']) || stripos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
//     exit(json_encode(['code' => 500, 'msg' => '禁止盗用API'], JSON_UNESCAPED_UNICODE));
// }
// */

// // URL验证
// $preg = "/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/i"; // 更严格的URL正则
// if (!empty($checkurl) && preg_match($preg, $checkurl)) {
//     // 补全协议头
//     if (!preg_match("/^https?:\/\//i", $checkurl)) {
//         $checkurl = "http://" . $checkurl;
//     }

//     // 构造API请求URL，模拟部分动态参数
//     $timestamp = microtime(true) * 1000;
//     $callback = "jQuery" . rand(1000000, 9999999) . "_" . (int)$timestamp;
//     $url = "https://cgi.urlsec.qq.com/index.php?m=check&a=gw_check&callback={$callback}&url=" . urlencode($checkurl) . "&_=" . (int)$timestamp;
    
//     // 注意：ticket参数缺失，可能需要从前端提取或模拟，这里暂时省略
//     // $url .= "&ticket=your_dynamic_ticket&randstr=@8XT";

//     $header = [
//         'CLIENT-IP: ' . $_SERVER['REMOTE_ADDR'], // 使用客户端真实IP
//         'X-FORWARDED-FOR: ' . $_SERVER['REMOTE_ADDR'],
//     ];
//     $referer = 'https://urlsec.qq.com/';

//     // 发送请求
//     $response = doCurl($url, [], $header, $referer);

//     // 检查响应是否有效
//     if (strpos($response, 'jQuery') === 0) {
//         // 移除JSONP包裹
//         $data = preg_replace('/^' . preg_quote($callback, '/') . '\((.+)\)$/', '$1', $response);
//         $data = json_decode($data, true);

//         if ($data && isset($data['data']['results'])) {
//             $results = $data['data']['results'];
//             $json = [
//                 'url' => $results['url'] ?? '',
//                 'type' => $results['whitetype'] ?? '',
//                 'beian' => $results['isDomainICPOk'] ?? '',
//                 'icpdode' => $results['ICPSerial'] ?? '',
//                 'icporg' => $results['Orgnization'] ?? '',
//                 'word' => $results['Wording'] ?? '',
//                 'wordtit' => $results['WordingTitle'] ?? '',
//             ];
//             exit(json_encode($json, JSON_UNESCAPED_UNICODE));
//         } else {
//             exit(json_encode(['code' => 500, 'msg' => 'API返回数据格式错误'], JSON_UNESCAPED_UNICODE));
//         }
//     } else {
//         exit(json_encode(['code' => 500, 'msg' => '请求失败或响应为空', 'response' => $response], JSON_UNESCAPED_UNICODE));
//     }
// } else {
//     $json = [
//         'warning' => '请填写正确的网址！以下是API使用方法',
//         'url' => '检测的网址',
//         'type' => '网址类型：为"1"，则网址未知（包括腾讯云绿标）为"2"，则网址报毒为"3"，则网址安全（即有付费的绿标）',
//         'beian' => '是否备案：为"1"，则已经备案为"0"，则未备案',
//         'icpdode' => '备案号，未备案则空',
//         'icporg' => '备案主体，未备案则空',
//         'word' => '报毒原因，未报毒则空',
//         'wordtit' => '报毒原因标题，未报毒则空',
//     ];
//     exit(json_encode($json, JSON_UNESCAPED_UNICODE));
// }

// https://yunapi.cn/api/tencent_security?http://www.xxsese.com
// https://apis.kit9.cn/api/tencent_security/api.php?url=https://www.sese98.com

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

// cURL函数
function doCurl($url, $data = [], $header = [], $referer = '', $timeout = 30) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, false); // 使用GET请求，与API一致
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    $response = curl_exec($ch);
    if ($error = curl_error($ch)) {
        curl_close($ch);
        return json_encode(['code' => 500, 'msg' => 'cURL错误: ' . $error], JSON_UNESCAPED_UNICODE);
    }
    curl_close($ch);
    return $response;
}

// 获取并验证URL
$checkurl = isset($_GET["url"]) ? trim($_GET["url"]) : '';

// Referer检查（防止盗用API，建议启用）
/*
if (empty($_SERVER['HTTP_REFERER']) || stripos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
    exit(json_encode(['code' => 500, 'msg' => '禁止盗用API'], JSON_UNESCAPED_UNICODE));
}
*/

// URL验证
$preg = "/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/i"; // 更严格的URL正则
if (!empty($checkurl) && preg_match($preg, $checkurl)) {
    // 补全协议头
    if (!preg_match("/^https?:\/\//i", $checkurl)) {
        $checkurl = "http://" . $checkurl;
    }
    
    //$url = 'https://yunapi.cn/api/tencent_security?'.$checkurl;
    $url = 'https://apis.kit9.cn/api/tencent_security/api.php?url='.$checkurl;
    $header = [
        'CLIENT-IP: ' . $_SERVER['REMOTE_ADDR'], // 使用客户端真实IP
        'X-FORWARDED-FOR: ' . $_SERVER['REMOTE_ADDR'],
    ];

    $referer = 'https://yunapi.cn/';

    // 发送请求
    $response = doCurl($url, [], $header, $referer);
    $response = json_decode($response,true);
   // echo $response['data'];
    exit(json_encode($response['data'], JSON_UNESCAPED_UNICODE));
}