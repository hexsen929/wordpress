<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|订单钩子处理
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 订单完成后生成授权记录
 * @param {int} $order_id 订单ID
 * @param {array} $order_data 订单数据
 */
function mrhe_create_auth_record($order_id, $order_data)
{
    //检查是否为授权产品
    $post_id = isset($order_data['post_id']) ? (int) $order_data['post_id'] : 0;
    if (!$post_id) {
        return;
    }
    
    //获取产品购买配置（CSF meta box）
    $product_meta = get_post_meta($post_id, 'mrhe_product_purchase', true);
    if (empty($product_meta['auth_enabled'])) {
            return;
        }
        
    // 获取 product_id（优先从订单数据获取，其次从产品配置获取，最后使用 post_id）
    $product_id = '';
    if (!empty($order_data['product_id'])) {
        // 优先使用订单中的 product_id
        $product_id = sanitize_text_field($order_data['product_id']);
    } elseif (!empty($product_meta['product_id'])) {
        // 其次使用产品配置的 product_id
        $product_id = sanitize_key($product_meta['product_id']);
    } else {
        // 默认使用 post_id
        $product_id = 'post_' . $post_id;
    }
    
    $user_id = isset($order_data['user_id']) ? (int) $order_data['user_id'] : 0;
    if (!$user_id) {
        return;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'mrhe_theme_aut';
    
    //检查是否已存在授权记录（只使用 user_id 和 post_id，一个用户购买一个产品只能有一条记录）
    $existing_record = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d AND post_id = %d",
        $user_id,
        $post_id
    ), ARRAY_A);
    
    if ($existing_record) {
        // 如果已存在记录，直接返回（不再需要同步 product_id）
        return;
    }
    
    //生成唯一授权码
    $auth_code = mrhe_generate_auth_code($user_id, $post_id);
    
    //获取最大域名数
    $max_domains = !empty($product_meta['auth_max_domains']) ? (int) $product_meta['auth_max_domains'] : 3;
    
    //插入授权记录（不再包含 product_id）
    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'post_id' => $post_id,
            'auth_code' => $auth_code,
            'domain' => serialize(array()),
            'is_authorized' => 1,
            'aut_max_url' => $max_domains
        ),
        array('%d', '%d', '%s', '%s', '%d', '%d')
    );
    
    //发送授权码到用户邮箱（可选）
    if (function_exists('zib_send_mail')) {
        $user = get_userdata($user_id);
        if ($user && $user->user_email) {
            $subject = 'mrhe主题授权码 - 订单号：' . $order_data['order_num'];
            $message = '您的授权码：' . $auth_code . "\n\n";
            $message .= '请在用户中心添加授权域名后使用。';
            
            zib_send_mail($user->user_email, $subject, $message);
        }
    }
}
add_action('zibpay_order_success', 'mrhe_create_auth_record', 10, 2);

/**
 * 为产品页面添加授权配置字段
 * @param {array} $fields 字段数组
 * @return {array} 修改后的字段数组
 */
function mrhe_add_product_auth_fields($fields)
{
    // 1. 启用授权管理开关
    $fields['mrhe_auth_enabled'] = array(
        'id' => 'mrhe_auth_enabled',
        'type' => 'switcher',
        'title' => '启用授权管理',
        'desc' => '开启后，用户购买此产品将自动生成授权码',
        'default' => false
    );
    
    // 2. 产品ID（新增）
    $fields['mrhe_product_id'] = array(
        'id' => 'mrhe_product_id',
        'type' => 'text',
        'title' => '产品ID',
        'desc' => '唯一标识此产品的ID，用于授权验证（留空将使用 post_ID）',
        'default' => '',
        'dependency' => array('mrhe_auth_enabled', '==', 'true'),
        'sanitize' => 'sanitize_key', // 只允许小写字母、数字、下划线、破折号
        'validate' => 'mrhe_validate_product_id'
    );
    
    // 3. 最大授权域名数
    $fields['mrhe_max_domains'] = array(
        'id' => 'mrhe_max_domains',
        'type' => 'number',
        'title' => '最大授权域名数',
        'desc' => '用户最多可以绑定多少个域名',
        'default' => 3,
        'min' => 1,
        'max' => 10,
        'dependency' => array('mrhe_auth_enabled', '==', 'true')
    );
    
    return $fields;
}
add_filter('zibpay_product_fields', 'mrhe_add_product_auth_fields');

/**
 * 验证 product_id 格式
 * @param {string} $value 输入值
 * @param {array} $field 字段配置
 * @param {array} $args 参数
 * @return {string|WP_Error} 验证结果
 */
function mrhe_validate_product_id($value, $field, $args) {
    if (empty($value)) {
        return ''; // 允许空值，将使用默认值
    }
    
    // 只允许小写字母、数字、下划线、破折号
    if (!preg_match('/^[a-z0-9_-]+$/', $value)) {
        return new WP_Error('invalid_product_id', 'product_id 只能包含小写字母、数字、下划线和破折号');
    }
    
    return $value;
}