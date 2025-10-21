<?php
/**
 * 外观设置功能
 * 
 * 包含文章版权、全站变灰、后台自定义CSS等功能
 */

// 安全检测 - 防止直接访问文件
if (!defined('ABSPATH')) {
    die('禁止直接访问');
}

/**
 * 文章版权功能实现
 * 
 * 在文章内容底部添加版权信息
 */
function zibll_add_copyright($content) {
    if (is_single() && zibll_plugin_option('show_copyright')) {
        $text = zibll_plugin_option('copyright_text', '本文来自子比主题演示插件');
        $content .= '<div class="zibll-copyright" style="margin-top:20px;padding:10px;border-top:1px dashed #ddd;color:#888;">'
                  . esc_html($text) . '</div>';
    }
    return $content;
}
add_filter('the_content', 'zibll_add_copyright', 99);

/**
 * 全站变灰功能实现
 * 
 * 在网站头部插入CSS样式
 */
function zibll_site_greyscale() {
    if (zibll_plugin_option('site_greyscale')) {
        echo '<style>html{filter:grayscale(100%);transition:all .3s;}</style>';
    }
}
add_action('wp_head', 'zibll_site_greyscale', 99);

/**
 * 后台自定义CSS实现
 * 
 * 在后台头部插入用户自定义的CSS
 */
function zibll_admin_custom_css() {
    $css = zibll_plugin_option('admin_css');
    if ($css && !empty(trim($css))) {
        echo '<style>' . wp_strip_all_tags($css) . '</style>';
    }
}
add_action('admin_head', 'zibll_admin_custom_css', 99);

