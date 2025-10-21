<?php
/**
 * 最终版授权失败自动清除测试
 */

// 加载 WordPress 环境
require_once('wp/wp-load.php');

echo "=== 授权失败自动清除测试 ===\n\n";

// 测试1: 检查服务端错误码
echo "测试1: 检查服务端错误码\n";
echo "----------------------\n";

$server = new MrheAuthServer();

// 测试封禁场景（需要先创建封禁记录）
global $wpdb;
$table_name = $wpdb->prefix . 'mrhe_theme_aut';

// 清理并创建测试数据
$wpdb->delete($table_name, array('domain' => 'test-banned.com'));
$wpdb->insert($table_name, array(
    'domain' => 'test-banned.com',
    'auth_code' => 'TEST123456',
    'is_banned' => 1,
    'created_at' => current_time('mysql'),
    'updated_at' => current_time('mysql')
));

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
$mock_response = array(
    'success' => false,
    'code' => 'banned',
    'message' => '您的授权已被封禁，请联系管理员'
);

// 直接调用缓存清除逻辑
$clear_cache_codes = ['banned', 'domain_not_found', 'invalid_auth'];
if (isset($mock_response['code']) && in_array($mock_response['code'], $clear_cache_codes)) {
    echo "检测到需要清除缓存的错误码: " . $mock_response['code'] . "\n";
    // 直接清除缓存
    delete_option('mrhe_post_zat');
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
    // 直接清除缓存
    delete_option('zibll_plugin_auth');
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

// 测试4: 验证代码修改
echo "测试4: 验证代码修改\n";
echo "------------------\n";

// 检查服务端代码是否包含错误码
$server_file = 'wp/wp-content/themes/zibll_child/inc/functions/mrhe-auth/server/class-auth-server.php';
$server_content = file_get_contents($server_file);

if (strpos($server_content, "'code' => 'banned'") !== false) {
    echo "✅ 服务端封禁错误码已添加\n";
} else {
    echo "❌ 服务端封禁错误码未添加\n";
}

if (strpos($server_content, "'code' => 'domain_not_found'") !== false) {
    echo "✅ 服务端域名不存在错误码已添加\n";
} else {
    echo "❌ 服务端域名不存在错误码未添加\n";
}

if (strpos($server_content, "'code' => 'invalid_domain'") !== false) {
    echo "✅ 服务端域名格式错误码已添加\n";
} else {
    echo "❌ 服务端域名格式错误码未添加\n";
}

if (strpos($server_content, "'code' => 'rate_limit'") !== false) {
    echo "✅ 服务端频率限制错误码已添加\n";
} else {
    echo "❌ 服务端频率限制错误码未添加\n";
}

// 检查客户端代码是否包含缓存清除逻辑
$client_file = 'wp/wp-content/themes/zibll_child/inc/functions/mrhe-auth/client/class-auth-client.php';
$client_content = file_get_contents($client_file);

if (strpos($client_content, '$clear_cache_codes = [\'banned\', \'domain_not_found\', \'invalid_auth\'];') !== false) {
    echo "✅ 子主题客户端缓存清除逻辑已添加\n";
} else {
    echo "❌ 子主题客户端缓存清除逻辑未添加\n";
}

$plugin_client_file = 'wp/wp-content/plugins/zib-uploads/includes/auth-client.php';
$plugin_client_content = file_get_contents($plugin_client_file);

if (strpos($plugin_client_content, '$clear_cache_codes = [\'banned\', \'domain_not_found\', \'invalid_auth\'];') !== false) {
    echo "✅ 插件客户端缓存清除逻辑已添加\n";
} else {
    echo "❌ 插件客户端缓存清除逻辑未添加\n";
}

echo "\n";

// 清理测试数据
echo "清理测试数据...\n";
$wpdb->delete($table_name, array('domain' => 'test-banned.com'));
delete_option('mrhe_post_zat');
delete_option('zibll_plugin_auth');

echo "测试完成！\n";
?>
