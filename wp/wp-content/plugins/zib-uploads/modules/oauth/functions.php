<?php
/**
 * 社交登录功能 - Google & MixAuth QQ登录
 * 完全基于子比主题的官方机制实现
 */

// 安全检测
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 独立插件社交登录实现
 * 
 * 策略：利用子主题已有的Google和MixAuth QQ支持
 * 通过配置桥接让插件的设置被子主题识别
 * 这样无论有没有子主题都能工作
 */

/**
 * 检查插件社交登录功能是否启用
 */
function zibll_plugin_social_login_enabled() {
    if (!function_exists('zibll_plugin_option')) {
        return false;
    }
    
    return zibll_plugin_option('google_enable', false) || zibll_plugin_option('mixauthqq_enable', false);
}

/**
 * 配置桥接：让插件设置被主题/子主题识别
 * 
 * 1. 如果有子主题，通过 option_mrhe_options 过滤器桥接到 _mrhe() 函数
 * 2. 如果只有父主题，通过 JavaScript 动态添加按钮
 */

// 插件现在直接通过filter提供OAuth功能，不需要桥接

/**
 * 将插件的OAuth类型添加到主题的社交登录类型列表
 * 使用更简单的方法：直接hook到WordPress的filter系统
 */
function zibll_plugin_add_oauth_types_to_social_data($args) {
    if (!is_array($args)) {
        $args = array();
    }
    
    // 检查插件是否启用了OAuth功能
    if (!zibll_plugin_social_login_enabled()) {
        return $args;
    }
    
    // Google登录 - 直接覆盖，插件配置优先
    if (zibll_plugin_option('google_enable', false)) {
        $args['google'] = array(
            'name'  => 'Google',
            'type'  => 'google',
            'class' => 'c-blue',
            'icon'  => 'fa fa-google',
        );
    }
    
    // MixAuth QQ登录 - 直接覆盖，插件配置优先
    if (zibll_plugin_option('mixauthqq_enable', false)) {
        $args['mixauthqq'] = array(
            'name'  => 'QQ',
            'type'  => 'mixauthqq',
            'class' => 'c-blue',
            'icon'  => 'fa fa-qq',
        );
    }
    
    return $args;
}

// 尝试hook到主题的social type filter（如果存在）
add_filter('zib_social_type_data', 'zibll_plugin_add_oauth_types_to_social_data', 99);

/**
 * 直接注入OAuth登录按钮（不依赖主题filter）
 * 模仿TikTok插件的方式
 */
function zibll_plugin_inject_oauth_buttons() {
    if (!zibll_plugin_social_login_enabled()) {
        return;
    }
    
    $google_enabled = zibll_plugin_option('google_enable', false);
    $mixauthqq_enabled = zibll_plugin_option('mixauthqq_enable', false);
    
    if (!$google_enabled && !$mixauthqq_enabled) {
        return;
    }
    ?>
    <style>
    /* Google登录按钮样式 */
    .social-login-item.google {
        background: #4285f4 !important;
        color: #fff !important;
        position: relative;
        width: 32px !important;
        height: 32px !important;
        line-height: 32px !important;
        text-align: center;
        border-radius: 50px;
        margin: 3px 5px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        vertical-align: middle;
    }
    
    .social-login-item.google:hover {
        opacity: .8;
        color: #fff !important;
        text-decoration: none;
    }

    .social-login-item.google.button-lg {
        width: 120px !important;
        font-size: 14px;
        justify-content: center;
    }

    .social-login-item.google.button-lg .fa-google {
        margin-right: 6px;
    }

    /* MixAuth QQ登录按钮样式 */
    .social-login-item.mixauthqq {
        background: #12b7f5 !important;
        color: #fff !important;
        position: relative;
        width: 32px !important;
        height: 32px !important;
        line-height: 32px !important;
        text-align: center;
        border-radius: 50px;
        margin: 3px 5px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        vertical-align: middle;
    }
    
    .social-login-item.mixauthqq:hover {
        opacity: .8;
        color: #fff !important;
        text-decoration: none;
    }

    .social-login-item.mixauthqq.button-lg {
        width: 120px !important;
        font-size: 14px;
        justify-content: center;
    }

    .social-login-item.mixauthqq.button-lg .fa-qq {
        margin-right: 6px;
    }
    </style>
    <script>
    jQuery(document).ready(function($) {
        // 插入登录按钮的函数
        function insertOAuthButtons() {
            $('.social_loginbar').each(function() {
                var $container = $(this);
                
                <?php if ($google_enabled): ?>
                // Google登录按钮
                if ($container.find('.social-login-item.google').length === 0) {
                    var googleButtonClass = '<?php echo _pz('oauth_button_lg') ? 'button-lg' : 'toggle-radius'; ?>';
                    var googleButtonText = googleButtonClass === 'button-lg' ? 'Google登录' : '';
                    var googleUrl = '<?php echo esc_url(zib_get_oauth_login_url('google')); ?>';
                    var googleButton = '<a rel="nofollow" title="Google登录" href="' + googleUrl + '" class="social-login-item google ' + googleButtonClass + '"><i class="fa fa-google" aria-hidden="true"></i>' + googleButtonText + '</a>';
                    $container.append(googleButton);
                }
                <?php endif; ?>
                
                <?php if ($mixauthqq_enabled): ?>
                // MixAuth QQ登录按钮
                if ($container.find('.social-login-item.mixauthqq').length === 0) {
                    var qqButtonClass = '<?php echo _pz('oauth_button_lg') ? 'button-lg' : 'toggle-radius'; ?>';
                    var qqButtonText = qqButtonClass === 'button-lg' ? 'QQ登录' : '';
                    var qqUrl = '<?php echo esc_url(zib_get_oauth_login_url('mixauthqq')); ?>';
                    var qqButton = '<a rel="nofollow" title="QQ登录" href="' + qqUrl + '" class="social-login-item mixauthqq ' + qqButtonClass + '"><i class="fa fa-qq" aria-hidden="true"></i>' + qqButtonText + '</a>';
                    $container.append(qqButton);
                }
                <?php endif; ?>
            });
        }

        // 初始插入
        insertOAuthButtons();

        // 监听 DOM 变化
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1 && (
                            $(node).hasClass('social_loginbar') || 
                            $(node).find('.social_loginbar').length
                        )) {
                            insertOAuthButtons();
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'zibll_plugin_inject_oauth_buttons');

/**
 * 获取OAuth配置 - 与子主题保持一致
 */
function zibll_plugin_get_oauth_config($type = 'google') {
    $defaults = array(
        'appid'         => '',
        'appkey'        => '',
        'backurl'       => home_url('/oauth/' . $type . '/callback'),
        'agent'         => false,
        'appkrivatekey' => '',
        'auto_reply'    => array(),
    );
    
    if ($type === 'google') {
        $config = array(
            'appid'   => zibll_plugin_option('google_client_id'),
            'appkey'  => zibll_plugin_option('google_client_secret'),
            'backurl' => home_url('/oauth/google/callback'),
        );
        return wp_parse_args($config, $defaults);
    }
    
    if ($type === 'mixauthqq') {
        $config = array(
            'server_url' => zibll_plugin_option('mixauthqq_server_url'),
            'backurl'    => home_url('/oauth/mixauthqq/callback'),
            'integration_mode' => zibll_plugin_option('mixauthqq_integration_mode', 'api'),
        );
        return wp_parse_args($config, $defaults);
    }
    
    return $defaults;
}

/**
 * Google登录URL Filter - 与子主题完全一致
 */
function zibll_plugin_oauth_google_login_url_filter($url, $type) {
    if ($type === 'google' && zibll_plugin_option('google_enable', false)) {
        $config = zibll_plugin_get_oauth_config('google');
        if (!empty($config['appid']) && !empty($config['appkey'])) {
            $url = home_url('oauth/google');
        }
    }
    return $url;
}
add_filter('zib_oauth_login_url', 'zibll_plugin_oauth_google_login_url_filter', 5, 2);

/**
 * MixAuth QQ登录URL Filter - 与子主题完全一致
 */
function zibll_plugin_oauth_mixauthqq_login_url_filter($url, $type) {
    if ($type === 'mixauthqq' && zibll_plugin_option('mixauthqq_enable', false)) {
        // 只要功能启用就提供URL，不依赖server_url配置
        $url = home_url('oauth/mixauthqq');
    }
    return $url;
}
add_filter('zib_oauth_login_url', 'zibll_plugin_oauth_mixauthqq_login_url_filter', 5, 2);

/**
 * AJAX处理函数：代理MixAuth状态检查请求 - 与子主题完全一致
 */
function zibll_plugin_mixauthqq_check_status_ajax() {
    header('Content-Type: application/json');
    
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mixauthqq_status')) {
        http_response_code(403);
        echo json_encode(['error' => '安全验证失败']);
        wp_die();
    }
    
    $server_url = isset($_POST['server_url']) ? sanitize_url($_POST['server_url']) : '';
    $qr_id = isset($_POST['qr_id']) ? sanitize_text_field($_POST['qr_id']) : '';
    
    if (empty($server_url) || empty($qr_id)) {
        http_response_code(400);
        echo json_encode(['error' => '参数缺失']);
        wp_die();
    }
    
    $response = wp_remote_post($server_url . '/api/status', array(
        'body' => json_encode(array(
            'type' => 'qq',
            'id' => $qr_id
        )),
        'headers' => array(
            'Content-Type' => 'application/json',
            'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url(),
        ),
        'timeout' => 15,
        'sslverify' => false,
        'blocking' => true,
    ));
    
    if (is_wp_error($response)) {
        http_response_code(500);
        echo json_encode(['error' => 'MixAuth服务请求失败: ' . $response->get_error_message()]);
        wp_die();
    }
    
    $body = wp_remote_retrieve_body($response);
    $http_code = wp_remote_retrieve_response_code($response);
    
    $json_data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(502);
        echo json_encode(['error' => 'MixAuth服务返回无效数据']);
        wp_die();
    }
    
    http_response_code($http_code);
    echo $body;
    wp_die();
}
add_action('wp_ajax_mixauthqq_check_status', 'zibll_plugin_mixauthqq_check_status_ajax');
add_action('wp_ajax_nopriv_mixauthqq_check_status', 'zibll_plugin_mixauthqq_check_status_ajax');


/**
 * 简化的回退方案 - 现在主要通过filter和类型注册自动显示按钮
 * 保留此函数作为备用方案，但通常不需要
 */
function zibll_plugin_add_fallback_buttons() {
    // 现在通过标准的filter和类型注册，按钮会自动显示
    // 此函数保留作为备用方案，但通常不需要执行
    return;
}

// 启用回退方案
add_action('wp_head', 'zibll_plugin_add_fallback_buttons');



/**
 * 注册OAuth路由重写规则 - 与子主题完全一致
 */
function zibll_plugin_oauth_rewrite_rules($wp_rewrite) {
    if ($ps = get_option('permalink_structure')) {
        $new_rules['oauth/([A-Za-z]+)$']          = 'index.php?oauth=$matches[1]';
        $new_rules['oauth/([A-Za-z]+)/callback$'] = 'index.php?oauth=$matches[1]&oauth_callback=1';
        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }
}
add_action('generate_rewrite_rules', 'zibll_plugin_oauth_rewrite_rules');

/**
 * 注册OAuth相关的query变量
 * 没有这个，WordPress会丢弃URL中的oauth参数，导致路由失效
 */
function zibll_plugin_oauth_query_vars($public_query_vars) {
    // 仅在非后台环境注册 - 与子主题保持一致
    if (!is_admin()) {
        $public_query_vars[] = 'oauth';
        $public_query_vars[] = 'oauth_callback';
    }
    return $public_query_vars;
}
add_filter('query_vars', 'zibll_plugin_oauth_query_vars');

/**
 * 处理OAuth模板路由
 */
function zibll_plugin_oauth_template() {
    $oauth = strtolower(get_query_var('oauth'));
    $oauth_callback = get_query_var('oauth_callback');
    
    // 仅处理插件支持的类型
    $allow_list = array('google', 'mixauthqq');
    
    if ($oauth && in_array($oauth, $allow_list)) {
        // 检查插件功能是否启用
        if (!zibll_plugin_social_login_enabled()) {
            return;
        }
        
        // 检查对应类型是否启用
        if ($oauth === 'google' && !zibll_plugin_option('google_enable', false)) {
            return;
        }
        if ($oauth === 'mixauthqq' && !zibll_plugin_option('mixauthqq_enable', false)) {
            return;
        }
        
        global $wp_query;
        $wp_query->is_home = false;
        $wp_query->is_page = false;
        
        // 构造模板文件路径
        $template_file = $oauth_callback ? '/callback.php' : '/login.php';
        $template = ZIBLL_PLUGIN_PATH . 'modules/oauth/' . $oauth . $template_file;
        
        if (file_exists($template)) {
            load_template($template);
            exit;
        } else {
            wp_die('模板文件不存在：' . esc_html($template_file));
        }
    }
}
add_action('template_redirect', 'zibll_plugin_oauth_template', 4);



/**
 * 用户中心OAuth绑定支持 - 已移除重复实现
 * 现在通过zib_get_social_type_data()统一处理，避免重复显示
 */

/**
 * 生成OAuth绑定HTML - 已移除
 * 现在通过主题的zib_oauth_set函数统一处理
 */
function zibll_plugin_generate_oauth_binding_html_removed($user_id, $type, $name, $icon) {
    $rurl = function_exists('zib_get_user_center_url') ? zib_get_user_center_url('account') : home_url();
    $bind_href = function_exists('zib_get_oauth_login_url') ? 
        zib_get_oauth_login_url($type, $rurl) : 
        home_url('/oauth/' . $type);
    
    if (!$bind_href) {
        return '';
    }
    
    $bind_href = add_query_arg('bind', $type, $bind_href);
    
    // 兼容性检查：确保在所有环境下都能正确读取绑定状态
    $oauth_info = null;
    $oauth_id = get_user_meta($user_id, 'oauth_' . $type . '_openid', true);
    
    // 首先尝试使用主题的用户元数据函数
    if (function_exists('zib_get_user_meta')) {
        $oauth_info = zib_get_user_meta($user_id, 'oauth_' . $type . '_getUserInfo', true);
    }
    
    // 如果主题函数没有返回数据，尝试直接从用户元数据表读取
    if (!$oauth_info) {
        $oauth_info = get_user_meta($user_id, 'oauth_' . $type . '_getUserInfo', true);
    }
    
    // 如果还是没有数据，但是有openid，可能是数据存储位置不同
    // 在这种情况下，我们创建基本的绑定信息显示
    if (!$oauth_info && $oauth_id) {
        $oauth_info = array(
            'name' => $name,
            'avatar' => ''
        );
    }
    
    
    if ($oauth_info && $oauth_id) {
        // 已绑定状态
        $user_name = !empty($oauth_info['name']) ? esc_attr($oauth_info['name']) : $name . '账号';
        $user_avatar = !empty($oauth_info['avatar']) ? $oauth_info['avatar'] : '';
        $avatar_html = '';
        
        if ($user_avatar && function_exists('zib_get_lazy_attr')) {
            $lazy_attr = zib_get_lazy_attr('lazy_avatar', $user_avatar, 'avatar', ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-null.svg');
            $avatar_html = '<span class="avatar-img avatar-sm mr6" style="--this-size: 22px;"><img ' . $lazy_attr . ' alt="' . $name . '头像"></span>';
        }
        
        $desc = $avatar_html ? $avatar_html . $user_name : $user_name;
        $desc = '<div class="muted-2-color text-ellipsis mr10 ml20" data-toggle="tooltip" title="已绑定' . $name . '账号">' . $desc . '</div>';
        $btn = '<a data-toggle="tooltip" href="javascript:;" openid="' . esc_attr($oauth_id) . '" title="解绑' . $name . '账号" user-id="' . $user_id . '" untying-type="' . $type . '" class="em09 p2-10 oauth-untying but hollow c-yellow">解绑</a>';
    } else {
        // 未绑定状态
        $desc = '<div class="muted-2-color">暂未绑定</div>';
        $btn = '<a title="绑定' . $name . '账号" href="' . esc_url($bind_href) . '" class="em09 p2-10 but hollow c-blue">绑定</a>';
    }
    
    $html = '<div class="mb10"><div class="flex ac jsb muted-box">';
    $html .= '<div class="flex ac type-logo"><span class="social-login-item circular mr6 em12 ' . $type . '"><i class="' . $icon . '" aria-hidden="true"></i></span><span class="">' . $name . '</span></div>';
    $html .= '<div class="overflow-hidden">' . $desc . '</div>';
    $html .= '<div class="shrink0">' . $btn . '</div>';
    $html .= '</div></div>';
    
    return $html;
}

/**
 * 处理OAuth解绑请求
 */
function zibll_plugin_handle_oauth_untying() {
    if (empty($_POST['user_id']) || empty($_POST['type'])) {
        return; // 让其他处理函数继续
    }
    
    $type = $_POST['type'];
    
    // 只处理我们支持的OAuth类型
    if (!in_array($type, array('google', 'mixauthqq'))) {
        return; // 让其他处理函数继续
    }
    
    // 检查插件功能是否启用
    if (!zibll_plugin_social_login_enabled()) {
        return; // 让其他处理函数继续
    }
    
    $user_id = (int)$_POST['user_id'];
    $current_user_id = get_current_user_id();
    
    // 安全检查
    if (!$current_user_id || $current_user_id != $user_id) {
        wp_send_json_error(array('msg' => '权限不足', 'ys' => 'danger'));
        exit;
    }
    
    // 删除绑定信息
    delete_user_meta($user_id, 'oauth_' . $type . '_openid');
    if (function_exists('zib_update_user_meta')) {
        zib_update_user_meta($user_id, 'oauth_' . $type . '_getUserInfo', false);
    } else {
        delete_user_meta($user_id, 'oauth_' . $type . '_getUserInfo');
    }
    
    // 返回成功结果
    $goto = function_exists('zib_get_user_center_url') ? zib_get_user_center_url('account') : home_url();
    $type_name = $type === 'google' ? 'Google' : 'MixAuth QQ';
    wp_send_json(array(
        'error' => 0,
        'msg' => '已解除' . $type_name . '绑定',
        'ys' => 'success',
        'reload' => true,
        'goto' => $goto
    ));
    exit;
}
add_action('wp_ajax_user_oauth_untying', 'zibll_plugin_handle_oauth_untying', 5);

/**
 * 插件激活时刷新rewrite规则
 * 确保OAuth路由规则被正确注册
 */
function zibll_plugin_oauth_flush_rewrites() {
    // 刷新rewrite规则，使新的路由生效
    flush_rewrite_rules();
}

// 注册激活钩子
register_activation_hook(ZIBLL_PLUGIN_PATH . 'index.php', 'zibll_plugin_oauth_flush_rewrites');

// 注册停用钩子，清理规则
register_deactivation_hook(ZIBLL_PLUGIN_PATH . 'index.php', 'zibll_plugin_oauth_flush_rewrites');

/**
 * 添加OAuth按钮样式 - 确保与子主题一致
 */
function zibll_plugin_oauth_styles() {
    if (!is_admin()) {
        echo '<style>
        .social-login-item.mixauthqq {
            background: #5dbef4 !important;
        }
        </style>';
    }
}
add_action('wp_head', 'zibll_plugin_oauth_styles');

/**
 * 扩展后台用户列表的社交登录显示 - 支持插件OAuth类型
 */
function zibll_plugin_extend_admin_oauth_display($var, $column_name, $user_id) {
    // 只处理社交登录列
    if ($column_name !== 'oauth') {
        return $var;
    }
    
    // 获取所有可用的OAuth类型，包括插件添加的类型
    $social_types = zib_get_social_type_data();
    $args = array();
    foreach ($social_types as $type => $data) {
        $args[] = array(
            'name' => $data['name'],
            'type' => $type,
        );
    }
    
    $oauth = array();
    foreach ($args as $arg) {
        $name = $arg['name'];
        $type = $arg['type'];

        $bind_href = zib_get_oauth_login_url($type);
        if ($bind_href) {
            $oauth_info = zib_get_user_meta($user_id, 'oauth_' . $type . '_getUserInfo', true);
            $oauth_id   = get_user_meta($user_id, 'oauth_' . $type . '_openid', true);
            if ($oauth_info && $oauth_id) {
                $oauth[] = $name;
            }
        }
    }

    $html = $oauth ? '已绑定' . implode('、', $oauth) : '未绑定社交账号';
    $user = get_userdata($user_id);
    $phone_number = get_user_meta($user->ID, 'phone_number', true);
    $html .= $phone_number ? '<br>' . $phone_number : '<br>未绑定手机号';
    
    return '<div style="font-size: 12px;">' . $html . '</div>';
}
add_filter('manage_users_custom_column', 'zibll_plugin_extend_admin_oauth_display', 10, 3);

/**
 * 用户中心OAuth绑定显示支持
 * 现在通过父主题的zib_get_social_type_data()过滤器自动处理
 * 不需要单独的用户中心显示函数
 */

/**
 * 由于子主题重写了zib_oauth_set函数，我们需要通过其他方式确保插件OAuth类型被正确显示
 * 通过修改子主题的zib_oauth_set函数来支持插件OAuth类型
 */
function zibll_plugin_ensure_oauth_types_in_child_theme() {
    // 检查子主题是否重写了zib_oauth_set函数
    if (function_exists('zib_oauth_set') && 
        function_exists('zib_get_social_type_data') && 
        !has_filter('zib_social_type_data', 'zibll_plugin_add_oauth_types_to_social_data')) {
        
        // 如果子主题重写了zib_oauth_set但没有使用filter机制，我们需要确保OAuth类型被正确注册
        add_filter('zib_social_type_data', 'zibll_plugin_add_oauth_types_to_social_data', 10, 1);
    }
}
add_action('init', 'zibll_plugin_ensure_oauth_types_in_child_theme', 20);

/**
 * 控制OAuth类型的启用状态
 * 让插件配置覆盖子主题配置
 */
function zibll_plugin_oauth_enabled_filter($enabled, $type) {
    if ($type === 'google' && zibll_plugin_option('google_enable', false)) {
        $config = zibll_plugin_get_oauth_config('google');
        return !empty($config['appid']) && !empty($config['appkey']);
    }
    
    if ($type === 'mixauthqq' && zibll_plugin_option('mixauthqq_enable', false)) {
        $config = zibll_plugin_get_oauth_config('mixauthqq');
        return !empty($config['server_url']) || $config['integration_mode'] === 'local';
    }
    
    return $enabled;
}
add_filter('zib_oauth_google_enabled', 'zibll_plugin_oauth_enabled_filter', 5, 2);
add_filter('zib_oauth_mixauthqq_enabled', 'zibll_plugin_oauth_enabled_filter', 5, 2);