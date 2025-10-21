<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime: 2025-09-08 14:19:07
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|商城系统|商品功能函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

/**
 * @description: 获取商品收藏按钮
 * @param {*} $posts_id
 * @param {*} $class
 * @param {*} $text
 * @param {*} $ok_text
 * @param {*} $icon
 * @param {*} $count
 * @return {*}
 */
function zib_shop_get_product_favorite_btn($posts_id, $class = 'but', $text = '收藏', $ok_text = '已收藏', $icon = true, $count = false)
{

    if (!$posts_id) {
        global $post;
        $posts_id = $post->ID;
    }
    $user_id = get_current_user_id();
    if (zib_is_my_meta_ed('favorite_product', $posts_id)) {
        $class .= ' active';
        $text = $ok_text;
    }
    $action = ' ajax-action="favorite_product"';
    $icon   = $icon ? '<icon>' . zib_get_svg('favorite') . '</icon>' : '';

    if ($count) {
        $count = (int) get_post_meta($posts_id, 'favorite_count', true);
        $count = '<count class="">' . ($count ? _cut_count($count) : '') . '</count>';
    }

    if (!$user_id) {
        $action = '';
        $class .= ' signin-loader';
    }

    return '<a href="javascript:;"' . $action . ' class="btn-favorite ' . $class . '" data-id="' . $posts_id . '">' . $icon . '<text>' . $text . $count . '</text></a>';
}

//获取商品销量
function zib_shop_get_product_sales_volume($product_id, $cut = false)
{
    $count = (int) get_post_meta($product_id, 'sales_volume', true);
    return $cut ? _cut_count($count) : $count;
}

//更新商品销量
function zib_shop_update_product_sales_volume($product_id, $count)
{
    if (!$product_id) {
        return;
    }

    $sales_count = zib_shop_get_product_sales_volume($product_id, false);
    $sales_count += (int) $count;
    update_post_meta($product_id, 'sales_volume', $sales_count);
}

//获取商品销量显示
function zib_shop_get_product_show_sales_count($product_id)
{

    $show_sales = _pz('shop_sales_show', '');
    if ($show_sales === 'off') {
        return '';
    }

    $sales_count = zib_shop_get_product_sales_volume($product_id, false);
    if (!$sales_count) {
        return '';
    }

    if ($show_sales === 'min') {
        $min = _pz('shop_sales_show_min', 0);
        if ($min > 0 && $min >= $sales_count) {
            return '';
        }
    }

    return _cut_count($sales_count);
}

//获取商品的限购配置
function zib_shop_get_product_limit_buy_config($product_id)
{
    $config      = zib_shop_get_product_config($product_id, 'limit_buy', true);
    $config_type = $config['type'] ?? '';
    $off_return  = array(
        'type'     => 'off',
        'is_limit' => false,
    );
    if ($config_type === 'off') {
        return $off_return;
    }

    if (!$config_type) {
        //从分类获取
        $config      = zib_shop_get_product_cat_config($product_id, 'limit_buy', 'type');
        $config_type = $config['type'] ?? '';
        if ($config_type === 'off') {
            return $off_return;
        }

        //从主题配置获取
        if (!$config_type) {
            $config      = _pz('shop_limit_buy', array());
            $config_type = $config['type'] ?? '';
            if ($config_type === 'off') {
                return $off_return;
            }
        }
    }

    if ($config_type !== 'on') {
        return $off_return;
    }

    $user_id            = get_current_user_id();
    $limit              = (int) $config['all'];
    $config['limit']    = $limit;
    $config['is_limit'] = $limit !== -1;

    if ($config['is_limit']) {
        $config['key_names'] = [
            'all'   => '普通用户',
            'auth'  => '认证用户',
            'vip_1' => _pz('pay_user_vip_1_name', '黄金会员'),
            'vip_2' => _pz('pay_user_vip_2_name', '钻石会员'),
        ];
    }

    if (!$user_id) {
        return $config;
    }

    //会员
    $user_level = zib_get_user_level($user_id);
    if ($config['is_limit'] && $user_level && isset($config['vip_' . $user_level]) && _pz('pay_user_vip_' . $user_level . '_s', true)) {
        $_limit = (int) $config['vip_' . $user_level];
        $limit  = $_limit === -1 ? $_limit : max($limit, $_limit);
    }

    //认证
    if ($config['is_limit'] && _pz('user_auth_s', true) && zib_is_user_auth($user_id)) {
        $_limit = (int) $config['auth'];
        $limit  = $_limit === -1 ? $_limit : max($limit, $_limit);
    }

    $config['is_limit']  = $limit !== -1;
    $config['limit']     = $limit; //最终限制数量，减去已购数量
    $config['limit_all'] = $limit; //不减去已购数量

    if ($config['is_limit'] && $config['limit'] > 0) {
        $count = zib_shop_get_user_bought_product_count($user_id, $product_id);
        $config['limit'] -= $count;
        $config['limit']        = $config['limit'] < 0 ? 0 : $config['limit'];
        $config['bought_count'] = $count;
    }

    return $config;
}

//获取用户已购商品数量
function zib_shop_get_user_bought_product_count($user_id, $product_id)
{
    $where = array(
        'user_id'    => $user_id,
        'post_id'    => $product_id,
        'status'     => [0, 1],
        'order_type' => zib_shop_get_order_type(),
        'fields'     => 'count',
    );

    $count = zibpay::order_query($where)['total'] ?? 0;

    return $count;
}

//获取商品是否开启评论
function zib_shop_product_is_open_comment($product_id)
{
    $config = zib_shop_get_product_in_turn_config($product_id, 'comment_s', true);

    if ($config === 'off') {
        return false;
    }

    return true;
}

/**
 * 获取商品参数配置，会从商品、分类、主题配置依次获取
 * 适用于：自定义服务保障、详情页底部内容、商品详情背景盒子、列表显示样式、商品详情页布局
 */
function zib_shop_get_product_in_turn_config($product_id, $key, $default = '')
{
    $config = zib_shop_get_product_config($product_id, $key, true);
    if (!$config) {
        $config = zib_shop_get_product_cat_config($product_id, $key);
    }
    if (!$config) {
        $config = _pz('shop_' . $key, $default);
    }

    return $config;
}

//获取商品选项库存
function zib_shop_get_product_opt_stock($product_id, $options_active)
{
    $options_string = zib_shop_product_options_to_string($options_active);
    $stock_type     = zib_shop_get_product_config($product_id, 'stock_type', 'all');

    $stock_all = (int) zib_shop_get_product_config($product_id, 'stock_all', -1);
    if ($stock_type !== 'opts') {
        return $stock_all;
    }

    //如果商品没有选项，则返回库存
    if (!isset(zib_shop_get_product_config($product_id, 'product_options')[0])) {
        return $stock_all;
    }

    //获取选项库存
    return (int) zib_shop_get_product_config($product_id, 'stock_opts.' . $options_string, 0);
}

//扣减商品库存
function zib_shop_product_deduct_stock($product_id, $options_active, $count)
{
    $options_string = zib_shop_product_options_to_string($options_active);
    $stock_type     = zib_shop_get_product_config($product_id, 'stock_type', 'all');

    if ($stock_type === 'all') {
        $stock_all = (int) zib_shop_get_product_config($product_id, 'stock_all', -1);
        if ($stock_all <= 0) {
            return;
        }
        $stock_all -= $count;
        zib_shop_save_product_config($product_id, 'stock_all', $stock_all);
    } else {
        $stock_opts = zib_shop_get_product_config($product_id, 'stock_opts.' . $options_string, 0);
        if ($stock_opts <= 0) {
            return;
        }
        $stock_opts -= $count;
        zib_shop_save_product_config($product_id, 'stock_opts.' . $options_string, $stock_opts);
    }
}

//添加商品库存
function zib_shop_product_add_stock($product_id, $options_active, $count)
{
    $options_string = zib_shop_product_options_to_string($options_active);
    $stock_type     = zib_shop_get_product_config($product_id, 'stock_type', 'all');

    if ($stock_type === 'all') {
        $stock_all = (int) zib_shop_get_product_config($product_id, 'stock_all', -1);
        if ($stock_all == -1) {
            return;
        }
        if ($count == -1) {
            $stock_all = -1;
        } else {
            $stock_all += $count;
        }
        zib_shop_save_product_config($product_id, 'stock_all', $stock_all);
    } else {
        $stock_opts = zib_shop_get_product_config($product_id, 'stock_opts.' . $options_string, 0);
        if ($stock_opts == -1) {
            return;
        }
        if ($count == -1) {
            $stock_opts = -1;
        } else {
            $stock_opts += $count;
        }
        zib_shop_save_product_config($product_id, 'stock_opts.' . $options_string, $stock_opts);
    }
}

//获取商品的运费配置
function zib_shop_get_product_shipping_fee_config($product_id)
{
    $config      = zib_shop_get_product_config($product_id, 'shipping_fee_opt', true);
    $pay_modo    = zib_shop_get_product_config($product_id, 'pay_modo', '0');
    $config_type = $config['type'] ?? '';
    $desc        = $config['desc'] ?? '';

    if (!$config_type) {
        //从分类获取
        $config      = zib_shop_get_product_cat_config($product_id, 'shipping_fee_opt', 'type');
        $config_type = $config['type'] ?? '';
        $desc        = $desc ?: $config['desc'] ?? '';
    }

    if (!$config_type) {
        $config      = _pz('shop_shipping_fee_opt', array());
        $config_type = $config['type'] ?? 'free';
        $desc        = $desc ?: $config['desc'] ?? '';
    }

    $config['desc'] = $desc;

    if ($config_type === 'free' || $pay_modo === 'points') {
        return array(
            'type' => 'free',
            'desc' => $desc,
        );
    }

    if ($config_type === 'fixed' && !$config['fixed_fee']) {
        return array(
            'type' => 'free',
            'desc' => $desc,
        );
    }

    if ($config_type === 'amount' && (!$config['amount_fee']['fee'] || !$config['amount_fee']['free_amount'])) {
        return array(
            'type' => 'free',
            'desc' => $desc,
        );
    }

    return $config;
}

/**
 * 获取商品的展示价格
 * @param int $product_id 商品ID
 * @param array $option_keys 可选参数，用于获取不同的价格选项
 * @return float 最终折扣价
 */
function zib_shop_get_product_display_price($product_id, $option_keys = [])
{
    $start_price = (float) zib_shop_get_product_config($product_id, 'start_price', true); //初始价格
    $pay_modo    = zib_shop_get_product_config($product_id, 'pay_modo'); //支付方式
    $is_points   = $pay_modo === 'points';

    $discount_dependency = [
        'item_data'    => [
            'price' => $start_price,
            'count' => 1,
        ],
        'product_data' => [
            'price' => $start_price,
            'count' => 1,
        ],
        'author_data'  => [
            'price' => $start_price,
            'count' => 1,
        ],
        'total_data'   => [
            'price' => $start_price,
            'count' => 1,
        ],
    ];

    $product_discounts = zib_shop_get_product_discount($product_id);
    //获取优惠活动，计算折后价
    if ($product_discounts) {
        foreach ($product_discounts as $discount_item) {
            $discount_price_calculate = zib_shop_discount_price_calculate($discount_item, $discount_dependency, $start_price, 1, ['price']);
            $start_price -= zib_shop_format_price($discount_price_calculate['preferential_amount'], $is_points);
        }
    }

    return zib_shop_format_price($start_price, $is_points);
}

//获取商品的服务配置
function zib_shop_get_product_service($product_id)
{
    $_service = zib_shop_get_product_in_turn_config($product_id, 'service', array());

    if ($_service && is_array($_service)) {
        return array_filter($_service, function ($value) {
            return $value['name'];
        });
    }

    return [];
}

//获取商品的售后政策
function zib_shop_get_product_after_sale_opt($product_id)
{
    $opt    = [];
    $config = zib_shop_get_product_config($product_id, 'after_sale_opt', true);
    if (isset($config['type']) && isset($config['opt']) && $config['type'] === 'custom') {
        $opt = $config['opt'];
    }

    if (!$opt) {
        $config = zib_shop_get_product_cat_config($product_id, 'after_sale_opt', 'type');
        if (isset($config['type']) && isset($config['opt']) && $config['type'] === 'custom') {
            $opt = $config['opt'];
        }
    }

    if (!$opt) {
        $config = _pz('shop_after_sale_opt', array());
        $opt    = $config ?: [];
    }

    //商品如果是虚拟商品，则不支持退款、换货、保修
    if (zib_shop_get_product_config($product_id, 'shipping_type') === 'auto') {
        $opt['refund_return'] = false;
        $opt['replacement']   = false;
        $opt['warranty']      = false;
    }

    return $opt;
}

/**
 * 获取商品优惠活动徽标
 * @param int $post_id 商品ID
 * @return string 活动徽标HTML
 */
function zib_shop_get_product_discount_badges($post_id)
{
    //活动
    $activity = zib_shop_get_product_discount($post_id);

    if (!$activity) {
        return '';
    }

    $html = '';
    foreach ($activity as $item) {
        $html .= '<span class="badge badge-discount">' . $item['small_badge'] . '</span>';
    }
    return $html;
}

//获取商品配置的封装函数
function zib_shop_get_product_config($post_id, $key = null, $default = '')
{

    if (is_object($post_id) && isset($post_id->ID)) {
        $post_id = $post_id->ID;
    }

    //定义静态变量
    static $product_configs = array();
    if (!isset($product_configs[$post_id])) {
        $product_configs[$post_id] = get_post_meta($post_id, 'product_config', true) ?: array();
    }

    return zib_get_array_value($product_configs[$post_id], $key, $default);
}

//同步meta数据
function zib_shop_product_meta_update($meta_id, $post_id, $meta_key, $_meta_value)
{

    if ($meta_key !== 'product_config' || !isset($_meta_value['start_price'])) {
        return;
    }

    $pay_modo    = isset($_meta_value['pay_modo']) ? $_meta_value['pay_modo'] : '0';
    $start_price = $_meta_value['start_price'];

    update_post_meta($post_id, 'zibpay_modo', $pay_modo);
    update_post_meta($post_id, 'zibpay_price', $start_price);
}
add_action('updated_post_meta', 'zib_shop_product_meta_update', 99, 4);
add_action('added_post_meta', 'zib_shop_product_meta_update', 99, 4);

//保存商品配置
function zib_shop_save_product_config($post_id, $key, $value)
{
    $config = zib_shop_get_product_config($post_id);
    $config = zib_set_array_value($config, $key, $value);
    update_post_meta($post_id, 'product_config', $config);
}

/**
 * 获取商品主图
 * @param null|int|WP_Post $post
 * @param string $class
 * @param string $size
 * @return string
 */
function zib_shop_get_product_thumbnail($post = null, $class = '', $size = 'medium')
{
    if (!is_object($post)) {
        $post = get_post($post);
    }

    $img_url    = zib_shop_get_product_thumbnail_url($post, $size);
    $_lazy_attr = zib_get_lazy_attr('lazy_posts_thumb', $img_url, $class);
    $alt        = $post->post_title . zib_get_delimiter_blog_name();
    $img_html   = '<img' . $_lazy_attr . ' src="' . $img_url . '" alt="' . $alt . '">';
    return $img_html;
}

//获取商品的图像
function zib_shop_get_product_thumbnail_url($post = null, $size = 'medium')
{
    if (!is_object($post)) {
        $post = get_post($post);
    }

    if (!$post) {
        return '';
    }

    //缓存
    $cache_url = wp_cache_get($post->ID, 'post_thumbnail_url_' . $size, true);
    if ($cache_url !== false) {
        return $cache_url;
    }

    //获取商品主图
    $img_url = zib_shop_get_product_config($post->ID, 'main_image', true);
    if ($img_url && ($size && 'full' !== $size)) {
        $img_id = zib_get_image_id($img_url);
        if ($img_id) {
            $img = wp_get_attachment_image_src($img_id, $size);
            if (isset($img[0])) {
                $img_url = $img[0];
            }
        }
    }

    //获取第一张封面图
    if (!$img_url) {
        $cover_images = explode(',', zib_shop_get_product_config($post->ID, 'cover_images', true));
        if (is_array($cover_images) && isset($cover_images[0]) && (int) $cover_images[0]) {
            $img_url = zib_get_attachment_image_src((int) $cover_images[0], $size)[0] ?? '';
        }
    }

    if (!$img_url) {
        $img_url = _pz('shop_main_image_default', '') ?: ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail.svg';
    }

    //缓存
    wp_cache_set($post->ID, $img_url, 'post_thumbnail_url_' . $size);

    return $img_url;
}

/**
 * 将商品选项数组转换为字符串
 * @param array $options_key 选项数组
 * @return string 拼接后的选项字符串
 */
function zib_shop_product_options_to_string($options_key)
{
    $options_string = '';
    if (is_array($options_key)) {
        foreach ($options_key as $key => $value) {
            $options_string .= zib_shop_product_options_key_splicing($key, $value); //统一拼接规范
        }
    } else {
        $options_string = $options_key;
    }

    if (!$options_string) {
        return '0';
    }

    return $options_string;
}

//拼接商品选项的key
function zib_shop_product_options_key_splicing($opt_key, $item_kye)
{
    return '|' . $opt_key . '_' . $item_kye;
}

//拆分商品选项的key
function zib_shop_product_options_to_array($opt_key_str)
{
    if (is_array($opt_key_str)) {
        return $opt_key_str;
    }

    $opt_key_str     = ltrim($opt_key_str, '|'); //去除第一个|
    $opt_key_arr     = explode('|', $opt_key_str);
    $opt_key_arr_new = [];
    foreach ($opt_key_arr as $item) {
        $item_arr = explode('_', $item);
        if (isset($item_arr[0]) && isset($item_arr[1])) {
            $opt_key_arr_new[$item_arr[0]] = $item_arr[1];
        }
    }
    return $opt_key_arr_new;
}

/**
 * 更新商品评分
 * @param int $product_id 商品ID
 * @param array $score_data 评分数据
 */
function zib_shop_update_product_score($product_id, $new_score_data)
{
    $score_data = zib_get_post_meta($product_id, 'score_data', true);
    $is_new     = false;
    if (!$score_data) {
        $score_data = array(
            'average'  => 5,
            'product'  => 5,
            'service'  => 5,
            'shipping' => 5,
            'count'    => 0, //次数
            'counts'   => array(
                'has_image' => 0,
                'good'      => 0,
                'bad'       => 0,
            ),
        );
        $is_new = true;
    }

    foreach ($new_score_data as $key => $value) {
        if (!in_array($key, ['product', 'service', 'shipping', 'average'])) {
            continue;
        }
        //计算平均
        if ($is_new) {
            $score_data[$key] = (string) $value;
        } else {
            $score_data[$key] = (string) round(((float) $score_data[$key] + (float) $value) / 2, 2);
        }
    }
    $score_data['count'] += 1;

    if (!empty($new_score_data['has_image'])) {
        $score_data['counts']['has_image'] += 1;
    }

    if ($score_data['average'] >= 3.5) {
        $score_data['counts']['good'] += 1;
    } else {
        $score_data['counts']['bad'] += 1;
    }

    zib_update_post_meta($product_id, 'score_data', $score_data);
    zib_shop_save_product_score($product_id, $score_data['average']);
    return $score_data;
}

//获取总评分
function zib_shop_get_product_score($product_id)
{
    $score_data = zib_floatval_round(get_post_meta($product_id, 'score', true));
    return $score_data;
}

//保存总评分
function zib_shop_save_product_score($product_id, $score)
{
    update_post_meta($product_id, 'score', $score);
}

//获取商品链接
function zib_shop_get_product_link($product_id, $class = '', $text = '')
{
    $url = get_permalink($product_id);
    if (!$url) {
        return '';
    }
    return '<a href="' . $url . '" class="' . $class . '">' . $text . '</a>';
}

//判断商品选项是否存在
function zib_shop_product_options_is_exists($product_id, $options_key = '')
{
    $options         = zib_shop_product_options_to_array($options_key);
    $product_options = zib_shop_get_product_config($product_id, 'product_options', true);

    if (isset($product_options[0]['opts'])) {
        if (count($options) !== count($product_options)) {
            return false;
        }

        foreach ($options as $key => $value) {
            if (!isset($product_options[$key]['opts'][$value])) {
                return false;
            }
        }
    }

    return true;
}

function zib_shop_get_product_share_btn($post_id, $class = 'but')
{
    return zib_get_post_share_btn($post_id, 'btn-share ' . $class, true);
}
