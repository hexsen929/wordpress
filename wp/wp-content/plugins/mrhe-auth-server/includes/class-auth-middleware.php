<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2025-01-15 10:00:00
 * @LastEditTime : 2025-01-15 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|权限验证中间件
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 权限验证中间件类
 * 统一管理所有权限验证逻辑
 */
class MrheAuthMiddleware
{
    /**
     * 验证管理员权限
     * @param bool $send_json 是否发送JSON响应(默认true)
     * @return bool 是否有权限
     */
    public static function verifyAdminPermission($send_json = true)
    {
        if (!current_user_can(MrheAuthConstants::ADMIN_CAPABILITY)) {
            if ($send_json) {
                MrheResponseHandler::error('权限不足，需要管理员权限', 'permission_denied');
            }
            return false;
        }
        return true;
    }

    /**
     * 验证Nonce
     * @param string $action Nonce动作名称
     * @param string $nonce_field Nonce字段名(默认'nonce')
     * @param bool $send_json 是否发送JSON响应(默认true)
     * @return bool 是否验证通过
     */
    public static function verifyNonce($action = null, $nonce_field = 'nonce', $send_json = true)
    {
        // 默认使用管理员Nonce
        if ($action === null) {
            $action = MrheAuthConstants::ADMIN_NONCE;
        }

        // 从POST或GET获取nonce
        $nonce = isset($_POST[$nonce_field]) ? $_POST[$nonce_field] : (isset($_GET[$nonce_field]) ? $_GET[$nonce_field] : '');

        if (!wp_verify_nonce($nonce, $action)) {
            if ($send_json) {
                MrheResponseHandler::error('安全验证失败，请刷新页面重试', 'nonce_verification_failed');
            }
            return false;
        }
        return true;
    }

    /**
     * 验证用户Nonce
     * @param string $nonce_field Nonce字段名(默认'_wpnonce')
     * @param bool $send_json 是否发送JSON响应(默认true)
     * @return bool 是否验证通过
     */
    public static function verifyUserNonce($nonce_field = '_wpnonce', $send_json = true)
    {
        return self::verifyNonce(MrheAuthConstants::USER_NONCE, $nonce_field, $send_json);
    }

    /**
     * 获取当前用户ID
     * @param bool $require_login 是否要求登录(默认true)
     * @param bool $send_json 是否发送JSON响应(默认true)
     * @return int|false 用户ID或false
     */
    public static function getCurrentUserId($require_login = true, $send_json = true)
    {
        $user_id = get_current_user_id();

        if ($require_login && !$user_id) {
            if ($send_json) {
                MrheResponseHandler::error('请先登录', 'not_logged_in');
            }
            return false;
        }

        return $user_id;
    }

    /**
     * 验证订单所有权
     * @param string $order_num 订单号
     * @param int $user_id 用户ID(可选,默认当前用户)
     * @param bool $send_json 是否发送JSON响应(默认true)
     * @return array|false 订单信息或false
     */
    public static function verifyOrderOwnership($order_num, $user_id = null, $send_json = true)
    {
        // 获取用户ID
        if ($user_id === null) {
            $user_id = self::getCurrentUserId(true, $send_json);
            if (!$user_id) {
                return false;
            }
        }

        // 验证订单号
        if (empty($order_num)) {
            if ($send_json) {
                MrheResponseHandler::error('订单号不能为空', 'invalid_order_num');
            }
            return false;
        }

        // 查询订单
        global $wpdb;
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->zibpay_order}
             WHERE order_num = %s AND user_id = %d AND status = 1",
            $order_num,
            $user_id
        ), ARRAY_A);

        // 检查数据库错误
        if ($wpdb->last_error) {
            error_log('MRHE订单查询错误: ' . $wpdb->last_error);
            if ($send_json) {
                MrheResponseHandler::error('数据库查询失败', 'database_error');
            }
            return false;
        }

        // 验证订单所有权
        if (!$order || $order['user_id'] != $user_id) {
            if ($send_json) {
                MrheResponseHandler::error('无权访问此订单', 'order_access_denied');
            }
            return false;
        }

        return $order;
    }

    /**
     * 验证授权记录所有权
     * @param int $auth_id 授权记录ID
     * @param int $user_id 用户ID(可选,默认当前用户)
     * @param bool $send_json 是否发送JSON响应(默认true)
     * @return array|false 授权记录或false
     */
    public static function verifyAuthOwnership($auth_id, $user_id = null, $send_json = true)
    {
        // 获取用户ID
        if ($user_id === null) {
            $user_id = self::getCurrentUserId(true, $send_json);
            if (!$user_id) {
                return false;
            }
        }

        // 查询授权记录
        global $wpdb;
        $table_name = MrheAuthConstants::getTableName();
        $auth_record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $auth_id
        ), ARRAY_A);

        // 检查数据库错误
        if ($wpdb->last_error) {
            error_log('MRHE授权查询错误: ' . $wpdb->last_error);
            if ($send_json) {
                MrheResponseHandler::error('数据库查询失败', 'database_error');
            }
            return false;
        }

        // 验证所有权
        if (!$auth_record || $auth_record['user_id'] != $user_id) {
            if ($send_json) {
                MrheResponseHandler::error('无权访问此授权记录', 'auth_access_denied');
            }
            return false;
        }

        return $auth_record;
    }

    /**
     * 组合验证: 管理员权限 + Nonce
     * @param string $action Nonce动作名称
     * @return bool 是否验证通过
     */
    public static function verifyAdminRequest($action = null)
    {
        return self::verifyAdminPermission() && self::verifyNonce($action);
    }

    /**
     * 组合验证: 用户登录 + Nonce
     * @param string $nonce_field Nonce字段名
     * @return int|false 用户ID或false
     */
    public static function verifyUserRequest($nonce_field = '_wpnonce')
    {
        $user_id = self::getCurrentUserId();
        if (!$user_id) {
            return false;
        }

        if (!self::verifyUserNonce($nonce_field)) {
            return false;
        }

        return $user_id;
    }
}
