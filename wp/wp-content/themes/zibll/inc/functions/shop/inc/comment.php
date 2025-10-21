<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-12-23 22:31:32
 * @LastEditTime: 2025-10-06 20:10:29
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题 | 商品评论
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

/**
 * @description: 获取商品评论数量
 * @param {*} $post 商品ID
 * @param {*} $cut 是否裁剪
 * @return {*}
 */
function zib_shop_get_comment_count($post = null, $cut = true)
{
    if (!$post && !is_object($post)) {
        $post = get_post($post);
    }

    if (!isset($post->ID)) {
        return 0;
    }

    //先从缓存获取
    $cache = wp_cache_get($post->ID, 'shop_comment_count');
    if ($cache !== false) {
        return $cut ? _cut_count($cache) : $cache;
    }

    //只获取一级评论
    $args = array(
        'post_id' => $post->ID,
        'status'  => 'approve',
        'count'   => true,
        'parent'  => 0,
    );
    $count = (int) get_comments($args);
    //写入缓存
    wp_cache_set($post->ID, $count, 'shop_comment_count');

    return $cut ? _cut_count($count) : $count;
}

//更新订单的评论状态
function zib_shop_update_order_comment_status($order_id, $status)
{
    $comment_status = zibpay::update_meta($order_id, 'comment_status', $status);
    return $comment_status;
}

//获取订单的评论状态
function zib_shop_get_order_comment_status($order_id)
{

    $comment_status = (int) zibpay::get_meta($order_id, 'comment_status');
    if ($comment_status === 0) {
        $comment_over_time = zib_shop_get_order_comment_over_time($order_id);
        if ($comment_over_time === 'over') {
            return 1;
        }
    }

    return $comment_status;
}

//获取评论状态名称
function zib_shop_get_order_comment_status_name($comment_status = '')
{

    $status_name = [
        -1 => '未开启评论',
        0  => '待评价',
        1  => '已评价',
        2  => '售后中无法评价', //售后流程会自动处理
    ];

    if (!$comment_status) {
        return $status_name;
    }

    return $status_name[$comment_status] ?? '';
}

//获取订单的评论时效：剩余评论时间
function zib_shop_get_order_comment_over_time($order_id)
{

    //确认收货时间
    $receive_time = zibpay::get_meta($order_id, 'order_data.shipping_data.receive_time');
    if (empty($receive_time)) {
        return false;
    }

    $current_time = current_time('Y-m-d H:i:s');
    $max_time     = _pz('shop_comment_max_day', 15) ?: 15; //默认15天

    //计算剩余确认收货时间
    $last_time = strtotime('+ ' . $max_time . ' day', strtotime($receive_time));

    if (strtotime($current_time) > $last_time) {
        //自动好评
        zib_shop_order_auto_comment($order_id);
        return 'over';
    }

    return $last_time;
}

//初始化评论状态
function zib_shop_init_order_comment_status(array $order)
{

    $init_comment_status = zib_shop_product_is_open_comment($order['post_id']) ? 0 : -1; //初始化评论状态
    zib_shop_update_order_comment_status($order['id'], $init_comment_status);
}

//系统自动好评
function zib_shop_order_auto_comment($order_id)
{

    /**
     * 注意：此处不能使用
     * $comment_status = zib_shop_get_order_comment_status($order_id);
     * 会进入死循环
     */

    $comment_status = (int) zibpay::get_meta($order_id, 'comment_status');
    if ($comment_status !== 0) {
        return;
    }

    $order = zibpay::get_order($order_id);

    $data = [
        'comment'    => '',
        'score_data' => [
            'product' => 5,
            'service' => 5,
        ],
        'is_auto'    => true,
    ];

    if (zib_shop_get_order_delivery_type($order_id) === 'express') {
        $data['score_data']['shipping'] = 5;
    }

    zib_shop_order_comment_handle($order, $data);
}

//订单评价统一处理函数
function zib_shop_order_comment_handle(array $order, array $data)
{

    $data_example = [
        'comment'    => '',
        'score_data' => [
            'product'  => 5,
            'service'  => 5,
            'shipping' => 5, //可选
        ],
        'img_ids'    => [], //可选
        'is_auto'    => true, //只有超时自动好评才设置为true
    ];

    $user     = get_userdata($order['user_id']);
    $order_id = $order['id'];
    //判断完成
    $comment_data = array(
        'comment_post_ID'      => $order['post_id'],
        'comment_content'      => $data['comment'],
        'comment_author'       => $user->display_name,
        'comment_author_email' => $user->user_email,
        'comment_author_url'   => $user->user_url,
        'user_id'              => $user->ID,
    );

    //允许重复评论
    add_filter('duplicate_comment_id', '__return_false');
    //直接批准，无需审核
    add_filter('pre_comment_approved', function () {
        return 1;
    });

    $comment_id = wp_new_comment(wp_slash($comment_data), true);
    if (is_wp_error($comment_id)) {
        return $comment_id;
    }

    $score_data            = $data['score_data'] ?? [];
    $score_data['average'] = (string) round(array_sum($score_data) / count($score_data), 2);
    //更新评论平均评分
    update_comment_meta($comment_id, 'score', $score_data['average']);

    $score_data['has_image']       = !empty($data['img_ids']) ? 1 : 0;
    $score_data['is_auto']         = !empty($data['is_auto']) ? 1 : 0;
    $comment_score_data            = $score_data;
    $comment_score_data['img_ids'] = $data['img_ids'] ?? [];
    //更新评论图片
    if ($score_data['has_image']) {
        update_comment_meta($comment_id, 'shop_has_image', 1);
    }
    zib_update_comment_meta($comment_id, 'score_data', $comment_score_data);

    //获取订单数据
    $order_data         = zibpay::get_meta($order_id, 'order_data');
    $comment_order_data = array(
        'options_active_name' => $order_data['options_active_name'],
        'count'               => $order_data['count'],
        'order_id'            => $order_id,
    );
    zib_update_comment_meta($comment_id, 'order_data', $comment_order_data);

    //更新商品评分
    $product_score = zib_shop_update_product_score($order['post_id'], $score_data);

    //更新订单评论状态：已评价
    zib_shop_update_order_comment_status($order_id, 1);

    //删除评价数量缓存
    wp_cache_delete($order['post_id'], 'shop_comment_count');

    return [
        'order_id'      => $order_id,
        'product_id'    => $order['post_id'],
        'comment_id'    => $comment_id,
        'product_score' => $product_score,
        'comment_score' => $comment_score_data,
    ];
}

function zib_shop_get_score_average_name_data($score)
{

    $score                   = (float) $score;
    $score_average_name_args = [
        '4.6' => ['超赞', 'c-red'],
        '4'   => ['很棒', 'c-red'],
        '3.5' => ['不错', 'c-yellow'],
        '2.8' => ['一般', ''],
        '2'   => ['较差', ''],
        '0'   => ['很差', ''],
    ];

    foreach ($score_average_name_args as $k => $v) {
        if ($score >= (float) $k) {
            return $v;
        }
    }

    return ['', ''];
}

function zib_shop_comment_paginate()
{
    echo zib_get_comment_paginate(_pz('comment_paginate_type'), _pz('comment_paging_ajax_ias_s'), _pz('comment_paging_ajax_ias_max', 3));
}

//评论查询筛选
function zib_shop_comments_template_query_args($comment_args)
{

    $ctype = !empty($_REQUEST['ctype']) ? $_REQUEST['ctype'] : '';

    if ($ctype == 'good') {
        $comment_args['meta_query'][] =
            [
            'key'     => 'score',
            'value'   => 3.5,
            'compare' => '>=',
        ];
    } elseif ($ctype == 'bad') {
        $comment_args['meta_query'][] =
            [
            'key'     => 'score',
            'value'   => 3.5,
            'compare' => '<',
        ];
    } elseif ($ctype == 'image') {
        $comment_args['meta_query'][] =
            [
            'key'     => 'shop_has_image',
            'value'   => 1,
            'compare' => '=',
        ];
    }

    return $comment_args;
}
add_filter('comments_template_query_args', 'zib_shop_comments_template_query_args');

//当删除评论或改变评论状态时候，更新商品的对应评论数据量
function zib_shop_update_product_score_counts($comment_id)
{
    $comment = get_comment($comment_id);

    if (empty($comment->comment_post_ID)) {
        return;
    }

    $post_id   = $comment->comment_post_ID;
    $post_type = get_post_type($post_id);
    if ($post_type !== 'shop_product') {
        return;
    }

    //删除评价数量缓存
    wp_cache_delete($post_id, 'shop_comment_count');

    $score_data = zib_get_post_meta($post_id, 'score_data', true);
    if (!$score_data) {
        $score_data = array(
            'average'  => 5,
            'product'  => 5,
            'service'  => 5,
            'shipping' => 5,
            'count'    => 0, //评价总次数
            'counts'   => array(
                'has_image' => 0,
                'good'      => 0,
                'bad'       => 0,
            ),
        );
    }

    $args = [
        'post_id' => $post_id,
        'status'  => 'approve',
        'count'   => true,
    ];

    //获取好评评论数量
    $args['meta_query'] = [
        [
            'key'     => 'score',
            'value'   => 3.5,
            'compare' => '>='],
    ];
    $count                        = get_comments($args);
    $score_data['counts']['good'] = $count;

    //获取差评评论数量
    $args['meta_query'] = [
        [
            'key'     => 'score',
            'value'   => 3.5,
            'compare' => '<'],
    ];
    $count                       = get_comments($args);
    $score_data['counts']['bad'] = $count;

    //获取有图的评论数量
    $args['meta_query'] = [
        [
            'key'     => 'shop_has_image',
            'value'   => 1,
            'compare' => '=',
        ],
    ];
    $count                             = get_comments($args);
    $score_data['counts']['has_image'] = $count;

    //更新评分数据
    zib_update_post_meta($post_id, 'score_data', $score_data);
}
add_action('wp_set_comment_status', 'zib_shop_update_product_score_counts');

function zib_shop_get_comment_link($link, $comment)
{

    $post_type = get_post_type($comment->comment_post_ID);
    if ($post_type !== 'shop_product') {
        return $link;
    }

    if (zib_shop_single_comment_is_show_tab()) {
        $link = add_query_arg('tab', 'comment', $link);
    }

    return $link;
}
add_filter('get_comment_link', 'zib_shop_get_comment_link', 10, 2);
