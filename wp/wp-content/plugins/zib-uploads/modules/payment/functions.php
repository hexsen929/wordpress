<?php
/**
 * 支付限制功能
 * 
 * 通过过滤器修改支付权限
 */

// 安全检测 - 防止直接访问文件
if (!defined('ABSPATH')) {
    die('禁止直接访问');
}

/**
 * 支付限制功能实现
 * 
 * 通过过滤器修改支付权限
 */
function zibll_filter_balance_pay($allow, $pay_type) {
    $prohibited = zibll_plugin_option('prohibited_balance_pay_types', array());
    if (in_array($pay_type, $prohibited)) {
        return false;
    }
    return $allow;
}
add_filter('zibpay_is_allow_balance_pay', 'zibll_filter_balance_pay', 10, 2);

