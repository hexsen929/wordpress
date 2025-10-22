<?php

/**
 * Template name: mrhe主题-推广产品页面
 * Description:   mrhe主题-推广产品页面
 */

// 内容购买页面
global $post;
get_header();
$header_style = zib_get_page_header_style();
$randomNumber = rand(1, 10);
// 直接读取posts_zibpay数据
$pay_mate = get_post_meta($post->ID, 'posts_zibpay', true);

?>
<style>
    .product-container {
        margin-top: 0px;
        opacity: 1 !important;
        transition: opacity .5s;
    }
</style>
<link rel='dns-prefetch' href='//s.w.org' rel="nofollow" />
<link rel='stylesheet' id='pay_page-css' href='<?php echo MRHE_AUTH_SERVER_URL; ?>templates/css/pay-page.css?ver=2' type='text/css' media='all' />
<main role="main">
    <div class="product-container" style="opacity: 0;">
			<div class="countdown-activity flex jc">
				<div class="activity-content flex ac">
					<div class="activity-title mr10">元旦快乐·喜迎龙年</div>
					<div class="activity-desc">79.9限时特价 最后一波</div>
				</div>
				<div class="countdown-content badg jb-yellow radius flex jc">
					<div class="countdown-desc">活动倒计时</div>
					<div class="countdown-time flex0 em09-sm badg jb-vip2 radius" data-over-text="活动已结束" data-newtime="2023-12-31 10:14:27" data-countdown="01/01/2024 23:59:59"></div>
				</div>
			</div>
        <div class="product-box-mrhe relative">
            <div class="product-background absolute" style="background:var(--linear-bg-<?php echo $randomNumber; ?>); "></div>
            <div class="product-row relative">
                <div class="payrow-6 payrow-left">
                    <?php
                        // 优先使用产品专属侧边栏（仿照 Zibll 官方风格）
                        $product_sidebar_id = 'product_sidebar_' . $post->ID;
                        if (function_exists('dynamic_sidebar')) {
                            if (!dynamic_sidebar($product_sidebar_id)) {
                                // 后备：使用通用产品侧边栏
                                dynamic_sidebar('product_sidebar');
                            }
                        }
                    ?>
                    <div class="more-but text-center">
                        <a target="_blank" class="but hollow c-white" href="https://hexsen.com/wordpress-zibll-sub-theme-of-sub-theme-officially-opened-for-sale.html">更新日志</a>
                        <a target="_blank" class="but hollow c-white" href="https://hexsen.com/wordpress-zibll-sub-theme-of-sub-theme-officially-opened-for-sale.html">主题文档</a>
                    </div>
                </div><div class="payrow-6 payrow-right">
                    <div class="pay-content">
                        <div class="product-header"><?php echo !empty($pay_mate['pay_title']) ? esc_html($pay_mate['pay_title']) : esc_html(get_the_title()); ?></div>
                        <div class="product-doc">
                            <?php echo wp_kses_post($pay_mate['pay_doc']); ?>
                        </div>
                        <?php echo zibpay_get_show_price($pay_mate, $post->ID, 'px13'); ?>
                        <form class="pay-form">
                        <div class="product-pay">
                            <!-- <div class="product-pay"><a href="javascript:;" class="but jb-red signin-loader"><i class="fa fa-angle-right" aria-hidden="true"></i>登录购买</a></div> -->
                            <?php
                                $user_id      = get_current_user_id();
                                $paid = zibpay_is_paid($post->ID,$user_id);
                                if(!$paid){
                                    echo !$user_id ? '<a href="javascript:;" class="but jb-blue signin-loader padding-lg"><i class="fa fa-angle-right" aria-hidden="true"></i>登录购买</a>' : zibpay_get_pay_form_but('', $post->ID); //购买按钮
                                }else{
                                    //查询用户的授权记录
                                    global $wpdb;
                                    $table_name = $wpdb->prefix . 'mrhe_theme_aut';
                                    
                                    $auth_code_check = $wpdb->get_var(
                                        $wpdb->prepare("SELECT auth_code FROM $table_name WHERE user_id = %d AND post_id = %d", $user_id, $post->ID)
                                    );
                                    
                                    if (!$auth_code_check) {
                                        //生成授权码（统一使用32位MD5）
                                        $authorizationCode = mrhe_generate_auth_code($user_id, $post->ID);
                                        
                                        //插入新记录到数据库
                                        $data = array(
                                            'user_id'      => $user_id,
                                            'post_id'      => $post->ID,
                                            'auth_code'    => $authorizationCode,
                                            'domain'       => serialize(array()),
                                            'is_authorized' => 1,
                                            'aut_max_url'  => 3
                                        );
                                        $wpdb->insert($table_name, $data);
                                        $auth_code_check = $authorizationCode;
                                    }
                                    $is_auth = zib_is_user_auth($user_id);
                                    $args = array(
                                        'user_id' => $user_id,
                                        'name' => 'mrhe主题正版用户',
                                        'desc' => 'mrhe主题正版用户 - 官方认证'
                                    );
                                    if(!$is_auth){
                                        zib_add_user_auth($args['user_id'], array(
                                            'name' => $args['name'],
                                            'desc' => $args['desc'],
                                        ));
                                    }
                                    $vip_level = zib_get_user_vip_level($user_id);
                                    $vip_data = array(
                                        'vip_level' => '2',
                                        'exp_date'  => 'Permanent',
                                        'type'      => 'mrhe主题赠送会员',
                                        'desc'      => 'mrhe主题赠送会员',
                                    );
                                    if(!$vip_level){
                                        zibpay_update_user_vip($user_id, $vip_data);
                                    }
                                    echo '<a target="_blank" href="'.home_url().'/user/product" class="but jb-yellow"><i class="fa fa-angle-right" aria-hidden="true"></i>管理授权</a><span class="but c-white opacity5" style="overflow: hidden; position: relative;">已购买</span>';
                                }
                            ?>
                        </div>
                        </form>
                        <div class="relative product-details box-body radius8">
                            <?php echo $pay_mate['pay_details']; ?>
                            <div class="abs-center right-top" data-toggle="tooltip" title="" data-original-title="分享">
                                <?php echo zib_get_post_share_btn($post->ID,'share-btn but cir p2-10 mt10 c-white',true); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="theme-box article-content">
            <article class="article wp-posts-content">
                <?php the_content(); ?>
            </article>
            <?php comments_template('/template/comments.php', true); ?>
        </div>
    </div>
</main>
<?php
get_footer();