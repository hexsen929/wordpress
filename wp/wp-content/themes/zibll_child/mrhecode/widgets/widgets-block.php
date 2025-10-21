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

add_action('widgets_init', 'widget_block_top');
function widget_block_top()
{
    register_widget('widget_ui_block');
}

/////用户列表-----
class widget_ui_block extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_name'       => 'mrhe-用户封禁情况',
            'w_id'        => 'widget_ui_block',
			'description' => '显示网站用户被封禁情况，建议侧边栏显示',
            'classname'   => '',
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }
    public function widget($args, $instance)
    {
        extract($args);
        echo "<style>
            .font-hidden{overflow: hidden;white-space: nowrap;text-overflow: ellipsis;}
            .xy_tc_mb-5{text-align:center;margin-bottom: -5px;}
            .xy-widget-title {position: relative;padding: 0 0 14px 20px !important;margin-top: 5px;border-bottom: 1px solid #f5f6f7;font-size: 16px;font-weight: 600;color: #18191a;width: 100%;}
            .xy-widget-title:after {left: 12px !important;}
            .xy-widget-title:before, .xy-widget-title:after {position: absolute;transform: skewX(-15deg);content: '';width: 3px;height: 16px;background: var(--theme-color);top: 0;left: 4px;bottom: 10%;transition: .4s;}
        </style>";
        $defaults = array(
            'title'        => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url' => '',
            'in_affix'     => '',
            'include'      => '',
            'exclude'      => '',
            'hide_box'     => false,
            'number'       => 10,
            'orderby'      => 'block',
            'order'        => 'DESC',
        );
        $instance   = wp_parse_args((array) $instance, $defaults);
        $mini_title = $instance['mini_title'];
        if ($mini_title) {
            $mini_title = '<small class="ml10">' . $mini_title . '</small>';
        }
        $title    = $instance['title'];
        $more_but = '';
        if ($instance['more_but'] && $instance['more_but_url']) {
            $more_but = '<div class="pull-right em09 mt3"><a href="' . $instance['more_but_url'] . '" class="muted-2-color">' . $instance['more_but'] . '</a></div>';
        }
        $mini_title .= $more_but;
        /*if ($title) {
            $title = '<div class="box-body notop"><div class="title-theme">' . $title . $mini_title . '</div></div>';
        }*/
        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';

        $class = !$instance['hide_box'] ? ' zib-widget' : '';
        echo '<div' . $in_affix . ' class="theme-box">';

        echo '<div class="user_lists' . $class . '">';
        $shu = $instance['number'];
        $orderby = $instance['orderby'];
        $isorderby = '拉黑名单';
        $order = $instance['order'];
         global $wpdb;
        $used =  $wpdb->get_results("SELECT meta_value,user_id,meta_key FROM {$wpdb->usermeta} WHERE meta_key='$orderby' AND meta_value !='0' ORDER BY user_id $order LIMIT $shu");
        
        if ($title) {
            if($instance['mini_title']){
                $xbt = '<small class="ml10">'.$instance['mini_title'].'</small>';
            }
            echo '<h2 class="xy-widget-title font-hidden">'.$instance['title'].''.$xbt.'</h2>';
        }
        
        foreach ($used as $k){
            $user = zib_get_user_name_link($k->user_id);
            $is_ban = zib_get_user_ban_info($k->user_id);
            $userimg = zib_get_avatar_box($k->user_id, 'avatar-img forum-avatar');
            
            $time = $is_ban['time'];
            $datetime    = date("jS H:i",strtotime("$time"));;
            
            $html = '<div class="posts-mini border-bottom  relative ">';
            $html .= $userimg;
            $html .='<div class="posts-mini-con em09 ml10 flex xx jsb"> <p class="flex jsb">';
            $html .= '<span class="flex1 flex"><name class="inflex ac relative-h"><a href="' . zib_get_user_home_url($k->user_id) . '">' . $user . '</a></name><span class="flex0 icon-spot muted-3-color" title="封禁时间：' . ($is_ban['banned_time'] ? $is_ban['banned_time'] : '永久') . '">' . $datetime. '</span></p>';
            $html .= '<div class="flex jsb muted-2-color font-hidden">'.$is_ban['reason'].'</div></div> ';
            $html .= '<div class="flex jsb xx text-right flex0 ml10"><div class="text-right em5"><span class="badge pull-right cursor" title="封禁状态">' . (2 == $is_ban['type'] ? '禁封中' : '小黑屋') . '</span></div></div></div>';
            echo $html;
        }
        if($used){
            echo '<div class="mt10 xy_tc_mb-5"><a href="'.$instance['more_but_url'].'" class="muted-2-color c-blue">'.$instance['more_but'].'</a></div>';
        }else{
            echo zib_get_ajax_null('暂无'.$instance['title'].'成员', 0);
        }
        echo '</div></div>';
    }

    public function form($instance)
    {
        $defaults = array(
            'title'        => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url' => '',
            'in_affix'     => '',
            'include'      => '',
            'exclude'      => '',
            'number'       => 10,
            'hide_box'     => '',
            'orderby'      => 'points',
            'order'        => 'DESC',
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        $page_input[] = array(
            'name'  => __('标题：', 'zib_language'),
            'id'    => $this->get_field_name('title'),
            'std'   => $instance['title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('副标题：', 'zib_language'),
            'id'    => $this->get_field_name('mini_title'),
            'std'   => $instance['mini_title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('跳转页面->文案：', 'zib_language'),
            'id'    => $this->get_field_name('more_but'),
            'std'   => $instance['more_but'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('跳转页面->链接：', 'zib_language'),
            'id'    => $this->get_field_name('more_but_url'),
            'std'   => $instance['more_but_url'],
            'desc'  => '设置为任意链接',
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            //    'name'  => __('显示背景盒子', 'zib_language'),
            'id'    => $this->get_field_name('hide_box'),
            'std'   => $instance['hide_box'],
            'desc'  => '不显示背景盒子',
            'style' => 'margin: 10px auto;',
            'type'  => 'checkbox',
        );

        echo zib_edit_input_construct($page_input);
        ?>

		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on');?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> 侧栏随动（仅在侧边栏有效）
			</label>
		</p>
		<p>
			<label>
				显示数目：
				<input style="width:100%;" id="<?php echo $this->get_field_id('number');
        ?>" name="<?php echo $this->get_field_name('number');
        ?>" type="number" value="<?php echo $instance['number'];
        ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				显示类型：
				<select style="width:100%;" id="<?php echo $this->get_field_id('orderby');
        ?>" name="<?php echo $this->get_field_name('orderby');
        ?>">
					<option value="banned" <?php selected('banned', $instance['orderby']);
        ?>>黑名单</option>
				</select>
			</label>
		</p>
		<p>
			<label>
				排序顺序：
				<select style="width:100%;" id="<?php echo $this->get_field_id('order');
        ?>" name="<?php echo $this->get_field_name('order');
        ?>">
					<option value="ASC" <?php selected('ASC', $instance['order']);
        ?>>升序</option>
					<option value="DESC" <?php selected('DESC', $instance['order']);
        ?>>降序</option>
				</select>
			</label>
		</p>

	<?php
}
}