<?php
/**
 * Plugin Name: 子比主题·功能增强插件
 * Plugin URI: https://www.hexsen.com/
 * Description: 为子比主题设计的功能增强插件，包含前端附件管理、多语言翻译、会员权限管理、社交登录等实用功能
 * Version: 2.0.0
 * Author: 何先生
 * Author URI: https://www.hexsen.com/
 **/

// 安全检测 - 防止直接访问此文件
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 判断依赖主题是否启用
 */
function zibll_plugin_error_notices()
{
    $con = '<div class="notice notice-error is-dismissible">
                <h3>插件错误！</h3>
                <p>此插件依赖于zibll主题，请先启用zibll子比主题</p></div>';
    echo $con;
}

if (get_template() != 'zibll') {
    add_action('admin_notices', 'zibll_plugin_error_notices');
    return;
}

/**
 * 定义插件常量 - 方便在插件其他文件中使用
 */
define('ZIBLL_PLUGIN_URL', plugins_url('', __FILE__));
define('ZIBLL_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * 获取插件配置项的值
 * 使用示例：$value = zibll_plugin_option('translation_enabled', false);
 * @param string $key 配置项的键
 * @param mixed $default 如果配置项不存在，返回的默认值
 * @return mixed 配置项的值或默认值
 */
function zibll_plugin_option($key = '', $default = null)
{
    // 声明静态变量以加快检索速度
    static $options = null;
    if ($options === null) {
        // 定义插件唯一的options储存KEY
        $options = get_option('zibll_plugin_option');
    }

    // 如果没有指定key，返回所有配置
    if (empty($key)) {
        return $options;
    }

    return isset($options[$key]) ? $options[$key] : $default;
}

/**
 * 插件初始化函数 - 在主题加载完成后执行
 */
function zibll_plugin_init() {
    // ========== 授权检查 START ==========
    // 1. 加载授权相关文件
    $auth_files = array(
        'includes/auth-client.php',
        'includes/auth-admin.php',
    );
    
    foreach ($auth_files as $file) {
        $file_path = plugin_dir_path(__FILE__) . $file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
    
    // 2. 检查授权状态
    if (class_exists('ZibUploadsAuthClient')) {
        $auth_client = ZibUploadsAuthClient::getInstance();
        
        // 如果未授权，限制功能
        if (!$auth_client->is_authorized()) {
            // 仅加载设置页面（用于授权管理）
            $options_file = plugin_dir_path(__FILE__) . 'options.php';
            if (file_exists($options_file)) {
                require_once $options_file;
            }
            
            // 显示授权提示
            add_action('admin_notices', 'zibll_plugin_auth_notice');
            
            // 生产环境：停止加载其他功能
            return;
        }
    }
    // ========== 授权检查 END ==========
    
    // 授权通过，正常加载所有功能
    $require_once = array(
        'action.php',                        // 前端附件管理
        'user.php',                          // 用户相关
        'options.php',                       // 后台设置面板
        'modules/translation/functions.php', // 翻译功能
        'modules/vip/functions.php',         // 会员权限
        'modules/payment/functions.php',     // 支付限制
        'modules/appearance/functions.php',  // 外观设置
        'modules/oauth/functions.php',       // 社交登录
    );

    // 循环加载所有必要文件
    foreach ($require_once as $require) {
        $file_path = plugin_dir_path(__FILE__) . $require;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
}
// 将初始化函数挂载到'zib_require_end'钩子
// 这个钩子在主题加载完成后触发，确保主题功能可用
add_action('zib_require_end', 'zibll_plugin_init');

/**
 * 显示插件未授权提示
 */
function zibll_plugin_auth_notice() {
    if (class_exists('ZibPluginAuth') && !ZibPluginAuth::is_aut() && !ZibPluginAuth::is_local()) {
        echo '<div class="notice notice-warning is-dismissible">
            <h3><i class="fa fa-shield"></i> 插件授权提示</h3>
            <p>子比主题·功能增强插件尚未授权，大部分功能暂不可用。</p>
            <p>
                <a href="' . admin_url('admin.php?page=zibll_plugin_option#tab=authorization') . '" class="button button-primary">
                    立即授权
                </a>
                <a href="https://e.hexsen.com/" target="_blank" class="button">
                    购买授权
                </a>
            </p>
        </div>';
    }
}

/**
 * 插件激活时刷新重写规则
 */
function zibll_plugin_activate() {
    // 确保重写规则被添加
    zibll_plugin_init();
    
    // OAuth功能：添加OAuth重写规则
    add_rewrite_rule('^oauth/([A-Za-z]+)$', 'index.php?oauth=$matches[1]', 'top');
    add_rewrite_rule('^oauth/([A-Za-z]+)/callback$', 'index.php?oauth=$matches[1]&oauth_callback=1', 'top');
    
    // 刷新重写规则
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'zibll_plugin_activate');

/**
 * 插件停用时刷新重写规则
 */
function zibll_plugin_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'zibll_plugin_deactivate');