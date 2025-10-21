<?php
/**
 * 会员功能核心文件
 * 
 * 本文件包含会员权限管理功能
 */

// 安全检测 - 防止直接访问文件
if (!defined('ABSPATH')) {
    die('禁止直接访问');
}

/**
 * 会员免评论查看功能
 * 
 * 让指定级别的会员用户无需评论即可查看"评论可见"的内容
 */
function zibll_vip_skip_comment_view($content) {
    // 检查是否启用了会员免评论功能
    $vip1_enabled = zibll_plugin_option('vip1_skip_comment_view', false);
    $vip2_enabled = zibll_plugin_option('vip2_skip_comment_view', false);
    
    if (!$vip1_enabled && !$vip2_enabled) {
        return $content;
    }
    
    // 检查用户是否登录
    if (!is_user_logged_in()) {
        return $content;
    }
    
    $user_id = get_current_user_id();
    
    // 检查用户VIP等级（假设使用子比主题的VIP系统）
    $user_vip_level = 0;
    if (function_exists('zib_get_user_vip_level')) {
        $user_vip_level = zib_get_user_vip_level($user_id);
    }
    
    // 根据VIP等级和设置决定是否跳过评论检查
    $should_skip = false;
    
    if ($vip2_enabled && $user_vip_level >= 2) {
        $should_skip = true;
    } elseif ($vip1_enabled && $user_vip_level >= 1) {
        $should_skip = true;
    }
    
    if ($should_skip) {
        // 移除评论可见的限制，直接显示内容
        // 这里需要根据子比主题的具体实现来调整
        remove_filter('the_content', 'zib_content_comment_view', 99);
    }
    
    return $content;
}
add_filter('the_content', 'zibll_vip_skip_comment_view', 10);

/**
 * 在评论可见内容处理之前检查VIP权限
 */
function zibll_check_vip_comment_permission($show_content, $post_id) {
    // 检查是否启用了会员免评论功能
    $vip1_enabled = zibll_plugin_option('vip1_skip_comment_view', false);
    $vip2_enabled = zibll_plugin_option('vip2_skip_comment_view', false);
    
    if (!$vip1_enabled && !$vip2_enabled) {
        return $show_content;
    }
    
    // 检查用户是否登录
    if (!is_user_logged_in()) {
        return $show_content;
    }
    
    $user_id = get_current_user_id();
    
    // 检查用户VIP等级
    $user_vip_level = 0;
    if (function_exists('zib_get_user_vip_level')) {
        $user_vip_level = zib_get_user_vip_level($user_id);
    }
    
    // 根据VIP等级和设置决定是否显示内容
    if ($vip2_enabled && $user_vip_level >= 2) {
        return true; // 二级会员可以查看
    } elseif ($vip1_enabled && $user_vip_level >= 1) {
        return true; // 一级会员可以查看
    }
    
    return $show_content; // 使用默认逻辑
}
// 这个钩子需要根据子比主题的具体实现来调整
// add_filter('zib_post_comment_view_permission', 'zibll_check_vip_comment_permission', 10, 2);

