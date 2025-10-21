<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-04-07 21:11:45
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2025 by Qinver, All Rights Reserved.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

function zib_shop_ajax_admin_shipping_submit()
{
    //非管理员禁止访问
    if (!current_user_can('administrator')) {
        zib_send_json_error('您没有权限访问此页面');
    }

    $order_id = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    $order_data = zibpay::get_order($order_id);
    if (!$order_data) {
        zib_send_json_error('订单不存在');
    }
    $order_meta_data = zibpay::get_meta($order_id, 'order_data', true);
    $delivery_remark = !empty($_REQUEST['delivery_remark']) ? sanitize_text_field($_REQUEST['delivery_remark']) : '';

    if ($order_meta_data['shipping_type'] === 'auto') {
        $delivery_content = !empty($_REQUEST['delivery_content']) ? $_REQUEST['delivery_content'] : '';
        if (!$delivery_content) {
            zib_send_json_error('请填写发货内容');
        }

        $shipping_data = [
            'delivery_content' => $delivery_content,
            'delivery_type'    => 'manual', //手动输入内容
            'delivery_remark'  => $delivery_remark,
        ];
    } elseif ($order_meta_data['shipping_type'] === 'manual') {
        $shipping_data = [
            'delivery_type'   => 'manual', //手动发货
            'delivery_remark' => $delivery_remark,
        ];
    } else {
        $express_number       = !empty($_REQUEST['express_number']) ? sanitize_text_field($_REQUEST['express_number']) : '';
        $express_company_name = !empty($_REQUEST['express_company_name']) ? sanitize_text_field($_REQUEST['express_company_name']) : '';
        $delivery_type        = !empty($_REQUEST['delivery_type']) ? ($_REQUEST['delivery_type'] == 'no_express' ? 'no_express' : 'express') : '';

        if ($delivery_type != 'no_express' && (!$express_number || !$express_company_name)) {
            zib_send_json_error('请填写完整的物流信息');
        }

        $shipping_data = [
            'express_number'       => $express_number,
            'express_company_name' => $express_company_name,
            'delivery_remark'      => $delivery_remark,
            'delivery_type'        => $delivery_type,
        ];
    }

    //手动发货
    zib_shop_manual_shipping($order_data, $shipping_data);

    zib_send_json_success('发货成功');
}
add_action('wp_ajax_admin_shipping_submit', 'zib_shop_ajax_admin_shipping_submit');

function zib_shop_ajax_admin_after_sale_handle_submit()
{
    //非管理员禁止访问
    if (!current_user_can('administrator')) {
        zib_send_json_error('您没有权限访问此页面');
    }

    $order_id = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    $order = zibpay::get_order($order_id);
    if (!$order) {
        zib_send_json_error('订单不存在');
    }

    $handle_type     = !empty($_REQUEST['handle_type']) ? sanitize_text_field($_REQUEST['handle_type']) : '';
    $author_remark   = !empty($_REQUEST['author_remark']) ? sanitize_text_field($_REQUEST['author_remark']) : '';
    $refund_channel  = !empty($_REQUEST['refund_channel']) ? sanitize_text_field($_REQUEST['refund_channel']) : '';
    $return_address  = !empty($_REQUEST['return_address']) ? $_REQUEST['return_address'] : [];
    $consignee_phone = $return_address['phone'] ?? '';

    $is_agreed       = $handle_type === 'agree';
    $order_data      = zibpay::get_meta($order_id, 'order_data');
    $after_sale_data = $order_data['after_sale_data'];
    $after_sale_type = $after_sale_data['type'];
    $is_points       = $order_data['pay_modo'] === 'points';

    $handle_data = [
        'author_remark' => $author_remark,
    ];

    if (!$is_agreed && !$author_remark) {
        zib_send_json_error('请填写拒绝原因等信息');
    }

    if ($is_agreed && !$refund_channel && !$is_points && in_array($after_sale_type, ['refund', 'insured_price'])) {
        zib_send_json_error('请选择退款到用户的渠道');
    }

    if ($is_agreed && (!$return_address || !$consignee_phone) && in_array($after_sale_type, ['refund_return', 'replacement', 'warranty'])) {
        zib_send_json_error('请选择退货地址');
    }

    if ($is_agreed) {
        switch ($after_sale_type) {
            case 'refund':
                //退款
                $handle_data['refund_channel'] = $refund_channel;
                $send_msg                      = '已同意退款';
                break;
            case 'refund_return':
                //退货退款
                $handle_data['return_address'] = $return_address;
                $send_msg                      = '已同意退货退款';
                break;
            case 'replacement':
                //换货
                $handle_data['return_address'] = $return_address;
                $send_msg                      = '已同意换货';
                break;
            case 'warranty':
                //保修
                $handle_data['return_address'] = $return_address;
                $send_msg                      = '已同意保修';
                break;
            case 'insured_price':
                //保价
                $handle_data['refund_channel'] = $refund_channel;
                $send_msg                      = '已同意保价';
                break;
        }
    } else {
        $send_msg = '已拒绝此售后申请';
    }

    zib_shop_after_sale_author_handle($order, $is_agreed, $handle_data);

    zib_send_json_success($send_msg);
}
add_action('wp_ajax_admin_after_sale_handle_submit', 'zib_shop_ajax_admin_after_sale_handle_submit');

function zib_shop_ajax_admin_after_sale_refund_return_handle()
{
    //非管理员禁止访问
    if (!current_user_can('administrator')) {
        zib_send_json_error('您没有权限访问此页面');
    }

    $order_id = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    $order = zibpay::get_order($order_id);
    if (!$order) {
        zib_send_json_error('订单不存在');
    }

    $order_data      = zibpay::get_meta($order_id, 'order_data');
    $after_sale_data = $order_data['after_sale_data'];

    if ($after_sale_data['type'] != 'refund_return') {
        zib_send_json_error('当前售后类型不支持退货退款');
    }

    if ($after_sale_data['status'] != 2) {
        zib_send_json_error('当前售后状态不支持退货退款');
    }

    $author_remark  = !empty($_REQUEST['author_remark']) ? sanitize_text_field($_REQUEST['author_remark']) : '';
    $refund_channel = !empty($_REQUEST['refund_channel']) ? sanitize_text_field($_REQUEST['refund_channel']) : '';

    if (!$refund_channel && $order_data['pay_modo'] != 'points') {
        zib_send_json_error('请选择退款到用户的渠道');
    }

    zib_shop_after_sale_refund_return_author_handle($order, [
        'author_remark'  => $author_remark,
        'refund_channel' => $refund_channel,
    ]);

    zib_send_json_success('退货退款成功');
}
add_action('wp_ajax_admin_after_sale_refund_return_handle', 'zib_shop_ajax_admin_after_sale_refund_return_handle');

function zib_shop_ajax_after_sale_express_data()
{
    $order_id = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $order    = zibpay::get_order($order_id);
    if (!$order) {
        zib_send_json_error('订单不存在');
    }
    //判断权限：自己或管理员
    if ($order['user_id'] != get_current_user_id() && $order['post_author'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_send_json_error('权限不足');
    }

    $type = !empty($_REQUEST['type']) ? sanitize_text_field($_REQUEST['type']) : 'user_return';

    $express_data = zib_shop_get_after_sale_express_data($order_id, $type);

    zib_send_json_success([
        'express_data' => $express_data,
    ]);
}
add_action('wp_ajax_after_sale_express_data', 'zib_shop_ajax_after_sale_express_data');
