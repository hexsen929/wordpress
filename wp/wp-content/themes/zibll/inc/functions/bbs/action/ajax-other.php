<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-11-09 13:59:52
 * @LastEditTime : 2025-07-25 15:55:57
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|AJAX执行类函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//edit选择板块的列表
function zib_bbs_ajax_plate_select_lists()
{
    zib_ajax_send_ajaxpager(zib_bbs_edit::plate_select_lists());
}
add_action('wp_ajax_plate_select_lists', 'zib_bbs_ajax_plate_select_lists');
add_action('wp_ajax_nopriv_plate_select_lists', 'zib_bbs_ajax_plate_select_lists');

//edit选择话题的列表
function zib_bbs_ajax_topic_select_lists()
{

    zib_ajax_send_ajaxpager(zib_bbs_edit::topic_select_lists());
}
add_action('wp_ajax_topic_select_lists', 'zib_bbs_ajax_topic_select_lists');
add_action('wp_ajax_nopriv_topic_select_lists', 'zib_bbs_ajax_topic_select_lists');

//edit选择标签的列表
function zib_bbs_ajax_tag_select_lists()
{
    zib_ajax_send_ajaxpager(zib_bbs_edit::tag_select_lists());
}
add_action('wp_ajax_tag_select_lists', 'zib_bbs_ajax_tag_select_lists');
add_action('wp_ajax_nopriv_tag_select_lists', 'zib_bbs_ajax_tag_select_lists');

//加分
function zib_bbs_bbs_user_score_extra_max($max)
{
    return _pz('bbs_score_extra_max') ?: 5;
}
add_filter('bbs_user_score_extra_max', 'zib_bbs_bbs_user_score_extra_max');

//减分
function zib_bbs_bbs_user_score_deduct_max($max)
{
    return _pz('bbs_score_deduct_max') ?: 3;
}
add_filter('bbs_user_score_deduct_max', 'zib_bbs_bbs_user_score_deduct_max');
