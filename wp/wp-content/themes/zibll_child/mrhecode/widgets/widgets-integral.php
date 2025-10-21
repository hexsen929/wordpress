<?php
/*
 * @Author        : mrhe
 * @Url           : hexsen.com
 * @Date          : 2022-10-02 18:08:54
 * @LastEditTime: 2022-08-01 18:08:54
 * @Email         : dhp110623@163.com
 * @Project       : mrhe
 * @Description   : 一款极其优雅的Zibll子主题
 * @Read me       : 感谢您使用mrhe，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

add_action('widgets_init', 'widget_mrhe_top');
function widget_mrhe_top()
{
    register_widget('widget_ui_user_points');
}

/////用户列表-----
class widget_ui_user_points extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_user_points',
            'w_name'      => 'mrhe - 用户积分排行榜',
            'classname'   => '',
            'description' => '显示网站用户积分排行榜余额排行榜，建议侧边栏显示。',
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }
    public function widget($args, $instance)
    {
        extract($args);

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
            'orderby'      => 'points',
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
        if ($title) {
            $title = '<div class="box-body notop"><div class="title-theme">' . $title . $mini_title . '</div></div>';
        }
        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';

        $class = !$instance['hide_box'] ? ' zib-widget' : '';

        $shu = $instance['number'];
        $orderby = $instance['orderby'];
        $isorderby = $instance['orderby']=='points'?'积分':'余额';
        $order = $instance['order'];
        global $wpdb;
        $used =  $wpdb->get_results("SELECT meta_value,user_id,meta_key FROM {$wpdb->usermeta} WHERE meta_key='$orderby' ORDER BY --meta_value $order LIMIT $shu");
        arsort($used);

        $i = 0;
        foreach ($used as $k)
        {

            $i++;
            $user = zib_get_user_name_link($k->user_id);
            $userimg = zib_get_avatar_box($k->user_id, 'avatar-img forum-avatar');
            $tophead ='<div class="user-content"><div style="display: flex;position: relative;min-height: 120px;" class="user-avatar text-center">';
            if ($i == 1) {
                $userimg =  $userimg ;
                $top1 ='<svg t="1670077020112" class="avatar-badge ls-is-cached lazyloaded" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3755" width="128" height="128"><path d="M326.997333 187.733333h386.901334v147.968A90.965333 90.965333 0 0 1 622.762667 426.666667h-204.8a90.965333 90.965333 0 0 1-90.965334-90.965334V187.733333z" fill="#79DEB4" p-id="3756"></path><path d="M588.629333 367.274667V187.733333h-136.533333v179.541334a273.066667 273.066667 0 1 0 136.533333 0z" fill="#EDF4FF" p-id="3757"></path><path d="M520.362667 733.866667a413.696 413.696 0 0 1-273.066667-102.4 273.066667 273.066667 0 0 0 546.133333 0 413.525333 413.525333 0 0 1-273.066666 102.4z" fill="#D8E3F0" p-id="3758"></path><path d="M748.032 119.466667H292.864a34.133333 34.133333 0 0 0 0 68.266666h455.168a34.133333 34.133333 0 0 0 0-68.266666z" fill="#FFC670" p-id="3759"></path><path d="M520.362667 631.466667m-204.8 0a204.8 204.8 0 1 0 409.6 0 204.8 204.8 0 1 0-409.6 0Z" fill="#FFC670" p-id="3760"></path><path d="M520.362667 678.4a607.573333 607.573333 0 0 1-204.8-35.157333 204.8 204.8 0 0 0 408.405333 0 608.597333 608.597333 0 0 1-203.605333 35.157333z" fill="#FFA742" p-id="3761"></path><path d="M495.957333 512l-51.2 79.701333h51.2V750.933333h68.266667V512h-68.266667z" fill="#FFDEAD" p-id="3762"></path><path d="M954.709333 393.386667l-91.136-39.253334L844.117333 256a17.066667 17.066667 0 0 0-12.288-13.141333 17.066667 17.066667 0 0 0-17.066666 5.290666l-45.226667 51.2a17.066667 17.066667 0 0 0-4.266667 11.264v34.133334a105.984 105.984 0 0 1-10.581333 46.08 17.066667 17.066667 0 0 0 2.730667 18.944 325.461333 325.461333 0 0 1 73.386666 126.464 17.066667 17.066667 0 0 0 16.213334 12.117333h1.877333a17.066667 17.066667 0 0 0 15.189333-15.36l6.314667-60.586667 85.333333-48.469333a17.066667 17.066667 0 0 0 8.704-15.701333 17.066667 17.066667 0 0 0-9.728-14.848zM196.096 631.466667a327.168 327.168 0 0 1 10.581333-80.896 17.066667 17.066667 0 0 0-5.802666-17.066667l-38.058667-31.402667a17.066667 17.066667 0 0 0-17.066667-2.218666 17.066667 17.066667 0 0 0-9.898666 15.018666l-2.048 78.848-67.072 43.008a17.066667 17.066667 0 0 0-7.68 17.066667 17.066667 17.066667 0 0 0 11.264 13.994667l74.410666 26.453333 20.48 76.117333a17.066667 17.066667 0 0 0 13.141334 12.288h3.413333a17.066667 17.066667 0 0 0 13.482667-6.656l17.066666-21.674666a17.066667 17.066667 0 0 0 2.56-16.042667 324.266667 324.266667 0 0 1-18.773333-106.837333z" fill="#FFEFB0" p-id="3763"></path><path d="M703.829333 406.869333a107.861333 107.861333 0 0 0 27.136-71.168V204.8h17.066667a51.2 51.2 0 0 0 0-102.4H292.864a51.2 51.2 0 0 0 0 102.4h17.066667v130.901333a107.349333 107.349333 0 0 0 26.965333 71.168 290.133333 290.133333 0 1 0 366.933333 0zM292.864 170.666667a17.066667 17.066667 0 0 1 0-34.133334h455.168a17.066667 17.066667 0 0 1 0 34.133334z m403.968 34.133333v130.901333a73.045333 73.045333 0 0 1-21.162667 51.2 294.058667 294.058667 0 0 0-69.973333-32.597333V204.8z m-125.269333 0v141.312a295.936 295.936 0 0 0-51.2-4.778667 299.178667 299.178667 0 0 0-51.2 4.778667V204.8z m-136.533334 0v149.333333a290.133333 290.133333 0 0 0-69.802666 32.597334 73.045333 73.045333 0 0 1-21.162667-51.2V204.8z m85.333334 682.666667a256 256 0 1 1 256-256 256 256 0 0 1-256 256z" fill="#3D3D63" p-id="3764"></path><path d="M520.362667 409.6a221.866667 221.866667 0 1 0 221.866666 221.866667 221.866667 221.866667 0 0 0-221.866666-221.866667z m0 409.6a187.733333 187.733333 0 1 1 187.733333-187.733333 187.733333 187.733333 0 0 1-187.733333 187.733333z" fill="#3D3D63" p-id="3765"></path><path d="M564.224 494.933333h-68.266667a17.066667 17.066667 0 0 0-14.336 7.850667l-51.2 79.701333a17.066667 17.066667 0 0 0 14.336 26.282667h34.133334V750.933333a17.066667 17.066667 0 0 0 17.066666 17.066667h68.266667a17.066667 17.066667 0 0 0 17.066667-17.066667V512a17.066667 17.066667 0 0 0-17.066667-17.066667z m-17.066667 238.933334h-34.133333v-142.165334a17.066667 17.066667 0 0 0-17.066667-17.066666h-19.968L505.344 529.066667h41.813333zM954.709333 393.386667l-91.136-39.253334L844.117333 256a17.066667 17.066667 0 0 0-12.288-13.141333 17.066667 17.066667 0 0 0-17.066666 5.290666l-45.226667 51.2a17.066667 17.066667 0 0 0 25.6 22.528l22.528-25.6 14.336 72.192a17.066667 17.066667 0 0 0 9.898667 12.458667l68.266666 29.184-64.341333 35.84A17.066667 17.066667 0 0 0 836.266667 460.8l-6.485334 70.144a17.066667 17.066667 0 0 0 12.629334 18.090667 7.68 7.68 0 0 0 2.730666 0 17.066667 17.066667 0 0 0 18.602667-15.36l6.656-61.269334 85.333333-48.469333a17.066667 17.066667 0 0 0 8.704-15.701333 17.066667 17.066667 0 0 0-9.728-14.848zM189.610667 728.917333l-14.165334-52.906666a17.066667 17.066667 0 0 0-10.922666-11.605334l-50.176-17.066666 44.714666-29.184a17.066667 17.066667 0 0 0 7.68-13.824l1.365334-53.248 11.434666 9.216a17.066667 17.066667 0 0 0 21.333334-26.624l-38.058667-31.573334a17.066667 17.066667 0 0 0-17.066667-2.218666 17.066667 17.066667 0 0 0-9.898666 15.018666l-2.048 78.848-67.072 43.008a17.066667 17.066667 0 0 0-7.68 17.066667 17.066667 17.066667 0 0 0 11.264 13.994667l74.410666 26.453333 20.48 76.117333a17.066667 17.066667 0 0 0 13.141334 12.288 16.042667 16.042667 0 0 0 10.24-1.194666 17.066667 17.066667 0 0 0 6.656-5.12l17.066666-21.674667a17.066667 17.066667 0 0 0-22.186666-25.088z" fill="#3D3D63" p-id="3766"></path></svg>';
                $top ='<span style="left: 50%;transform: translateX(-50%);" class="avatar-img avatar-lg mb10">'.get_avatar($k->user_id, 100).$top1.'</span>';
            } elseif($i==2){
                $top2 ='<svg t="1670077086909" class="avatar-badge ls-is-cached lazyloaded" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6783" width="128" height="128"><path d="M114.2784 979.456a43.7248 43.7248 0 0 1 43.6224-43.7248h704.8192a43.7248 43.7248 0 0 1 0 87.3472H157.9008a43.6224 43.6224 0 0 1-43.6224-43.6224z" fill="#d81e06" opacity=".64" p-id="6784"></path><path d="M1005.1584 414.3104a43.9296 43.9296 0 0 0-2.8672-34.9184 8.0896 8.0896 0 0 0 0-0.9216 44.032 44.032 0 0 0-30.72-22.3232 43.6224 43.6224 0 0 0-39.424 13.6192l-169.984 120.5248L545.5872 247.808a61.44 61.44 0 0 0-4.608-4.4032 43.52 43.52 0 0 0-61.44 3.4816L260.7104 491.52 89.2928 369.2544a43.52 43.52 0 0 0-74.4448 22.3232 42.0864 42.0864 0 0 0 0.9216 20.48l92.16 424.6528a42.8032 42.8032 0 0 0 3.584 15.872 43.52 43.52 0 0 0 47.8208 27.136 35.1232 35.1232 0 0 0 4.5056-0.9216h704.7168a43.7248 43.7248 0 0 0 34.816-17.1008 44.6464 44.6464 0 0 0 9.1136-20.48l92.16-427.3152z" fill="#d81e06" p-id="6785"></path><path d="M510.2592 8.0896a133.12 133.12 0 1 0 133.12 133.12 133.12 133.12 0 0 0-133.12-133.12z" fill="#d81e06" p-id="6786"></path></svg>';
                $top.='     <span style="  position: absolute;left: 15%;transform: translateX(-15%);bottom: -20%;" class="avatar-img avatar-lg">'.get_avatar($k->user_id, 100).$top2.'</span>';
            }elseif($i==3){
                $top3 = '<svg t="1670077086909" class="avatar-badge ls-is-cached lazyloaded" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6783" width="128" height="128"><path d="M114.2784 979.456a43.7248 43.7248 0 0 1 43.6224-43.7248h704.8192a43.7248 43.7248 0 0 1 0 87.3472H157.9008a43.6224 43.6224 0 0 1-43.6224-43.6224z" fill="#8a8a8a" opacity=".64" p-id="6784"></path><path d="M1005.1584 414.3104a43.9296 43.9296 0 0 0-2.8672-34.9184 8.0896 8.0896 0 0 0 0-0.9216 44.032 44.032 0 0 0-30.72-22.3232 43.6224 43.6224 0 0 0-39.424 13.6192l-169.984 120.5248L545.5872 247.808a61.44 61.44 0 0 0-4.608-4.4032 43.52 43.52 0 0 0-61.44 3.4816L260.7104 491.52 89.2928 369.2544a43.52 43.52 0 0 0-74.4448 22.3232 42.0864 42.0864 0 0 0 0.9216 20.48l92.16 424.6528a42.8032 42.8032 0 0 0 3.584 15.872 43.52 43.52 0 0 0 47.8208 27.136 35.1232 35.1232 0 0 0 4.5056-0.9216h704.7168a43.7248 43.7248 0 0 0 34.816-17.1008 44.6464 44.6464 0 0 0 9.1136-20.48l92.16-427.3152z" fill="#8a8a8a" p-id="6785"></path><path d="M510.2592 8.0896a133.12 133.12 0 1 0 133.12 133.12 133.12 133.12 0 0 0-133.12-133.12z" fill="#8a8a8a" p-id="6786"></path></svg>';
                $top .= '<span style="position: absolute;left: 86%;transform: translateX(-85%);bottom: -20%;" class="avatar-img avatar-lg">'.get_avatar($k->user_id, 100).$top3.'</span>';
            }
            if ($i > 3) {
                $html = '<div class="posts-mini border-bottom  relative ">';
                $html .= $userimg;
                $html .='<div class="posts-mini-con em09 ml10 flex xx jsb"> <p class="flex jsb">';
                $html .= '<span class="flex1 flex"><name class="inflex ac relative-h">'.$user.'</name></p>';
                $html .= '<div class="mt6 flex jsb muted-2-color">'.$isorderby.':'. $k->meta_value.'</div></div> ';
                $html .= '<div style="background:url(https://www.bpwzj.com/wp-content/uploads/2022/12/16d81b4d4d212502.png);     width: 14%;
    background-size: contain; background-repeat: no-repeat;    margin: auto;"class="avatar-img forum-avatar"><div style="color: #fb1d1d;
    font-size: 22px;
    font-weight: bold;"class="text-center">'.$i.'</div></div>';
                $html .= '</div>';
                $com .= $html;
            }
        }
        echo '<div' . $in_affix . ' class="theme-box">'.$title.'<div style="
    background: url(https://www.bpwzj.com/wp-content/uploads/2022/12/21fcbab9e8223035.png);
    background-size: contain;
    background-repeat: no-repeat;
    " class="text-center  user_lists' . $class . '">'.$tophead.$top.'</div></div><div class="mb10" style="margin-top: 3rem;">'.$com.'</div></div></div>';

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
            'name'  => __('标题右侧按钮->文案：', 'zib_language'),
            'id'    => $this->get_field_name('more_but'),
            'std'   => $instance['more_but'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->链接：', 'zib_language'),
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
                    <option value="points" <?php selected('points', $instance['orderby']);
                    ?>>积分</option>
                    <option value="balance" <?php selected('balance', $instance['orderby']);
                    ?>>余额</option>
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