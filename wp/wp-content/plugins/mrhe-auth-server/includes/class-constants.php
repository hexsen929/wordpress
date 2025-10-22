<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2025-01-15 10:00:00
 * @LastEditTime : 2025-01-15 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|常量定义
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 授权系统常量定义类
 * 统一管理所有硬编码的常量值
 */
class MrheAuthConstants
{
    /**
     * 数据库表名
     */
    const AUTH_TABLE = 'mrhe_theme_aut';

    /**
     * 默认最大授权域名数
     */
    const DEFAULT_MAX_DOMAINS = 3;

    /**
     * 管理员权限能力
     */
    const ADMIN_CAPABILITY = 'manage_options';

    /**
     * Nonce名称
     */
    const ADMIN_NONCE = 'mrhe_admin_auth_nonce';
    const USER_NONCE = 'mrhe_user_auth_nonce';

    /**
     * 缓存组名
     */
    const CACHE_GROUP = 'mrhe_auth';

    /**
     * 缓存过期时间(秒)
     */
    const CACHE_EXPIRATION = 300; // 5分钟

    /**
     * 授权状态
     */
    const STATUS_AUTHORIZED = 1;
    const STATUS_UNAUTHORIZED = 0;
    const STATUS_BANNED = -1;

    /**
     * 响应代码
     */
    const RESPONSE_SUCCESS = 0;
    const RESPONSE_ERROR = 1;

    /**
     * 获取完整的数据库表名
     * @return string 完整表名
     */
    public static function getTableName()
    {
        global $wpdb;
        return $wpdb->prefix . self::AUTH_TABLE;
    }

    /**
     * 获取缓存键
     * @param string $key 缓存键名
     * @return string 完整缓存键
     */
    public static function getCacheKey($key)
    {
        return 'mrhe_auth_' . $key;
    }
}
