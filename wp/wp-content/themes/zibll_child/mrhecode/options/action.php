<?php
/*
 * @Author        : Qinver
 * @Url           : mrhe.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime: 2023-05-16 09:58:05
 * @Email         : 770349780@qq.com
 * @Project       : mrhe子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//自定义css、js,这里注销原zibll设置css，替换为子主题修改后的css
function csf_add_custom_wp_enqueue_mrhe()
{
	wp_deregister_style('csf_custom_css');
    // Style
    wp_enqueue_style('csf_custom_css', get_stylesheet_directory_uri() . '/inc/csf-framework/assets/css/style.min.css', array(), MRHE_THEME_VERSION);
    // Script
    //wp_enqueue_script('csf_custom_js', get_template_directory_uri() . '/inc/csf-framework/assets/js/main.min.js', array('jquery'), THEME_VERSION);
}
//add_action('csf_enqueue', 'csf_add_custom_wp_enqueue_mrhe');

//备份主题数据
function mrhe_options_backup($type = '自动备份')
{
    $prefix  = 'mrhe_options';
    $options = get_option($prefix);

    $options_backup = get_option($prefix . '_backup');
    if (!$options_backup) {
        $options_backup = array();
    }

    $time                  = current_time('Y-m-d H:i:s');
    $options_backup[$time] = array(
        'time' => $time,
        'type' => $type,
        'data' => $options,
    );
    return update_option($prefix . '_backup', $options_backup);
}

function mrhe_csf_reset_to_backup()
{
    mrhe_options_backup('重置全部 自动备份');
}
add_action('csf_mrhe_options_reset_before', 'mrhe_csf_reset_to_backup');

function mrhe_csf_reset_section_to_backup()
{
    mrhe_options_backup('重置选区 自动备份');
}
add_action('csf_mrhe_options_reset_section_before', 'mrhe_csf_reset_section_to_backup');

//主题更新自动备份
function mrhe_new_mrhe_to_backup()
{
    $prefix         = 'mrhe_options';
    $options_backup = get_option($prefix . '_backup');
    $time           = false;

    if ($options_backup) {
        $options_backup = array_reverse($options_backup);
        foreach ($options_backup as $key => $val) {
            if ('更新主题 自动备份' == $val['type']) {
                $time = $key;
                break;
            }
        }
    }
    if (!$time || (floor((strtotime(current_time("Y-m-d H:i:s")) - strtotime($time)) / 3600) > 240)) {
        mrhe_options_backup('更新主题 自动备份');
        //更新主题刷新所有缓存
        wp_cache_flush();
    }
}
add_action('mrhe_update_notices', 'mrhe_new_mrhe_to_backup');

function mrhe_csf_save_section_to_backup()
{
    $prefix         = 'mrhe_options';
    $options_backup = get_option($prefix . '_backup');
    $time           = false;

    if ($options_backup) {
        $options_backup = array_reverse($options_backup);
        foreach ($options_backup as $key => $val) {
            if ('定期自动备份' == $val['type']) {
                $time = $key;
                break;
            }
        }
    }
    if (!$time || (floor((strtotime(current_time("Y-m-d H:i:s")) - strtotime($time)) / 3600) > 600)) {
        mrhe_options_backup('定期自动备份');
    }
}
add_action('csf_mrhe_options_saved', 'mrhe_csf_save_section_to_backup');
 
//导入主题设置
function mrhe_ajax_options_import()
{
    if (!is_super_admin()) {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '操作权限不足')));
        exit();
    }

    $data = !empty($_REQUEST['import_data']) ? $_REQUEST['import_data'] : '';

    if (!$data) {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请粘贴需导入配置的json代码')));
        exit();
    }

    $import_data = json_decode(wp_unslash(trim($data)), true);

    if (empty($import_data) || !is_array($import_data)) {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => 'json代码格式错误，无法导入')));
        exit();
    }

    mrhe_options_backup('导入配置 自动备份');

    $prefix = 'mrhe_options';
    update_option($prefix, $import_data);
    echo (json_encode(array('error' => 0, 'reload' => 1, 'msg' => '主题设置已导入，请刷新页面')));
    exit();
}
add_action('wp_ajax_mrhe_options_import', 'mrhe_ajax_options_import');

//备份主题设置
function mrhe_ajax_options_backup()
{
    $type   = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '手动备份';
    $backup = mrhe_options_backup($type);
    echo (json_encode(array('error' => 0, 'reload' => 1, 'msg' => '当前配置已经备份')));
    exit();
}
add_action('wp_ajax_mrhe_options_backup', 'mrhe_ajax_options_backup');

function mrhe_ajax_options_backup_delete()
{
    if (!is_super_admin()) {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '操作权限不足')));
        exit();
    }
    if (empty($_REQUEST['key'])) {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '参数传入错误')));
        exit();
    }

    $prefix = 'mrhe_options';
    if ('mrhe_options_backup_delete_all' == $_REQUEST['action']) {
        update_option($prefix . '_backup', false);
        echo (json_encode(array('error' => 0, 'reload' => 1, 'msg' => '已删除全部备份数据')));
        exit();
    }

    $options_backup = get_option($prefix . '_backup');

    if ('mrhe_options_backup_delete_surplus' == $_REQUEST['action']) {
        if ($options_backup) {
            $options_backup = array_reverse($options_backup);
            update_option($prefix . '_backup', array_reverse(array_slice($options_backup, 0, 3)));
            echo (json_encode(array('error' => 0, 'reload' => 1, 'msg' => '已删除多余备份数据，仅保留最新3份')));
            exit();
        }
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '暂无可删除的数据')));
    }

    if (isset($options_backup[$_REQUEST['key']])) {
        unset($options_backup[$_REQUEST['key']]);

        update_option($prefix . '_backup', $options_backup);
        echo (json_encode(array('error' => 0, 'reload' => 1, 'msg' => '所选备份已删除')));
    } else {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '此备份已删除')));
    }
    exit();
}
add_action('wp_ajax_mrhe_options_backup_delete', 'mrhe_ajax_options_backup_delete');
add_action('wp_ajax_mrhe_options_backup_delete_all', 'mrhe_ajax_options_backup_delete');
add_action('wp_ajax_mrhe_options_backup_delete_surplus', 'mrhe_ajax_options_backup_delete');

function mrhe_ajax_options_backup_restore()
{
    if (!is_super_admin()) {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '操作权限不足')));
        exit();
    }
    if (empty($_REQUEST['key'])) {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '参数传入错误')));
        exit();
    }

    $prefix         = 'mrhe_options';
    $options_backup = get_option($prefix . '_backup');
    if (isset($options_backup[$_REQUEST['key']]['data'])) {
        update_option($prefix, $options_backup[$_REQUEST['key']]['data']);
        echo (json_encode(array('error' => 0, 'reload' => 1, 'msg' => '主题设置已恢复到所选备份[' . $_REQUEST['key'] . ']')));
    } else {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '备份恢复失败，未找到对应数据')));
    }
    exit();
}
add_action('wp_ajax_mrhe_options_backup_restore', 'mrhe_ajax_options_backup_restore');