<?php

/**
 * Template name: 会员购买页面
 * Description: 会员购买
 */

global $post;
get_header();
$randomNumber = rand(1, 10);
?>
<style>
    .product-container {
        margin-top: 0px;
        opacity: 1 !important;
        transition: opacity .5s;
    }
</style>
<link rel='dns-prefetch' href='//s.w.org' rel="nofollow" />
<link rel='stylesheet' id='pay_page-css' href='<?php echo get_stylesheet_directory_uri(); ?>/mrhecode/css/pay-page.css?ver=2' type='text/css' media='all' />
<main role="main">
    <div class="product-container" style="opacity: 0;">
			<div class="countdown-activity flex jc">
				<div class="activity-content flex ac">
					<div class="activity-title mr10">元旦快乐·喜迎龙年</div>
					<div class="activity-desc">79.9限时特价 最后一波</div>
				</div>
				<div class="countdown-content badg jb-yellow radius flex jc">
					<div class="countdown-desc">活动倒计时</div>
					<div class="countdown-time flex0 em09-sm badg jb-vip2 radius" data-over-text="活动已结束" data-newtime="2023-12-31 10:14:27" data-countdown="01/10/2025 23:59:59"></div>
				</div>
			</div>
        <div class="product-box-mrhe relative">
            <div class="product-background absolute" style="background:var(--linear-bg-<?php echo $randomNumber; ?>); "></div>
            <div class="product-row relative">
                <div class="payrow-6 payrow-left">
                    <?php
                        if (function_exists('dynamic_sidebar')) {
                            dynamic_sidebar('product_vip_sidebar');
                        }
                    ?>
                    <div class="more-but text-center">
                        <a target="_blank" class="but hollow c-white" href="https://hexsen.com/wordpress-zibll-sub-theme-of-sub-theme-officially-opened-for-sale.html">更新日志</a>
                        <a target="_blank" class="but hollow c-white" href="https://hexsen.com/wordpress-zibll-sub-theme-of-sub-theme-officially-opened-for-sale.html">主题文档</a>
                    </div>
                </div><div class="payrow-6 payrow-right">
                    <div class="pay-content">
                        <div class="product-header">淘金案例库</div>
                        <div class="product-doc">
                            <div>加入淘金案例库，一起同步学习搞钱！<br>接收项目线索，流量情报，学习营销干货以及赚钱案例等……</div>
                            <div class="mt6"><a href="javascript:;" rel="external nofollow" class="but c-white font-bold px12 p2-10" style="overflow: hidden; position: relative;"> 原价990元，直降720元<i class="ml6 fa fa-angle-right em12"></i></a>
                            </div>
                        </div>
                        <div class="px13">
                            <b class="em3x"><span class="pay-mark">￥</span>270</b>
                            <div class="inline-block ml10 text-left">
                                <badge><i class="fa fa-fw fa-bolt"></i> 最后一波</badge><br>
                                <span class="original-price" title="原价 990"><span class="pay-mark">￥</span>990</span>
                            </div>
                        </div>
                        <div class="badg badg-lg payvip-icon">限时优惠 仅限100个名额</div>
                        <form class="pay-form">
                        <div class="product-pay">
                            <?php
                                $user_id      = get_current_user_id();

                                function mrhe_get_header_payvip_icon($user_id = 0)
                                {
                                    if (!$user_id || (!_pz('pay_user_vip_1_s', true) && !_pz('pay_user_vip_2_s', true))) {
                                        return;
                                    }
                                
                                    $vip_level = zib_get_user_vip_level($user_id);
                                
                                    if ($vip_level) {
                                        return '<a target="_blank" href="'.zib_get_user_center_url().'" class="but jb-yellow"><i class="fa fa-angle-right" aria-hidden="true"></i>会员中心</a><span class="but c-white opacity5" style="overflow: hidden; position: relative;">已购买</span>';
                                    } elseif (_pz('nav_user_pay_vip', true)) {
                                        $button = '<a class="pay-vip but jb-blue signin-loader padding-lg" href="javascript:;">开通会员</a>';
                                        return $button;
                                    }
                                    return '';
                                }
                                    echo !$user_id ? '<a href="javascript:;" class="but jb-blue signin-loader padding-lg"><i class="fa fa-angle-right" aria-hidden="true"></i>登录购买</a>' : mrhe_get_header_payvip_icon($user_id); //购买按钮
                            ?>
                        </div>
                        </form>
                        <div class="relative product-details box-body radius8">
                            <ul>
                                <li>会员6大权益：</li>
                                <li>1.全站90%资源免费下载</li>
                                <li>2.赚钱笔记专栏免费阅读</li>
                                <li>3.专属会员微信交流群</li>
                                <li>4.有问必答365天提问答疑</li>
                                <li>5.收费项目享有折扣优惠</li>
                                <li>6.一次付费有效期12个月</li>
                            </ul>
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