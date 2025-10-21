<?php
require_once('wp/wp-load.php');

echo "=== 检查插件授权数据 ===\n\n";

// 检查授权缓存
$auth_data = get_option('zibll_plugin_auth_cache');
echo "1. 授权缓存数据:\n";
if ($auth_data && is_array($auth_data)) {
    echo "   存在: ✅\n";
    echo "   过期时间: " . date('Y-m-d H:i:s', $auth_data['time']) . "\n";
    echo "   当前时间: " . date('Y-m-d H:i:s', time()) . "\n";
    echo "   是否过期: " . (time() > $auth_data['time'] ? '❌ 是' : '✅ 否') . "\n";
    echo "   Token: " . (isset($auth_data['token']) ? $auth_data['token'] : '不存在') . "\n";
    echo "   Product ID: " . (isset($auth_data['product_id']) ? $auth_data['product_id'] : '不存在') . "\n";
} else {
    echo "   存在: ❌ 不存在或格式错误\n";
}

// 检查签名
if ($auth_data && isset($auth_data['token'])) {
    $token = $auth_data['token'];
    $signature = get_option('zibll_plugin_auth_signature_' . $token);
    echo "\n2. 授权签名:\n";
    echo "   存在: " . ($signature ? '✅ 是' : '❌ 否') . "\n";
    if ($signature) {
        echo "   签名值: " . substr($signature, 0, 20) . "...\n";
    }
}

// 检查下次验证时间
$next_verify = get_option('zibll_plugin_auth_next', 0);
echo "\n3. 下次验证时间:\n";
echo "   时间: " . ($next_verify ? date('Y-m-d H:i:s', $next_verify) : '未设置') . "\n";
echo "   是否需要验证: " . (time() > $next_verify ? '❌ 是' : '✅ 否') . "\n";

// 检查当前域名
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
echo "\n4. 当前域名: " . $domain . "\n";

echo "\n=== 检查完成 ===\n";
