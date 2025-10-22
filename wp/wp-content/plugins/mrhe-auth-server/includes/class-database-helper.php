<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2025-01-15 10:00:00
 * @LastEditTime : 2025-01-15 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|数据库辅助类
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 数据库辅助类
 * 统一管理所有数据库操作
 */
class MrheAuthDatabase
{
    /**
     * 获取授权记录
     * @param int $user_id 用户ID
     * @param int $post_id 产品ID
     * @return array|null 授权记录或null
     */
    public static function getAuthRecord($user_id, $post_id)
    {
        global $wpdb;
        $table_name = MrheAuthConstants::getTableName();

        $record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND post_id = %d",
            $user_id,
            $post_id
        ), ARRAY_A);

        // 检查数据库错误
        if ($wpdb->last_error) {
            error_log('MRHE数据库查询错误: ' . $wpdb->last_error);
            return null;
        }

        return $record;
    }

    /**
     * 获取用户的所有授权记录
     * @param int $user_id 用户ID
     * @param bool $only_authorized 是否只返回已授权的记录
     * @return array 授权记录数组
     */
    public static function getUserAuthRecords($user_id, $only_authorized = false)
    {
        global $wpdb;
        $table_name = MrheAuthConstants::getTableName();

        $where = "WHERE user_id = %d";
        if ($only_authorized) {
            $where .= " AND is_authorized = " . MrheAuthConstants::STATUS_AUTHORIZED;
        }

        $records = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name $where ORDER BY created_at DESC",
            $user_id
        ), ARRAY_A);

        // 检查数据库错误
        if ($wpdb->last_error) {
            error_log('MRHE数据库查询错误: ' . $wpdb->last_error);
            return array();
        }

        return $records ? $records : array();
    }

    /**
     * 通过授权码获取授权记录
     * @param string $auth_code 授权码
     * @return array|null 授权记录或null
     */
    public static function getAuthRecordByCode($auth_code)
    {
        global $wpdb;
        $table_name = MrheAuthConstants::getTableName();

        $record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE auth_code = %s",
            $auth_code
        ), ARRAY_A);

        // 检查数据库错误
        if ($wpdb->last_error) {
            error_log('MRHE数据库查询错误: ' . $wpdb->last_error);
            return null;
        }

        return $record;
    }

    /**
     * 更新授权记录
     * @param int $id 记录ID
     * @param array $data 要更新的数据
     * @return bool 是否成功
     */
    public static function updateAuthRecord($id, $data)
    {
        global $wpdb;
        $table_name = MrheAuthConstants::getTableName();

        // 添加更新时间
        $data['updated_at'] = current_time('mysql');

        $result = $wpdb->update(
            $table_name,
            $data,
            array('id' => $id),
            null,
            array('%d')
        );

        // 检查数据库错误
        if ($wpdb->last_error) {
            error_log('MRHE数据库更新错误: ' . $wpdb->last_error);
            return false;
        }

        return $result !== false;
    }

    /**
     * 插入新的授权记录
     * @param array $data 授权数据
     * @return int|false 插入的ID或false
     */
    public static function insertAuthRecord($data)
    {
        global $wpdb;
        $table_name = MrheAuthConstants::getTableName();

        // 设置默认值
        $defaults = array(
            'is_authorized' => MrheAuthConstants::STATUS_AUTHORIZED,
            'is_banned' => 0,
            'aut_max_url' => MrheAuthConstants::DEFAULT_MAX_DOMAINS,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        );

        $data = wp_parse_args($data, $defaults);

        $result = $wpdb->insert($table_name, $data);

        // 检查数据库错误
        if ($wpdb->last_error) {
            error_log('MRHE数据库插入错误: ' . $wpdb->last_error);
            return false;
        }

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * 删除授权记录
     * @param int $id 记录ID
     * @return bool 是否成功
     */
    public static function deleteAuthRecord($id)
    {
        global $wpdb;
        $table_name = MrheAuthConstants::getTableName();

        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );

        // 检查数据库错误
        if ($wpdb->last_error) {
            error_log('MRHE数据库删除错误: ' . $wpdb->last_error);
            return false;
        }

        return $result !== false;
    }

    /**
     * 检查授权记录是否存在
     * @param int $user_id 用户ID
     * @param int $post_id 产品ID
     * @return bool 是否存在
     */
    public static function authRecordExists($user_id, $post_id)
    {
        $record = self::getAuthRecord($user_id, $post_id);
        return !empty($record);
    }
}
