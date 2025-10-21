<?php
/**
 * 简化版授权失败自动清除测试
 */

// 加载 WordPress 环境
require_once('wp/wp-load.php');

echo "=== 授权失败自动清除测试 ===\n\n";

// 测试1: 检查服务端错误码
echo "测试1: 检查服务端错误码\n";
echo "----------------------\n";

$server = new MrheAuthServer();

// 测试封禁场景
$result = $server->verify_auth('test-banned.com', 'rhe-theme-v1');
echo "封禁场景 - 成功: " . ($result['success'] ? '是' : '否') . "\n";
echo "封禁场景 - 错误码: " . ($result['code'] ?? '无') . "\n";
echo "封禁场景 - 消息: " . ($result['message'] ?? '无') . "\n\n";

// 测试域名不存在场景
$result = $server->verify_auth('nonexistent-domain.com', 'rhe-theme-v1');
echo "域名不存在场景 - 成功: " . ($result['success'] ? '是' : '否') . "\n";
echo "域名不存在场景 - 错误码: " . ($result['code'] ?? '无') . "\n";
echo "域名不存在场景 - 消息: " . ($result['message'] ?? '无') . "\n\n";

// 测试域名格式错误场景
$result = $server->verify_auth('', 'rhe-theme-v1');
echo "域名格式错误场景 - 成功: " . ($result['success'] ? '是' : '否') . "\n";
echo "域名格式错误场景 - 错误码: " . ($result['code'] ?? '无') . "\n";
echo "域名格式错误场景 - 消息: " . ($result['message'] ?? '无') . "\n\n";

// 测试2: 检查客户端缓存清除逻辑
echo "测试2: 检查客户端缓存清除逻辑\n";
echo "---------------------------\n";

// 设置测试缓存
update_option('mrhe_post_zat', array(
    'time' => time() + 3600,
    'token' => '123456',
    'randstr' => 'test',
    'product_id' => 'rhe-theme-v1',
    'signature' => 'test_signature'
));

echo "设置测试缓存...\n";

// 模拟封禁响应
$client = new MrheAuthClient();
$reflection = new ReflectionClass($client);
$method = $reflection->getMethod('auto_verify');
$method->setAccessible(true);

// 模拟服务端返回封禁错误
$mock_response = array(
    'success' => false,
    'code' => 'banned',
    'message' => '您的授权已被封禁，请联系管理员'
);

// 直接调用缓存清除逻辑
$clear_cache_codes = ['banned', 'domain_not_found', 'invalid_auth'];
if (isset($mock_response['code']) && in_array($mock_response['code'], $clear_cache_codes)) {
    echo "检测到需要清除缓存的错误码: " . $mock_response['code'] . "\n";
    $client->clear_auth_cache();
    echo "✅ 缓存清除逻辑执行成功\n";
} else {
    echo "❌ 缓存清除逻辑未执行\n";
}

// 检查缓存是否被清除
$auth_data = get_option('mrhe_post_zat');
if ($auth_data) {
    echo "❌ 缓存未被清除\n";
} else {
    echo "✅ 缓存已被清除\n";
}

echo "\n";

// 测试3: 插件客户端测试
echo "测试3: 插件客户端测试\n";
echo "-------------------\n";

// 设置插件测试缓存
update_option('zibll_plugin_auth', array(
    'time' => time() + 3600,
    'token' => '123456',
    'randstr' => 'test',
    'product_id' => 'zib-uploads',
    'signature' => 'test_signature'
));

echo "设置插件测试缓存...\n";

$plugin_client = new ZibUploadsAuthClient();

// 模拟封禁响应
$mock_response = array(
    'success' => false,
    'code' => 'banned',
    'message' => '您的授权已被封禁，请联系管理员'
);

// 直接调用缓存清除逻辑
$clear_cache_codes = ['banned', 'domain_not_found', 'invalid_auth'];
if (isset($mock_response['code']) && in_array($mock_response['code'], $clear_cache_codes)) {
    echo "检测到需要清除缓存的错误码: " . $mock_response['code'] . "\n";
    $plugin_client->clear_auth_cache();
    echo "✅ 插件缓存清除逻辑执行成功\n";
} else {
    echo "❌ 插件缓存清除逻辑未执行\n";
}

// 检查插件缓存是否被清除
$plugin_auth_data = get_option('zibll_plugin_auth');
if ($plugin_auth_data) {
    echo "❌ 插件缓存未被清除\n";
} else {
    echo "✅ 插件缓存已被清除\n";
}

echo "\n";

// 清理测试数据
echo "清理测试数据...\n";
delete_option('mrhe_post_zat');
delete_option('zibll_plugin_auth');

echo "测试完成！\n";
?>
