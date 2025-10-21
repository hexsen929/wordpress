<?php

/** 
 * @ name 文章新增相关 Meta
 * @description 创建 Meta 时请参照如下方式
	$meta_conf = array(
		'position'  => 'side',
		'priority'  => 'low',
	    'box_id'    => "box_id",
	    'box_title' => "box_title",
	    'ipt_id'    => "ipt_id",
	    'ipt_name'  => "ipt_name",
	    'div_class' => "div_class",
			position：Box显示在边栏或者默认位置可选side、默认normal
			priority: 模块显示的优先级（'high', 'core', 'default'or 'low'）默认high
			box_id：后台 Meta 盒子的 ID 属性。
			box_title：后台 Meta 盒子的标题属性。
			ipt_id：代码中提交元素的 ID 属性。
			ipt_name：代码中提交元素的 Name 属性。
			div_class：后台 Meta 盒子中各项目的 Class 样式属性。
			name：后台每一个 Meta 项目的 name 属性，用于生成数据库中对应的字段。
			std：后台每一个 Meta 项目的默认值。
			title：后台每一个 Meta 项目的标题属性。
	);
	$my_meta = array(
		array(
		    'name'   => "Meta_name", // 对应数据库中meta_key
		    'std'    => "", 
		    'title'  => 'Meta_title：'
		),
	);
	new CreateMyMetaBox($meta_conf,$my_meta);
 */

class CreateMyMetaBox
{

	var $meta_conf, $my_meta, $post_id;

	function __construct($meta_conf, $my_meta)
	{
		$this->meta_conf   = $meta_conf;
		$this->my_meta     = $my_meta;

		add_action('admin_menu', array(&$this, 'my_meta_box_create'));
		add_action('save_post', array(&$this, 'my_meta_box_save'));
	}

	public function my_meta_box_create()
	{
		if (function_exists('add_meta_box')) {
			add_meta_box($this->meta_conf['box_id'], __($this->meta_conf['box_title'], 'MRHE'), array(&$this, 'my_meta_box_init'), 'post', $this->meta_conf['position'] ? $this->meta_conf['position'] : 'normal', $this->meta_conf['priority'] ? $this->meta_conf['priority'] : 'default');
		}
	}

	public function my_meta_box_init($post_id)
	{

		$class = $this->meta_conf['div_class'] ? $this->meta_conf['div_class'] : '';
		//$post_id = $_GET['post'];
		$post_id = isset($_GET['post']) ? $_GET['post'] : $post_id;

		foreach ($this->my_meta as $meta_box) {
			$meta_box_value = get_post_meta($post_id, $meta_box['name'], true);
			if ($meta_box_value == "") {
				$meta_box_value = $meta_box['std'];
			}
			if ($meta_box['name'] == 'subtitle') {
				echo '<p>' . (isset($meta_box['title']) ? $meta_box['title'] : '') . '</p>';
				echo '<p><input type="text" style="width:98%" value="' . $meta_box_value . '" name="' . $meta_box['name'] . '"></p>';
			} else if ($meta_box['type'] == 'checkbox') {
				echo '<p><label> ' . (isset($meta_box['title']) ? $meta_box['title'] : '') . '<input ' . ($meta_box_value ? 'checked' : '') . ' style="margin-left:10px" type="checkbox" value="1" name="' . $meta_box['name'] . '"></label></p>';
			} else if ($meta_box['type'] == 'number') {
				echo '<p>' . (isset($meta_box['title']) ? $meta_box['title'] : '');
				echo '<input type="number" style="width:80px;margin-left:30px;" value="' . $meta_box_value . '" name="' . $meta_box['name'] . '"></p>';
			} else if ($meta_box['type'] == 'textarea') {
				echo '<p>' . (isset($meta_box['title']) ? $meta_box['title'] : '');
				echo '<textarea type="text" style="width: 100%;height: 50px;" value="' . $meta_box_value . '" name="' . $meta_box['name'] . '">' . $meta_box_value . '</textarea></p>';
			} else {
				echo '<div class= ' . $class . '>
		    <p>' . $meta_box['title'] . '</p>
		    <p><input type="text" style="width:100%;" value="' . $meta_box_value . '" name="' . $meta_box['name'] . '"></p>
		    </div>';
			}
		}
		if ($this->meta_conf['function']) {
			$tui = get_post_meta($post_id, $this->meta_conf['function'], true);
			if ($tui) echo '<p>返回结果：' . $tui . '</p>';
		}
		echo '<input type="hidden" name="' . $this->meta_conf['ipt_name'] . '" id="' . $this->meta_conf['ipt_id'] . '" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';
	}

	public function my_meta_box_save($post_id)
	{

		//$post_id = $_POST['post_ID'];
		$post_id = isset($_POST['post_ID']) ? $_POST['post_ID'] : $post_id;
		if (!wp_verify_nonce(isset($_POST[$this->meta_conf['ipt_name']]) ? $_POST[$this->meta_conf['ipt_name']] : '', plugin_basename(__FILE__)))
			return;
		if (!current_user_can('edit_posts', $post_id))
			return;

		foreach ($this->my_meta as $meta_box) {

			$data = $_POST[$meta_box['name']] ? $_POST[$meta_box['name']] : "";

			if (get_post_meta($post_id, $meta_box['name']) == "") {
				add_post_meta($post_id, $meta_box['name'], $data, true);
			} elseif ($data != get_post_meta($post_id, $meta_box['name'], true)) {
				update_post_meta($post_id, $meta_box['name'], $data);
			} elseif ($data == "") {
				delete_post_meta($post_id, $meta_box['name'], get_post_meta($post_id, $meta_box['name'], true));
			}
		}
	}
}

/* 
 * 文章来源
 * ====================================================
*/
$postmeta_from_conf = array(
	'position'  => 'normal',
	'priority'  => 'high',
	'function'  => false,
	'box_id'    => "postmeta_from_conf_meta_box",
	'box_title' => '来源',
	'ipt_id'    => "postmeta_from_conf_meta_ipt_id",
	'ipt_name'  => "postmeta_from_conf_meta_ipt_name",
	'div_class' => "postmeta_from_conf-meta-box-s2",
);
$postmeta_from_s = array(
	array(
		"name" => "fromname_value",
		"std" => "",
		"title" => '来源名',
		"type" => "text",
		'function' => false
	),
	array(
		"name" => "fromurl_value",
		"std" => "",
		"title" => '来源网址',
		"type" => "text",
		'function' => false
	)
);
if (_mrhe('post_from_s')) {
	$postmeta_from_meta_box = new CreateMyMetaBox($postmeta_from_conf, $postmeta_from_s);
};

// /* 
//  * post meta from baidu tui
//  * ====================================================
// */
// $postmeta_baidu_s1 = array(
// 	'position'  => 'side',
// 	'priority'  => 'high',
// 	'function'  => 'baidu_tui_back',
// 	'box_id'    => "postmeta_baidu_s1_meta_box1",
// 	'box_title' => '百度资源提交',
// 	'ipt_id'    => "postmeta_baidu_s1_meta_ipt_id1",
// 	'ipt_name'  => "postmeta_baidu_s1_meta_ipt_name1",
// 	'div_class' => "postmeta_baidu_s1-meta-box-s21",
// );
// $postmeta_baidu1 = array(
//     array(
//         "title" => "百度资源提交",
//         "name" => "is_original",
//         "std" => false,
//         'type' => "checkbox",
//     ),
// );
// $postmeta_baidu_meta_box = new CreateMyMetaBox($postmeta_baidu_s1,$postmeta_baidu1);

// // 新文章发布时实时推送
// add_action('publish_post', 'tui_post_to_baidu');
// add_action( "publish_future_post", "tui_post_to_baidu" );
// function tui_post_to_baidu($postid) {
// 	if( _cao('baidu_tui_on') && _cao('baidu_tui_function')['baidu_post_token']){
// 	    global $post;
// 	    $plink = get_permalink($postid);

// 	    if( !$plink || get_post_meta($postid, 'baidu_tui_back', true) ){
// 	    	return false;
// 	    }
// 		$isoriginal = get_post_meta($postid, 'is_original', true);
// 	    $urls = array();
// 	    $urls[] = $plink;
// 		//$api = 'http://data.zz.baidu.com/urls?site='. home_url( '/' ) .'&token='. _cao('xzh_function')['xzh_post_token'];
// 		//$api = 'http://data.zz.baidu.com/urls?appid='. _cao('xzh_function')['xzh_appid'] .'&token='. _cao('xzh_function')['xzh_post_token'] .'&type=';
// 		if( $isoriginal ){
// 	    	//$api .= '&type=daily';
// 	    	$api = 'http://data.zz.baidu.com/urls?site='. home_url( '/' ) .'&token='. _cao('baidu_tui_function')['baidu_post_token'] .'&type=daily';
// 			$tui = '快速收录';
// 	    }else{
// 			$api = 'http://data.zz.baidu.com/urls?site='. home_url() .'&token='. _cao('baidu_tui_function')['baidu_post_token'];
// 			$tui = '普通收录';
// 		}
// 		$ch = curl_init();
// 		$options =  array(
// 		    CURLOPT_URL => $api,
// 		    CURLOPT_POST => true,
// 		    CURLOPT_RETURNTRANSFER => true,
// 		    CURLOPT_POSTFIELDS => implode("\n", $urls),
// 		    CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
// 		);
// 		curl_setopt_array($ch, $options);
// 		$result = curl_exec($ch);
// 		$result = json_decode($result);
// 		$result_text = $tui.'成功 '.$result->success.'条';
// 		if( $result->error ){
// 			$result_text = $tui.'失败 '.$result->message;
// 		}
// 		update_post_meta($postid, 'baidu_tui_back', $result_text);
	
// 	}
// }
// /* 后台文章列表添加推送显示 */
// add_filter('manage_posts_columns', 'mrhe_add_posts_baidu_submit_columns');
// function mrhe_add_posts_baidu_submit_columns($columns) {
// 	$columns['baidu'] = '百度推送';
// 	return $columns;
// }

// add_action('manage_posts_custom_column', 'mrhe_manage_posts_columns', 10, 2);
// function mrhe_manage_posts_columns($column_name, $id) {
// 	global $wpdb;
// 	switch ($column_name) {
// 		case 'baidu':
// 			$baidu_status =  get_post_meta($id, 'baidu_tui_back', true);
// 			if($baidu_status){
// 				$baidu = $baidu_status;
// 			}else{
// 				$baidu = '没有推送';
// 			}
// 			echo $baidu;
// 			break;
// 		default:
// 			break;
// 	}
// }


/* 
 * 产品购买功能 - 使用CSF框架
 * ====================================================
*/

// 添加产品购买Meta Box - 直接使用posts_zibpay
if (strpos($_SERVER['SCRIPT_NAME'], 'post-new.php') !== false || strpos($_SERVER['SCRIPT_NAME'], 'post.php') !== false) {
    CSF::createMetabox('mrhe_product_purchase', mrhe_product_purchase_meta());
    CSF::createSection('mrhe_product_purchase', array(
        'fields' => mrhe_product_purchase_fields(),
    ));
}

// 添加CSF框架专用保存钩子，同步数据到posts_zibpay
add_action('csf_mrhe_product_purchase_saved', 'mrhe_sync_to_posts_zibpay', 10, 3);
function mrhe_sync_to_posts_zibpay($data, $post_id, $csf_instance) {
    // 检查数据是否有效
    if (empty($data) || !is_array($data)) {
        return;
    }
    
    // 检查是否是我们关心的post类型
    if (!in_array(get_post_type($post_id), array('page'))) {
        return;
    }
    
    // 同步到posts_zibpay字段，供父主题使用
    update_post_meta($post_id, 'posts_zibpay', $data);
    
}

// 添加产品购买订单处理钩子
add_filter('initiate_order_data_type_3', 'mrhe_handle_product_purchase_order', 10, 2);
function mrhe_handle_product_purchase_order($__data, $post_data) {
    $post_id = !empty($post_data['post_id']) ? (int) $post_data['post_id'] : 0;
    if (!$post_id) {
        zib_send_json_error('商品数据获取错误');
    }

    $post = get_post($post_id);
    if (empty($post->post_author)) {
        zib_send_json_error('商品数据获取错误');
    }

    $user_id = get_current_user_id();
    if (!$user_id && !_pz('pay_no_logged_in', true)) {
        zib_send_json_error('请先登录');
    }

    // 直接读取posts_zibpay数据
    $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    if (empty($pay_mate) || !is_array($pay_mate)) {
        zib_send_json_error('商品数据获取错误');
    }
    
    if ($pay_mate['pay_type'] !== '3') {
        zib_send_json_error('商品数据获取错误');
    }

    $__data['post_author'] = $post->post_author;
    $__data['order_type'] = '3'; // 产品购买
    $__data['product_id'] = !empty($pay_mate['product_id']) ? $pay_mate['product_id'] : '';
    $__data['order_price'] = isset($pay_mate['pay_price']) ? round((float) $pay_mate['pay_price'], 2) : 0;

    if ($user_id) {
        // 会员价格
        $vip_level = zib_get_user_vip_level($user_id);
        if ($vip_level && _pz('pay_user_vip_' . $vip_level . '_s', true) && isset($pay_mate['vip_' . $vip_level . '_price'])) {
            $vip_price = round((float) $pay_mate['vip_' . $vip_level . '_price'], 2);
            
            // 如果会员价格为0，设置为免费
            if ($vip_price <= 0) {
                $__data['order_price'] = 0;
            } else {
                // 会员金额和正常金额取更小值
                $__data['order_price'] = $vip_price < $__data['order_price'] ? $vip_price : $__data['order_price'];
            }
        }
    }

    // 设置订单数据
    $__data['mate_order_data'] = array(
        'product_id' => $post->ID,
        'product_title' => $post->post_title,
        'prices' => array(),
        'count' => 1,
    );

    return $__data;
}

/**
 * 产品购买Meta Box配置
 */
function mrhe_product_purchase_meta() {
    $meta = array(
        'title'     => '产品购买设置',
        'post_type' => array('page'),
        'data_type' => 'serialize',
    );
    return apply_filters('mrhe_add_product_purchase_meta_box_meta', $meta);
}

/**
 * 产品购买字段配置
 */
function mrhe_product_purchase_fields() {
    $fields = array(
        array(
            'title'   => '启用产品购买',
            'id'      => 'pay_type',
            'type'    => 'radio',
            'default' => 'no',
            'inline'  => true,
            'options' => array(
                'no' => __('关闭', 'zib_language'),
                '3'  => __('产品购买', 'zib_language'),
            ),
            'desc'    => '启用此页面的产品购买功能',
        ),
        array(
            'dependency' => array('pay_type', '==', '3'),
            'title'      => '产品ID',
            'id'         => 'product_id',
            'type'       => 'text',
            'default'    => '',
            'desc'       => '此处设置产品ID',
        ),
        // 购买权限
        array(
            'dependency' => array('pay_type', '==', '3'),
            'title'      => '购买权限',
            'id'         => 'pay_limit',
            'type'       => 'radio',
            'default'    => '0',
            'desc'       => '设置此处可实现会员专享资源功能，配置对应的会员价格可实现专享免费资源<br/><i class="fa fa-fw fa-info-circle fa-fw"></i> 使用此功能，请确保付费会员功能已开启，否则会出错',
            'options'    => array(
                '0' => __('所有人可购买', 'zib_language'),
                '1' => _pz('pay_user_vip_1_name') . '及以上会员可购买',
                '2' => '仅' . _pz('pay_user_vip_2_name') . '可购买',
            ),
        ),
        // 支付类型
        array(
            'dependency' => array('pay_type', '==', '3'),
            'title'      => '支付类型',
            'id'         => 'pay_modo',
            'type'       => 'radio',
            'default'    => _pz('pay_modo_default', 0),
            'options'    => array(
                '0'      => __('普通商品（金钱购买）', 'zib_language'),
                'points' => __('积分商品（积分购买，依赖于用户积分功能）', 'zib_language'),
            ),
        ),
        // 积分售价
        array(
            'dependency' => array(
                array('pay_type', '==', '3'),
                array('pay_modo', '==', 'points'),
            ),
            'id'         => 'points_price',
            'title'      => '积分售价',
            'class'      => '',
            'default'    => _pz('points_price_default'),
            'type'       => 'number',
            'unit'       => '积分',
        ),
        array(
            'dependency' => array(
                array('pay_type', '==', '3'),
                array('pay_modo', '==', 'points'),
            ),
            'title'      => _pz('pay_user_vip_1_name') . '积分售价',
            'id'         => 'vip_1_points',
            'class'      => 'compact',
            'subtitle'   => '填0则为' . _pz('pay_user_vip_1_name') . '免费',
            'default'    => _pz('vip_1_points_default'),
            'type'       => 'number',
            'unit'       => '积分',
        ),
        array(
            'dependency' => array(
                array('pay_type', '==', '3'),
                array('pay_modo', '==', 'points'),
            ),
            'title'      => _pz('pay_user_vip_2_name') . '积分售价',
            'id'         => 'vip_2_points',
            'class'      => 'compact',
            'subtitle'   => '填0则为' . _pz('pay_user_vip_2_name') . '免费',
            'default'    => _pz('vip_2_points_default'),
            'type'       => 'number',
            'unit'       => '积分',
            'desc'       => '会员价格不能高于售价',
        ),
        // 执行价
        array(
            'dependency' => array(
                array('pay_type', '==', '3'),
                array('pay_modo', '!=', 'points'),
            ),
            'id'         => 'pay_price',
            'title'      => '执行价',
            'default'    => _pz('pay_price_default', '0.01'),
            'type'       => 'number',
            'unit'       => '元',
        ),
        // 原价
        array(
            'dependency' => array(
                array('pay_type', '==', '3'),
                array('pay_modo', '!=', 'points'),
            ),
            'id'         => 'pay_original_price',
            'title'      => '原价',
            'class'      => 'compact',
            'subtitle'   => '显示在执行价格前面，并划掉',
            'default'    => _pz('pay_original_price_default'),
            'type'       => 'number',
            'unit'       => '元',
        ),
        // 促销标签
        array(
            'dependency' => array(
                array('pay_type', '==', '3'),
                array('pay_original_price', '!=', ''),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => ' ',
            'subtitle'   => '促销标签',
            'class'      => 'compact',
            'id'         => 'promotion_tag',
            'sanitize'   => false,
            'type'       => 'textarea',
            'default'    => _pz('pay_promotion_tag_default', '<i class="fa fa-fw fa-bolt"></i> 限时特惠'),
            'attributes' => array(
                'rows' => 1,
            ),
        ),
        // 黄金会员价格
        array(
            'dependency' => array(
                array('pay_type', '==', '3'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => _pz('pay_user_vip_1_name') . '价格',
            'id'         => 'vip_1_price',
            'class'      => 'compact',
            'subtitle'   => '填0则为' . _pz('pay_user_vip_1_name') . '免费',
            'default'    => _pz('vip_1_price_default'),
            'type'       => 'number',
            'unit'       => '元',
        ),
        // 钻石会员价格
        array(
            'dependency' => array(
                array('pay_type', '==', '3'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => _pz('pay_user_vip_2_name') . '价格',
            'id'         => 'vip_2_price',
            'class'      => 'compact',
            'subtitle'   => '填0则为' . _pz('pay_user_vip_2_name') . '免费',
            'default'    => _pz('vip_2_price_default'),
            'type'       => 'number',
            'unit'       => '元',
            'desc'       => '会员价格不能高于执行价',
        ),
        // 推广折扣
        array(
            'dependency' => array(
                array('pay_type', '==', '3'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => '推广折扣',
            'id'         => 'pay_rebate_discount',
            'class'      => 'compact',
            'subtitle'   => __('通过推广链接购买，额外优惠的金额', 'zib_language'),
            'desc'       => __('1.需开启推广返佣功能  2.注意此金不能超过实际购买价，避免出现负数', 'zib_language'),
            'default'    => _pz('pay_rebate_discount', 0),
            'type'       => 'number',
            'unit'       => '元',
        ),
        // 销量浮动
        array(
            'dependency' => array('pay_type', '==', '3'),
            'title'      => '销量浮动',
            'id'         => 'pay_cuont',
            'subtitle'   => __('为真实销量增加或减少的数量', 'zib_language'),
            'default'    => zib_get_mt_rand_number(_pz('pay_cuont_default', 0)),
            'type'       => 'number',
        ),
        // 优惠码
        array(
            'dependency' => array('pay_type', '==', '3'),
            'title'      => '优惠码',
            'label'      => __('允许使用优惠码', 'zib_language'),
            'desc'       => __('开启后请在<a target="_blank" href="' . admin_url('admin.php?page=zibpay_coupon_page') . '">优惠码管理</a>中添加优惠码<div class="c-yellow">由于php特性，此功能有一定风险可能会出现优惠码被多个订单同时使用的情况，建议仅在特殊活动时，短时间开启</div>', 'zib_language'),
            'id'         => 'coupon_s',
            'default'    => false,
            'type'       => 'switcher',
        ),
        array(
            'dependency' => array('coupon_s|pay_type', '!=|!=', '|3'),
            'title'      => ' ',
            'subtitle'   => __('优惠券默认说明', 'zib_language'),
            'class'      => 'compact',
            'id'         => 'coupon_desc',
            'default'    => '',
            'desc'       => '用户填写优惠码时，展示的提醒内容，支持html代码，请注意代码规范',
            'sanitize'   => false,
            'type'       => 'textarea',
            'attributes' => array(
                'rows' => 1,
            ),
        ),
        // 资源下载
        array(
            'dependency' => array('pay_type', '==', '3'),
            'id'         => 'pay_download',
            'type'       => 'group',
            'button_title' => '添加资源',
            'title'      => '资源下载',
            'sanitize'   => false,
            'class'      => 'pay-download-group',
            'fields'     => array(
                array(
                    'title'       => __('下载地址', 'zib_language'),
                    'id'          => 'link',
                    'placeholder' => '上传文件或输入下载地址',
                    'preview'     => false,
                    'type'        => 'upload',
                    'desc'        => '部分云盘的分享链接直接粘贴，可自动识别链接及提取码',
                ),
                array(
                    'title'      => '资源备注',
                    'desc'       => '按钮旁边的额外内容，例如：提取密码、解压密码等',
                    'id'         => 'more',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                ),
                array(
                    'title'    => '点击复制',
                    'subtitle' => '复制的名称',
                    'class'    => 'compact',
                    'default'  => '',
                    'id'       => 'copy_key',
                    'type'     => 'text',
                ),
                array(
                    'title'    => ' ',
                    'subtitle' => '复制的内容',
                    'class'    => 'compact',
                    'default'  => '',
                    'id'       => 'copy_val',
                    'type'     => 'text',
                    'desc'     => '为"资源备注"按钮添加点击复制功能，请设置复制名称和复制内容',
                ),
                array(
                    'dependency'   => array('link', '!=', ''),
                    'id'           => 'icon',
                    'type'         => 'icon',
                    'title'        => '自定义按钮图标',
                    'button_title' => '选择图标',
                    'default'      => 'fa fa-download',
                ),
                array(
                    'dependency' => array('link', '!=', ''),
                    'title'      => '自定义按钮文案',
                    'class'      => 'compact',
                    'id'         => 'name',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                ),
                array(
                    'dependency' => array('link', '!=', ''),
                    'title'      => '自定义按钮颜色',
                    'class'      => 'compact skin-color',
                    'desc'       => '按钮图标、文案、颜色默认均会自动获取，建议为空即可。<br>上方的按钮图标为主题自带的fontawesome 4图标库，如需添加其它图标可采用HTML代码，请注意代码规范！<br><a href="https://www.zibll.com/547.html" target="_blank">使用阿里巴巴Iconfont图标详细图文教程</a>',
                    'id'         => 'class',
                    'type'       => 'palette',
                    'options'    => CFS_Module::zib_palette(),
                ),
            ),
        ),
        // 商品信息
        array(
            'dependency' => array('pay_type', '==', '3'),
            'title'      => '商品信息',
            'subtitle'   => __('商品标题', 'zib_language'),
            'desc'       => __('（可选）如需要单独显示商品标题请填写此项', 'zib_language'),
            'id'         => 'pay_title',
            'type'       => 'text',
        ),
        array(
            'dependency' => array('pay_type', '==', '3'),
            'title'      => ' ',
            'subtitle'   => __('商品简介', 'zib_language'),
            'id'         => 'pay_doc',
            'desc'       => __('（可选）如需要单独显示商品介绍请填写此项', 'zib_language'),
            'class'      => 'compact',
            'sanitize'   => false,
            'type'       => 'textarea',
            'attributes' => array(
                'rows' => 1,
            ),
        ),
        array(
            'dependency' => array('pay_type', '==', '3'),
            'title'      => ' ',
            'subtitle'   => '更多详情',
            'id'         => 'pay_details',
            'desc'       => __('（可选）显示在商品卡片下方的内容（支持HTML代码，请注意代码规范）', 'zib_language'),
            'class'      => 'compact',
            'default'    => _pz('pay_details_default'),
            'sanitize'   => false,
            'type'       => 'textarea',
            'attributes' => array(
                'rows' => 3,
            ),
        ),
        array(
            'dependency' => array('pay_type', '==', '3'),
            'title'      => ' ',
            'subtitle'   => '额外隐藏内容',
            'id'         => 'pay_extra_hide',
            'desc'       => __('（可选）付费后显示的额外隐藏内容（支持HTML代码，请注意代码规范）', 'zib_language'),
            'class'      => 'compact',
            'default'    => _pz('pay_extra_hide_default'),
            'sanitize'   => false,
            'type'       => 'textarea',
            'attributes' => array(
                'rows' => 3,
            ),
        ),
        // 域名授权配置
        array(
            'dependency' => array('pay_type', '==', '3'),
            'title'      => '域名授权',
            'subtitle'   => '启用域名授权',
            'id'         => 'auth_enabled',
            'desc'       => '启用后，用户购买此产品后需要进行域名授权管理',
            'default'    => false,
            'type'       => 'switcher',
        ),
        array(
            'dependency' => array('auth_enabled|pay_type', '==|==', 'true|3'),
            'title'      => ' ',
            'subtitle'   => '最大授权域名数',
            'class'      => 'compact',
            'id'         => 'auth_max_domains',
            'type'       => 'number',
            'default'    => 3,
            'unit'       => '个域名',
            'desc'       => '用户最多可以绑定多少个域名，0为无限制',
        ),
    );
    return apply_filters('mrhe_add_product_purchase_meta_box_args', $fields);
}


