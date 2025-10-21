<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:50
 * @LastEditTime : 2025-07-29 12:54:03
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

global $wpdb;
$wpdb->zibpay_card_password = $wpdb->prefix . 'zibpay_card_password';

/**
 * @description: 支付的订单系统
 * @param {*}
 * @return {*}
 */
class ZibCardPass
{
    public static $table_name = 'zibpay_card_password';

    public static function create_db()
    {
        global $wpdb;
        /**判断没有则创建 */
        if ($wpdb->get_var("show tables like '{$wpdb->zibpay_card_password}'") != $wpdb->zibpay_card_password) {
            $wpdb->query("CREATE TABLE $wpdb->zibpay_card_password (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `card` varchar(50) DEFAULT NULL COMMENT '卡号',
                `password` varchar(50) DEFAULT NULL COMMENT '密码',
                `type` varchar(50) DEFAULT NULL COMMENT '类型',
                `post_id` int(11) DEFAULT NULL COMMENT '商品id',
                `order_num` varchar(50) DEFAULT NULL COMMENT '订单号',
                `create_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
                `modified_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
                `status` varchar(50) DEFAULT NULL COMMENT '状态',
                `meta` longtext DEFAULT NULL COMMENT '元数据',
                `other` longtext DEFAULT NULL COMMENT '其它',
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . " COMMENT='卡密';");

            $wpdb->query("ALTER TABLE $wpdb->zibpay_card_password ADD INDEX(`card`)"); //添加索引
            $wpdb->query("ALTER TABLE $wpdb->zibpay_card_password ADD INDEX(`password`)"); //添加索引
            $wpdb->query("ALTER TABLE $wpdb->zibpay_card_password ADD INDEX(`type`)"); //添加索引
            $wpdb->query("ALTER TABLE $wpdb->zibpay_card_password ADD INDEX(`status`)"); //添加索引
            $wpdb->query("ALTER TABLE $wpdb->zibpay_card_password ADD INDEX(`create_time`,`modified_time`,`id`)"); //添加索引
            $wpdb->query("ALTER TABLE $wpdb->zibpay_card_password ADD INDEX(`post_id`,`order_num`,`id`)"); //添加索引
        } elseif (version_compare(THEME_VERSION, '7.0.5', '<=') && !$wpdb->get_row("show index from {$wpdb->zibpay_card_password} WHERE Key_name = 'card'")) {
            $wpdb->query("ALTER TABLE $wpdb->zibpay_card_password ADD INDEX(`card`)"); //添加索引
            $wpdb->query("ALTER TABLE $wpdb->zibpay_card_password ADD INDEX(`password`)"); //添加索引
            $wpdb->query("ALTER TABLE $wpdb->zibpay_card_password ADD INDEX(`type`)"); //添加索引
            $wpdb->query("ALTER TABLE $wpdb->zibpay_card_password ADD INDEX(`status`)"); //添加索引
            $wpdb->query("ALTER TABLE $wpdb->zibpay_card_password ADD INDEX(`create_time`,`modified_time`,`id`)"); //添加索引
            $wpdb->query("ALTER TABLE $wpdb->zibpay_card_password ADD INDEX(`post_id`,`order_num`,`id`)"); //添加索引
        }
    }

    //新增
    public static function add($values)
    {
        return self::update($values);
    }

    //更新数据库
    public static function update($values)
    {
        $defaults = array(
            'card'          => '',
            'password'      => '',
            'type'          => '',
            'post_id'       => '',
            'order_num'     => '',
            'create_time'   => current_time('mysql'),
            'modified_time' => current_time('mysql'),
            'status'        => '',
            'meta'          => '',
            'other'         => '',
        );
        $values = wp_parse_args($values, $defaults);

        //准备数据
        //根据需要压缩数据
        $values['meta']  = $values['meta'] ? maybe_serialize($values['meta']) : '';
        $values['other'] = $values['other'] ? maybe_serialize($values['other']) : '';

        $db = ZibDB::name(self::$table_name);
        //判断更新还是新增
        if (!empty($values['id'])) {
            //更新数据库
            unset($values['create_time']); //清除创建时间
            $values = array_filter($values); //清除为空的数组键。
            $where  = array('id' => $values['id']);
            //执行更新
            if (false !== $db->where($where)->update($values)) {
                //挂钩添加
                return self::get_row(array('id' => $values['id']));
            }
            return false;
        }
        //如果不是更新，则新增数据库
        if (false !== $db->insert($values)) {
            //挂钩添加
            return $values;
        }

        return false;
    }

    /**
     * @description: 根据Array数据获取1条消息
     * @param array $where 例如：array('id' => '10');
     * @return {*}
     */
    public static function get_row($where)
    {

        $db = self::get($where, 'id', 0, 1);

        if (isset($db[0])) {
            $db        = $db[0];
            $db->meta  = maybe_unserialize($db->meta);
            $db->other = maybe_unserialize($db->other);
        }
        return $db;
    }

    /**
     * @description: 获取消息的Meta值
     * @param int $id 消息ID
     * @param mixed $key Meta键名
     * @param mixed $defaults 默认值
     * @return {*}
     */
    public static function get_meta($id, $key, $defaults = false)
    {
        $msg_db = self::get_row(array('id' => $id));
        if ($msg_db) {
            $metas = (array) $msg_db->meta;
            if (isset($metas[$key])) {
                return $metas[$key];
            }
        }
        return $defaults;
    }

    /**
     * @description: 设置消息的Meta值
     * @param int $id 消息ID
     * @param mixed $key Meta键名
     * @param mixed $values Meta键值
     * @return {*}
     */
    public static function set_meta($id, $key, $values)
    {
        $msg_db = self::get_row(array('id' => $id));
        $metas  = array();
        if (is_array($msg_db->meta)) {
            $metas = $msg_db->meta;
        }
        $metas[$key] = $values;
        $metas       = maybe_serialize($metas);

        return ZibDB::name(self::$table_name)->where((int) $id)->update(array('meta' => $metas));
    }

    /**
     * @description: 根据Array数据获取计数
     * @param array $where 例如：array('id' => '10');
     * @return int $count
     */
    public static function get_count($where)
    {

        global $wpdb;

        $format_data = self::format_data($where);
        $conditions  = $format_data['conditions'];
        $values      = $format_data['values'];

        $sql = "SELECT COUNT(*) FROM {$wpdb->zibpay_card_password} WHERE $conditions";

        if ($values) {
            $count = $wpdb->get_var($wpdb->prepare($sql, $values));
        } else {
            $count = $wpdb->get_var($sql, $values);
        }
        return $count;
    }

    /**
     * @description: 根据数据获取消息
     * @param array $where 例如：array('id' => '10');
     * @param mixed $orderby 排序依据
     * @param int $offset 跳过前几个
     * @param int||mixed  $ice_perpage 加载数量| 'all' 代表加载全部
     * @param mixed $decs 'DESC'降序 | 'ASC'降序
     * @return {*}
     */
    public static function get($where, $orderby = 'id', $offset = 0, $ice_perpage = 10, $decs = 'DESC')
    {

        global $wpdb;
        $format_data = self::format_data($where);
        $conditions  = $format_data['conditions'];
        $values      = $format_data['values'];
        $decs        = 'DESC' == $decs ? 'DESC' : '';
        $limit       = '';
        if ('all' != $ice_perpage) {
            $limit = 'limit ' . $offset . ',' . $ice_perpage;
        }
        $sql = "SELECT * FROM {$wpdb->zibpay_card_password} WHERE $conditions order by $orderby $decs $limit";

        if ($values) {
            $msg_db = $wpdb->get_results($wpdb->prepare($sql, $values));
        } else {
            $msg_db = $wpdb->get_results($sql);
        }
        return $msg_db;
    }

    /**
     * @description: 根据数据查找删除消息
     * @param array $where 例如：array('id' => '10');
     * @return {*}
     */
    public static function delete($where)
    {
        global $wpdb;
        $format_data = self::format_data($where);
        $conditions  = $format_data['conditions'];
        $values      = $format_data['values'];

        $sql = "DELETE FROM {$wpdb->zibpay_card_password} WHERE $conditions";
        if ($values) {
            $query = $wpdb->query($wpdb->prepare($sql, $values));
        } else {
            $query = $wpdb->query($sql);
        }

        return $query;
    }

    /**
     * @description: 格式化Array为SQL语言
     * @param array $where 例如：array('id' => '10');
     * @return int $count
     */
    public static function format_data($where)
    {
        $conditions = array();
        $values     = array();

        if (!is_array($where)) {
            return array(
                'conditions' => $where,
                'values'     => '',
            );
        }

        $format_int = array('id', 'post_id'); //格式为数字的表名称

        foreach ($where as $field => $value) {
            if (is_null($value)) {
                $conditions[] = "`$field` IS NULL";
                continue;
            }
            $format = in_array($field, $format_int) ? '%d' : '%s';

            //数组判断-》转为SQL IN语句
            if (is_array($value)) {
                $arrar_field = array();
                foreach ($value as $arrar_f) {
                    $arrar_field[] = $format;
                    $values[]      = $arrar_f;
                }
                $arrar_field  = implode(',', $arrar_field);
                $conditions[] = "`$field` IN ($arrar_field)";
            } elseif (stristr($value, '|')) {
                $arrar_field  = explode('|', $value);
                $conditions[] = "`$field` $arrar_field[0] $format";
                $values[]     = $arrar_field[1];
            } else {
                $conditions[] = "`$field` = $format";
                $values[]     = $value;
            }
        }

        $conditions = implode(' AND ', $conditions);

        return array(
            'conditions' => $conditions,
            'values'     => $values,
        );
    }

    //随机号卡
    public static function rand_number($limit = 20)
    {
        $chars = mt_rand(100, 999) . mt_rand(100, 999) . mt_rand(100, 999);

        if ($limit > 27) {
            $chars = $chars . $chars;
        }

        $chars = str_shuffle($chars . $chars . $chars);
        $str   = substr($chars, 0, $limit);

        return $str;
    }

    /**
     * @description: 根据Array数据获取1条消息
     * @param array $where 例如：array('id' => '10');
     * @return {*}
     */
    public static function rand_password($limit = 35)
    {

        $str   = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        if ($limit > 60) {
            $chars = $chars . $chars;
        }
        $chars = str_shuffle($chars);
        $str   = substr($chars, 0, $limit);

        return $str;
    }

}
