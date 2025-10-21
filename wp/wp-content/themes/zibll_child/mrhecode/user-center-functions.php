<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-10-09 22:46:56
 * @LastEditTime : 2025-07-25 15:20:20
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 用户中心相关功能 - 投诉、授权管理、社交账号
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 投诉功能相关
 */

// 添加投诉标签页
function zibll_child_add_complaint_tab($tabs_array)
{
    if (!current_user_can('manage_options')) {
        $tabs_array['complaint'] = array(
            'title'         => '我的投诉',
            'nav_attr'      => 'drawer-title="我的投诉进度"',
            'content_class' => 'author-user-con',
            'loader'   => '<div class="zib-widget"><div class="box-body notop nopw-sm"><div class="border-bottom box-body"><div style="width: 150px;" class="placeholder t1 mb10"></div><div class="placeholder t1"></div></div><div class="border-bottom box-body"><div style="width: 150px;" class="placeholder t1 mb10"></div><div class="placeholder t1"></div></div><div class="border-bottom box-body"><div style="width: 150px;" class="placeholder t1 mb10"></div><div class="placeholder t1"></div></div><div class="box-body nobottom"><div style="width: 150px;" class="placeholder t1"></div></div></div></div>',
        );
    }
    return $tabs_array;
}
add_filter('user_ctnter_main_tabs_array', 'zibll_child_add_complaint_tab');

// 投诉内容处理
function zibll_child_complaint_content()
{
    global $wpdb;

    $user = wp_get_current_user();
    $user_id = isset($user->ID) ? (int) $user->ID : 0;
    if (!$user_id) {
        return;
    }

    // 使用 $wpdb->prepare() 处理 SQL 查询，防止 SQL 注入
    $query = $wpdb->prepare(
        "SELECT status, meta
        FROM {$wpdb->zib_message}
        WHERE send_user = %d",
        $user_id
    );

    // 执行查询
    $results = $wpdb->get_results($query);

    // 内联 CSS 样式
    $style = '
        <style>
            .progress {
                height: 20px;
                position: static;
                background-color: #f2f2f2;
                border-radius: 8px;
                box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }
            .progress-bar {
                height: 100%;
                box-shadow: inset 0 -1px 2px rgba(0, 0, 0, 0.15);
                transition: width 0.6s ease;
                border-radius: 6px;
            }
        </style>
    ';

    // 处理结果
    $form = '';
    $form .= '<div class="alert jb-blue"><b>加入网络监督员维护社区网络环境，举报不良信息，共建和谐绿色社区</b></div>';
    if (!empty($results)) {
        foreach ($results as $row) {
            // 对于每一行结果，$row 是一个对象，可以根据 status 字段的值进行判断
            $status = $row->status;
            // 解析 meta 字段
            $meta_value = $row->meta;
            $meta_array = unserialize($meta_value);
            $user_data = get_userdata($meta_array['report_user_id']); //获取被举报者名字

            if ($status == 1) {
                // "已完成" 显示为进度条的 100% 完成
                $progress = '<p>处理结果：' . $meta_array['process_desc'] . '</p>
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">已完成</div>
                            </div>';
            } else {
                // "处理中" 显示为进度条的 50% 完成（可根据实际进度进行调整）
                $progress = '<div class="progress">
                                <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 50%;">处理中</div>
                            </div>';
            }

            // 在这里处理 meta 值
            $form .= '<div class="mb40">';
            $form .= '<p>被举报用户：' . $user_data->display_name . '</p>';
            $form .= '<p>举报原因：' . $meta_array['desc'] . '</p>';
            $form .= '<p>举报详情：' . $meta_array['reason'] . '</p>';
            $form .= '<p>违规链接：' . $meta_array['url'] . '</p>';
            $form .= '<p>提交时间：' . $meta_array['time'] . '</p>';
            $form .= $progress;
            $form .= '</div>';
        }
    } else {
        $form .= '<div class="mb40">';
        $form .= '<p>没有你的举报记录</p>';
        $form .= '</div>';
    }

    $html = '<form class="my-complaint-form hexsen-complaint zib-widget"><div class="padding-h10" style="max-width: 502px;margin: auto;">' . $style . $form . '</div></form>';
    return zib_get_ajax_ajaxpager_one_centent($html);
}
add_filter('main_user_tab_content_complaint', 'zibll_child_complaint_content');




/**
 * 授权管理功能相关
 */

// 注意：服务端JS文件已移至插件 mrhe-auth-server

// 注意：服务端功能函数已移至插件 mrhe-auth-server