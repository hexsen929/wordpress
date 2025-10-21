<?php
/**
 * 前端附件管理 - 用户中心和作者相册功能
 */

if (!defined('ABSPATH')) {
    exit;
}

// 挂钩用户中心侧边栏按钮
function mrhe_attachment_user_center_button($buttons) {
    // 检查功能是否启用
    if (!_mrhe('attachment_manager_s')) {
        return $buttons;
    }
    
    $buttons[] = array(
        'html' => '',
        'icon' => zib_get_svg('poster-color'),
        'name' => '附件管理',
        'tab'  => 'imgmanage',
    );

    return $buttons;
}
add_filter('zib_user_center_page_sidebar_button_2_args', 'mrhe_attachment_user_center_button', 20, 1);

// 注册用户中心页面 Tab
function mrhe_attachment_user_center_tab($tabs_array) {
    // 检查功能是否启用
    if (!_mrhe('attachment_manager_s')) {
        return $tabs_array;
    }
    
    $tabs_array['imgmanage'] = array(
        'title'    => '附件管理',
        'nav_attr' => 'drawer-title="附件管理"',
        'class'    => 'user-pay-statistical mb20',
        'loader'   => str_repeat('<div class="posts-item card "><div class="item-thumbnail"><div class="radius8 item-thumbnail placeholder"></div> </div></div>', 16),
        'query'    => array('action' => 'mrhe_attachment_ajax'),
    );
    return $tabs_array;
}
add_filter('user_ctnter_main_tabs_array', 'mrhe_attachment_user_center_tab');

// —— 作者页：添加"个人相册"Tab ——
function mrhe_attachment_author_album_tab($tabs_array, $author_id) {
    // 检查总开关
    if (!_mrhe('attachment_manager_s')) {
        return $tabs_array;
    }
    
    // 检查作者相册开关
    if (!_mrhe('author_album_enable', true)) {
        return $tabs_array;
    }

    // 为作者主内容添加一个"相册"标签，并展示数量
    global $wpdb;
    $is_super_admin = is_super_admin();
    $display_type   = _mrhe('author_album_type', 'image_video');

    if ($is_super_admin) {
        // 管理员：显示全站所有附件数量
        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='attachment' AND post_status='inherit'");
    } else {
        // 非管理员：按设置的显示类别统计该作者的附件数量
        if ($display_type === 'image') {
            $count = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='attachment' AND post_status='inherit' AND post_mime_type LIKE %s AND post_author=%d",
                    'image/%',
                    (int) $author_id
                )
            );
        } elseif ($display_type === 'video') {
            $count = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='attachment' AND post_status='inherit' AND post_mime_type LIKE %s AND post_author=%d",
                    'video/%',
                    (int) $author_id
                )
            );
        } else { // image_video
            $count = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='attachment' AND post_status='inherit' AND post_author=%d AND (post_mime_type LIKE %s OR post_mime_type LIKE %s)",
                    (int) $author_id,
                    'image/%',
                    'video/%'
                )
            );
        }
    }

    $tabs_array['album'] = array(
        'title'         => '个人相册<count class="opacity8 ml3">' . $count . '</count>',
        'content_class' => '',
        'route'         => true,
        'loader'        => str_repeat('<div class="posts-item card "><div class="item-thumbnail"><div class="radius8 item-thumbnail placeholder"></div> </div></div>', 16),
    );
    return $tabs_array;
}
add_filter('author_main_tabs_array', 'mrhe_attachment_author_album_tab', 10, 2);

// —— 作者页：相册内容渲染 ——
function mrhe_attachment_author_album_content() {
    global $wp_query, $wpdb;

    // 检查总开关
    if (!_mrhe('attachment_manager_s')) {
        return '';
    }

    // 检查作者相册开关
    if (!_mrhe('author_album_enable', true)) {
        return '';
    }

    $curauth = $wp_query->get_queried_object();
    if (empty($curauth->ID)) {
        return;
    }

    $author_id     = (int) $curauth->ID;
    $is_super_admin = is_super_admin();

    // 分页与每页数量
    $paged = isset($_GET['album_paged']) ? (int) $_GET['album_paged'] : 1;
    if ($paged < 1) $paged = 1;
    $per_page = (int) _mrhe('attachment_list_number', 16);
    if ($per_page < 1) $per_page = 16;

    // 构建查询参数：管理员查看全部附件，普通用户按设置显示图片/视频/两者
    $args = array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    if ($is_super_admin) {
        // 管理员：不限制作者与类型（显示全部附件）
    } else {
        // 非管理员：限制作者，并根据设置过滤mime类型
        $args['author'] = $author_id;
        $display_type   = _mrhe('author_album_type', 'image_video');
        if ($display_type === 'image') {
            $args['post_mime_type'] = 'image';
        } elseif ($display_type === 'video') {
            $args['post_mime_type'] = 'video';
        } else {
            $args['post_mime_type'] = array('image', 'video');
        }
    }

    $query = new WP_Query($args);

    // 列表HTML
    $html = '';
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $att_id   = get_the_ID();
            $mime     = get_post_mime_type($att_id);
            $thumb    = wp_get_attachment_image_src($att_id, 'thumbnail');
            $full     = wp_get_attachment_image_src($att_id, 'full');
            $title    = get_the_title($att_id);
            $size_txt = mrhe_attachment_format_file_size($att_id);

            $html .= '<posts class="posts-item card ajax-item">';
            $html .= '<div class="item-thumbnail imgbox-container">';
            if (strpos((string)$mime, 'image/') === 0 && !empty($thumb[0]) && !empty($full[0])) {
                // 图片：显示缩略图
                $html .= '<img src="' . esc_url($thumb[0]) . '" data-src="' . esc_url($full[0]) . '" alt="' . esc_attr($title) . '" class="fit-cover radius8 lazyloadafter" loading="lazy" imgbox-index="0">';
            } else {
                // 非图片：显示文件类型图标
                $svg_icon = mrhe_attachment_get_file_type_icon((string)$mime);
                $html .= '<div class="file-type-icon" style="display:flex;justify-content:center;align-items:center;font-size:50px;margin:20%">' . $svg_icon . '</div>';
            }
            // 操作按钮：查看 + 管理员删除
            $html .= '<div class="but-average" style="z-index:1;position:absolute;bottom:0;width:100%">';
            // 查看按钮：若因权限未返回，则使用弹窗链接兜底
            $view_link = mrhe_attachment_view_link($att_id, 'but c-blue', ' ' . zib_get_svg('view') . '查看', 'a');
            if (empty($view_link)) {
                $args_view = array(
                    'tag'           => 'a',
                    'class'         => 'but c-blue',
                    'data_class'    => 'full-sm',
                    'height'        => 400,
                    'mobile_bottom' => true,
                    'text'          => ' ' . zib_get_svg('view') . '查看',
                    'query_arg'     => array('action' => 'mrhe_attachment_view_modal', 'id' => $att_id),
                );
                $view_link = zib_get_refresh_modal_link($args_view);
            }
            $html .= $view_link;

            // 管理员可以删除所有附件（权限由WP控制）
            if ($is_super_admin && current_user_can('delete_post', $att_id)) {
                $delete_link = mrhe_attachment_delete_link($att_id, 'but c-red', '<i class="fa fa-trash-o" aria-hidden="true"></i>删除', 'a');
                if (empty($delete_link)) {
                    // 选项禁用时的降级方案：直接创建刷新弹窗链接
                    $args_link = array(
                        'tag'           => 'a',
                        'class'         => 'but c-red',
                        'data_class'    => 'modal-mini',
                        'height'        => 240,
                        'mobile_bottom' => true,
                        'text'          => '<i class="fa fa-trash-o" aria-hidden="true"></i>删除',
                        'query_arg'     => array('action' => 'mrhe_attachment_delete_modal', 'id' => $att_id),
                    );
                    $html .= zib_get_refresh_modal_link($args_link);
                } else {
                    $html .= $delete_link;
                }
            }
            $html .= '</div>'; // 操作按钮结束
            $html .= '</div>'; // item-thumbnail
            $html .= '<div class="item-content mt6">';
            $html .= '<div class="item-title text-ellipsis">' . esc_html($title) . '</div>';
            $html .= '<div class="em09 muted-2-color">' . esc_html($size_txt) . '</div>';
            $html .= '</div>';
            $html .= '</posts>';
        }
        wp_reset_postdata();
    } else {
        $html .= zib_get_ajax_null('暂无图片', 40, 'null-order.svg');
    }

    // 统计总数用于分页
    if ($is_super_admin) {
        // 管理员：全站所有附件数量
        $total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='attachment' AND post_status='inherit'");
    } else {
        $display_type = _mrhe('author_album_type', 'image_video');
        if ($display_type === 'image') {
            $total_items = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='attachment' AND post_status='inherit' AND post_mime_type LIKE %s AND post_author=%d",
                    'image/%',
                    $author_id
                )
            );
        } elseif ($display_type === 'video') {
            $total_items = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='attachment' AND post_status='inherit' AND post_mime_type LIKE %s AND post_author=%d",
                    'video/%',
                    $author_id
                )
            );
        } else {
            $total_items = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='attachment' AND post_status='inherit' AND post_author=%d AND (post_mime_type LIKE %s OR post_mime_type LIKE %s)",
                    $author_id,
                    'image/%',
                    'video/%'
                )
            );
        }
    }

    // 分页：沿用主题的AJAX分页风格（路由模式，ajax_url传false）
    $ajax_url = false;
    if (_mrhe('paging_ajax_s', '1') === '1') {
        $html .= zib_get_ajax_next_paginate($total_items, $paged, $per_page, $ajax_url, 'text-center theme-pagination ajax-pag', 'next-page ajax-next', '', 'album_paged');
    } else {
        $html .= zib_get_ajax_number_paginate($total_items, $paged, $per_page, $ajax_url, 'ajax-pag', 'next-page ajax-next', 'album_paged');
    }

    return $html;
}
add_filter('main_author_tab_content_album', 'mrhe_attachment_author_album_content');

