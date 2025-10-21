<?php
/**
 * 测试授权失败自动清除功能
 * 验证封禁、域名不存在、网络错误三种场景
 */

// 加载 WordPress 环境
require_once('wp/wp-load.php');

echo "=== 授权失败自动清除测试 ===\n\n";

// 测试1: 封禁场景
echo "测试1: 封禁场景\n";
echo "----------------\n";

// 模拟封禁的授权记录
global $wpdb;
$table_name = $wpdb->prefix . 'mrhe_theme_aut';

// 先清理测试数据
$wpdb->delete($table_name, array('domain' => 'test-banned.com'));

// 插入封禁的测试记录
$wpdb->insert($table_name, array(
    'domain' => 'test-banned.com',
    'product_id' => 'rhe-theme-v1',
    'auth_code' => 'TEST123456',
    'is_banned' => 1,
    'created_at' => current_time('mysql'),
    'updated_at' => current_time('mysql')
));

// 模拟客户端授权验证
$client = new MrheAuthClient();
$result = $client->auto_verify();

echo "验证结果: " . ($result['success'] ? '成功' : '失败') . "\n";
echo "错误信息: " . ($result['message'] ?? '无') . "\n";
echo "错误码: " . ($result['code'] ?? '无') . "\n";

// 检查缓存是否被清除
$auth_data = get_option('mrhe_post_zat');
if ($auth_data) {
    echo "❌ 缓存未被清除（应该被清除）\n";
} else {
    echo "✅ 缓存已被清除\n";
}

echo "\n";

// 测试2: 域名不存在场景
echo "测试2: 域名不存在场景\n";
echo "-------------------\n";

// 清理测试数据
$wpdb->delete($table_name, array('domain' => 'test-nonexistent.com'));

// 模拟客户端授权验证（域名不存在）
$client = new MrheAuthClient();
$result = $client->auto_verify();

echo "验证结果: " . ($result['success'] ? '成功' : '失败') . "\n";
echo "错误信息: " . ($result['message'] ?? '无') . "\n";
echo "错误码: " . ($result['code'] ?? '无') . "\n";

// 检查缓存是否被清除
$auth_data = get_option('mrhe_post_zat');
if ($auth_data) {
    echo "❌ 缓存未被清除（应该被清除）\n";
} else {
    echo "✅ 缓存已被清除\n";
}

echo "\n";

// 测试3: 网络错误场景（模拟）
echo "测试3: 网络错误场景\n";
echo "------------------\n";

// 先设置一个有效的授权缓存
update_option('mrhe_post_zat', array(
    'time' => time() + 3600, // 1小时后过期
    'token' => '123456',
    'randstr' => 'test',
    'product_id' => 'rhe-theme-v1',
    'signature' => 'test_signature'
));

echo "设置测试缓存...\n";

// 模拟网络错误（通过修改API URL）
$original_url = MrheAuthClient::API_BASE_URL;
$reflection = new ReflectionClass('MrheAuthClient');
$property = $reflection->getProperty('API_BASE_URL');
$property->setAccessible(true);
$property->setValue(null, 'http://invalid-url-for-testing.com');

$client = new MrheAuthClient();
$result = $client->auto_verify();

echo "验证结果: " . ($result['success'] ? '成功' : '失败') . "\n";
echo "错误信息: " . ($result['message'] ?? '无') . "\n";

// 检查缓存是否被清除（网络错误不应该清除缓存）
$auth_data = get_option('mrhe_post_zat');
if ($auth_data) {
    echo "✅ 缓存未被清除（网络错误时不应清除）\n";
} else {
    echo "❌ 缓存被错误清除了（网络错误时不应清除）\n";
}

// 恢复原始URL
$property->setValue(null, $original_url);

echo "\n";

// 测试4: 插件客户端测试
echo "测试4: 插件客户端测试\n";
echo "-------------------\n";

// 测试插件客户端的封禁场景
$plugin_client = new ZibUploadsAuthClient();

// 设置插件授权缓存
update_option('zibll_plugin_auth', array(
    'time' => time() + 3600,
    'token' => '123456',
    'randstr' => 'test',
    'product_id' => 'zib-uploads',
    'signature' => 'test_signature'
));

echo "设置插件测试缓存...\n";

$result = $plugin_client->auto_verify();

echo "插件验证结果: " . ($result['success'] ? '成功' : '失败') . "\n";
echo "错误信息: " . ($result['message'] ?? '无') . "\n";

// 检查插件缓存是否被清除
$plugin_auth_data = get_option('zibll_plugin_auth');
if ($plugin_auth_data) {
    echo "❌ 插件缓存未被清除（应该被清除）\n";
} else {
    echo "✅ 插件缓存已被清除\n";
}

echo "\n";

// 清理测试数据
echo "清理测试数据...\n";
$wpdb->delete($table_name, array('domain' => 'test-banned.com'));
$wpdb->delete($table_name, array('domain' => 'test-nonexistent.com'));
delete_option('mrhe_post_zat');
delete_option('zibll_plugin_auth');

echo "测试完成！\n";
?>
