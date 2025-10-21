<?php 
/**
 * Template name: Navs
 * Description:   A site navigation page
 */
 get_header();
?>

<div class="site">
    <div class="">
<style>
.dark-theme #navs .item li{border-bottom: 15px solid #232425;background-color: #232425;}
.dark-theme .pageheader h1,.dark-theme .pageheader,.dark-theme #navs nav a,.dark-theme #navs nav a:hover,.dark-theme #navs h2{color:#cecece}
.dark-theme .pageheader,.dark-theme #navs nav,.dark-theme #navs h2{background-color: #033d5f}
.page-template-navs-php .pageheader{margin:0}
.pageheader{overflow:hidden;margin-bottom:0px;margin-top: -20px;padding:25px 20px;background-color:#45b6f7;color:#fff}
.container:before{display:table;content:" "}
.bdsharebuttonbox{overflow:hidden;height:24px;vertical-align:top;line-height:24px}
.custom{margin:20px 20px 0 0}
.pageheader .sitego a{color:#fff;text-decoration:none;font-size:18px}
.pageheader h1{float:none;margin:0;margin:0;color:#fff;font-size:30px;font-size:30px}
.pageheader .note{margin-top:5px}
#navs{color:#aaa}
.container{position:relative;margin:0 auto;padding:0;max-width:75pc}
#navs nav{position:absolute;top:0;left:0;width:140px;height:100%;background-color:#45b6f7}
#navs .items{margin-left:10pc}
#navs nav ul{top:0;margin:0;padding:0;width:140px;list-style:none}
#navs nav a{display:block;padding:10px 20px;border-bottom:1px solid rgba(0,0,0,.08);color:#fff;text-decoration:none}
#navs nav .active a,#navs nav a:hover{color:#fff;font-weight:700}
#navs .item{overflow:hidden;margin:20px 0 30px}
#navs .item h2{margin:0;padding:11px 15px;width:140px;font-size:18px}
#navs h2{margin:0;padding:10px;background-color:#45b6f7;color:#fff;font-weight:400;font-size:20px}
#navs .item ul{padding-left:0;margin: 0;margin-right: -1%;}
#navs .item li{float:left;overflow:hidden;margin-top:1%;margin-right:1%;padding:15px;width:19%;height:100px;border-bottom:15px solid #fff;background-color:#fff;font-size:9pt}
#navs .item li a{display:inline-block;margin-bottom:0px;border-bottom:2px solid transparent;color:#45b6f7;font-weight:700;font-size:14px}
@media (max-width:1280px){#navs .items{margin-right:10px}
}
@media (max-width:1024px){#navs .item{margin-top:10px}
#navs .items{margin:0}
#navs nav{display:none}
#navs .item li{width:24%}
}
@media (max-width:640px){#navs .item li{width:32.333333%}
#navs .item h2{width:auto;text-align:center}
}
@media (max-width:320px){#navs .item li{width:49%}
}
#siteform{display:none}
.fa-paper-plane-o{margin-right:5px}
.xoxo li a img {
    display: inline;
    width: 20px;
    height: 20px;
    margin-right: 8px;
}
</style>
<div class="pageheader">
	<div class="container">
		<div class="custom pull-right">
			<p>此处可设置自定义栏目</p>
		</div>
		<h1><?php the_title(); ?></h1>
		<div class="note"><?php echo _mrhe('navpage_cats_links')['navpage_desc'] ? _mrhe('navpage_cats_links')['navpage_desc'] : '这里显示的是网址导航的一句话描述...' ?></div>
	</div>
</div>

<section class="container" id="navs">
	<nav>
		<ul></ul>
	</nav>
	<div class="items">
<?php 

// $link_cat_ids = array();
// if( _mrhe('navpage_cats_links')['navpage_cats'] ){
// 	foreach (_mrhe('navpage_cats_links')['navpage_cats'] as $key => $value) {
// 		if( $value ) $link_cat_ids[] = $key;
// 	}
// }

// $link_cat_ids = implode(',', $link_cat_ids);
$link_cat_ids = _mrhe('navpage_cats_links')['navpage_cats'];
?>
		<?php 
        	$html = wp_list_bookmarks(array(
        		'category'         => $link_cat_ids,
        		'category_orderby' => 'include',
        		'category_order'   => 'ASC',
        		'orderby'          => 'rand',
        // 		'order'            => 'DESC',
        		'echo'             => false,
        		'show_description' => true,
        		'between'          => '<br>',
        		'title_li'         => __(''),
        		'category_before'  => '<div class="item">',
        		'category_after'   => '</div>'
        	));
			if( !empty($link_cat_ids) ){
				echo $html;
			}else{
				echo '请在后台-链接中添加链接和链接分类用于展示在这里。';
			}
		?>
	</div>
</section>
<script>
if( $('body').hasClass('page-template-pagesnavs_links-php') ){
    
$(".xoxo li a").each(function(e){
	 $(this).prepend("<img src=https://api.hexsen.com/api/favicon.php?url="+this.href.replace(/^(http:\/\/[^\/]+).*$/, '$1').replace( 'http://', '' )+">");
});
	
function scrollTo(name, add, speed) {
    /*if (!speed) speed = 300;*/
	speed = speed || 300;
    if (!name) {
        $('html,body').animate({
            scrollTop: 0
        }, speed)
    } else {
        if ($(name).length > 0) {
            $('html,body').animate({
                scrollTop: $(name).offset().top + (add || -86)
            }, speed)
        }
    }
}
$(function(){  
	/*获取要定位元素距离浏览器顶部的距离  */
	var navH = $("#navs nav ul").offset().top;  
	/*滚动条事件  */
	$(window).scroll(function(){  
	/*获取滚动条的滑动距离  */
	var scroH = $(this).scrollTop();  
	/*滚动条的滑动距离大于等于定位元素距离浏览器顶部的距离，就固定，反之就不固定  */
	if(scroH>=navH){  
		$("#navs nav ul").css({"position":"fixed","top":75});  
	}else if(scroH<navH){  
		$("#navs nav ul").css({"position":"sticky"});  
	}  
	})  
});
    var titles = '',
        i = 0;
    $('#navs .items h2').each(function(){
        titles += '<li><a href="javascript:void('+i+')">'+$(this).text()+'</a></li>';
        i++
    });
    $('#navs nav ul').html( titles );

    $('#navs .items a').attr('target', '_blank');
    // if( location.hash ){
    //     var index = location.hash.split('#')[1];
    //     $('#navs nav li:eq('+index+')').addClass('active');
    //     $('#navs nav .item:eq('+index+')').addClass('active');
    //     scrollTo( '#navs .items .item:eq('+index+')' );
    // };
    $('#navs nav a').each(function(e){
        $(this).click(function(){
            scrollTo( '#navs .items .item:eq('+$(this).parent().index()+')' );
            $(this).parent().addClass('active').siblings().removeClass('active');
        })
    })
}
</script>
<?php

get_footer();