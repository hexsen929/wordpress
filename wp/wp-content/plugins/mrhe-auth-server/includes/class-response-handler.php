<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2025-01-15 10:00:00
 * @LastEditTime : 2025-01-15 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|响应处理器类
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 响应处理器类
 * 统一管理所有JSON响应
 */
class MrheResponseHandler
{
    /**
     * 发送成功响应
     * @param mixed $data 响应数据
     * @param string $message 成功消息
     */
    public static function success($data = null, $message = '操作成功')
    {
        wp_send_json_success(array(
            'code' => MrheAuthConstants::RESPONSE_SUCCESS,
            'message' => $message,
            'data' => $data
        ));
    }

    /**
     * 发送错误响应
     * @param string $message 错误消息
     * @param string $code 错误代码
     * @param mixed $data 附加数据
     */
    public static function error($message = '操作失败', $code = 'error', $data = null)
    {
        wp_send_json_error(array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        ));
    }

    /**
     * 发送管理后台JSON响应(兼容旧格式)
     * @param int $code 响应代码(0=成功, 1=失败)
     * @param mixed $data 响应数据
     * @param string $message 响应消息
     */
    public static function adminJson($code, $data = null, $message = '')
    {
        wp_send_json(array(
            'code' => $code,
            'data' => $data,
            'msg' => $message
        ));
    }

    /**
     * 发送管理后台成功响应
     * @param mixed $data 响应数据
     * @param string $message 成功消息
     */
    public static function adminSuccess($data = null, $message = '操作成功')
    {
        self::adminJson(MrheAuthConstants::RESPONSE_SUCCESS, $data, $message);
    }

    /**
     * 发送管理后台错误响应
     * @param string $message 错误消息
     * @param mixed $data 附加数据
     */
    public static function adminError($message = '操作失败', $data = null)
    {
        self::adminJson(MrheAuthConstants::RESPONSE_ERROR, $data, $message);
    }
}
