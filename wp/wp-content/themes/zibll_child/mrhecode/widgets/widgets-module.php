<?php

/*
 * @Author        : mrhe
 * @Url           : mrhe.com
 * @Date          : 2022-10-02 18:08:54
 * @LastEditTime: 2022-08-01 18:08:54
 * @Email         : dhp110623@163.com
 * @Project       : mrhe
 * @Description   : 一款极其优雅的Zibll子主题
 * @Read me       : 感谢您使用mrhe，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if( class_exists( 'CSF' ) ) {
	CSF::createWidget( 'mrhe_widget_module', array(
			'title'       => 'mrhe-小模块',
			'classname'   => 'widget_module',
			'description' => '在添加的位置输出侧边栏小模块',
			'fields'      => array(
				array(
					'title'    => '小模块标题',
					'id'       => 'widget_module_title',
					'default'  => '何先生',
					'type'     => 'text',
				),
				array(
					'title'     => ' ',
					'subtitle'    => '小模块简介',
					'id'       => 'widget_module_introduce',
					'default'  => '专注于开发的平台',
					'type'     => 'text',
				),
				array(
					'title'     => ' ',
					'subtitle'    => '小模块链接',
					'id'       => 'widget_module_url',
					'default'  => 'https://hexsen.com',
					'type'     => 'text',
				),
			)
		)
	);

//获取当前小工具信息
	if( ! function_exists( 'mrhe_widget_module' ) ) {
		function mrhe_widget_module( $args, $instance ) { echo $args['before_widget'];?>
			<!--当前小工具核心内容代码-->
			<a class="ads" href="<?php echo $instance['widget_module_url']; ?>" target="<?php echo $instance['widget_module_title']; ?>"  style="border-radius:5px;">
				<h4><?php echo $instance['widget_module_title']; ?></h4>
				<h5><?php echo $instance['widget_module_introduce']; ?></h5>
				<span class="ads-btn ads-btn-outline">立即进入</span></a>
			<style>
                .ads{display:block; padding:40px 15px; text-align:center; color:#fff!important; background:#ff5719; background-image:-webkit-linear-gradient(135deg,#bbafe7,#5368d9); background-image:linear-gradient(135deg,#bbafe7,#5368d9)}.ads h4{margin:0; font-size:22px; font-weight:bold}.ads h5{margin:10px 0 0; font-size:14px; font-weight:bold}.ads.ads-btn{margin-top:20px; font-weight:bold}.ads.ads-btn:hover{color:#ff5719}.ads-btn{display:inline-block; font-weight:normal; margin-top:10px; color:#666; text-align:center; vertical-align:top; user-select:none; border:none; padding:0 36px; line-height:38px; font-size:14px; border-radius:10px; outline:0; -webkit-transition:all 0.3s ease-in-out; -moz-transition:all 0.3s ease-in-out; transition:all 0.3s ease-in-out}.ads-btn:hover,.btn:focus,.btn.focus{outline:0; text-decoration:none}.ads-btn:active,.btn.active{outline:0; box-shadow:inset 0 3px 5px rgba(0,0,0,0.125)}.ads-btn-outline{line-height:36px; color:#fff; background-color:transparent; border:1px solid#fff}.ads-btn-outline:hover,.btn-outline:focus,.btn-outline.focus{color:#343a3c; background-color:#fff}
			</style>
			<?php echo $args['after_widget'];}}}?>