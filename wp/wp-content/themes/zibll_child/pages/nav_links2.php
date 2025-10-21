<?php

/**
 * Template name: 网址导航
 */

get_header();

$link_cat_ids = _mrhe('navpage_cats_links')['navpage_cats'];
?>
<!-- <style>
#navs {
    min-height: 600px;
    margin-bottom:20px
}

#navs .focus {
    background-color: var(--main-bg-color);
    border-radius: 6px;
    margin-bottom: 20px;
    margin-left: 200px;
    text-align: center;
    padding:60px 20px
}

#navs .focus h1 {
    font-size:25px
}

#navs .focus .note {
    margin-top: 5px;
    color:var(--muted-2-color)
}

#navs nav {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 180px;
    border-radius: 6px;
    overflow:hidden;
    z-index: 0
}

#navs nav ul {
    width: 180px;
    background-color: var(--main-bg-color);
    overflow-y:auto
}

#navs nav ul.-roll-top {
    position: fixed;
    bottom: 0;
    border-radius:0
}

#navs nav ul.-roll-bottom {
    position: fixed;
    top: 0;
    border-radius:0
}

#navs nav ul::-webkit-scrollbar {
    width: 8px;
    height:8px
}

#navs nav ul::-webkit-scrollbar-thumb {
    background-color:rgba(0, 0, 0, .1)
}

#navs nav ul::-webkit-scrollbar-thumb:hover {
    background-color:rgba(0, 0, 0, .3)
}

#navs nav a {
    display: block;
    padding:11px 22px
}

#navs nav a:hover {
    color:var(--focus-color)
}

#navs nav .active a {
    color: #fff;
    background-color:var(--theme-color)
}

#navs .items {
    margin-left:200px
}

#navs .item {
    overflow: hidden;
    margin-top:20px
}

#navs .item h3 {
    position: relative;
    display: inline-block;
    font-size: 17px;
    margin: 0;
    padding-left:17px
}

#navs .item h3::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 0;
    margin-top: -8px;
    height: 16px;
    width: 4px;
    border-radius: 10px;
    background-color:var(--theme-color)
}

#navs .item ul {
    margin-right:-1.3%
}

#navs .item li {
    float: left;
    width: 32.0333333333%;
    margin-right: 1.3%;
    margin-top:1.3%
}

#navs .item li a {
    display: block;
    padding: 15px 20px;
    background-color: var(--main-bg-color);
    border-radius:6px
}

#navs .item li a:hover {
    color: #fff;
    background-color:var(--theme-color)
}

#navs .item li a:hover p {
    color:rgba(255, 255, 255, .8)
}

#navs .item li a:hover i {
    color:#fff
}

#navs .item li a:hover img {
    opacity:1
}

#navs .item li strong {
    display: block;
    font-size:15px
}

#navs .item li i {
    float: right;
    font-size: 15px;
    color: #ccc;

}

#navs .item li p {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
    overflow: hidden;
    margin: 5px 0 0;
    color: #999;
    height:63px
}

#navs .item li img {
    float: right;
    max-width: 56px;
    max-height: 56px;
    border-radius: 6px;
    margin-left: 15px;
    margin-top: 10px;
    opacity:.8
}

@media (max-width: 768px) {
    #navs .item ul {
        margin-right:-3%
    }

    #navs .item li {
        width: 47%;
        margin-right: 3%;
        margin-top:3%
    }
}

@media (max-width: 600px) {
    #navs nav {
        display:none
    }

    #navs .focus {
        margin-left: 0;
        border-radius: 0;
        padding: 40px 15px;
        margin-bottom:15px
    }

    #navs .focus h1 {
        font-size:20px
    }

    #navs .items {
        margin-left: 0;
        padding:0 15px
    }

    #navs .item ul {
        margin-right:-2%
    }

    #navs .item li {
        width: 48%;
        margin-right: 2%;
        margin-top:2%
    }

    #navs .item h3 {
        font-size: 14px;
        padding-left:15px
    }

    #navs .item h3::before {
        height: 14px;
        margin-top:-7px
    }

    #navs .item li a {
        padding:12px 15px
    }

    #navs .item li strong {
        font-size: 14px;
        font-weight:400
    }

    #navs .item li i {
        font-size:14px
    }

    #navs .item li p {
        font-size: 12px;
        height:55px
    }

    #navs .item li img {
        max-width: 50px;
        max-height: 50px;
        margin-left: 10px;
        margin-top:8px
    }
}
</style>-->
<section class="container" id="navs">
	<nav>
		<ul></ul>
	</nav>
	<div class="focus">
		<h1><?php the_title(); ?></h1>
		<div class="note"><?php echo _mrhe('navpage_cats_links')['navpage_desc'] ? _mrhe('navpage_cats_links')['navpage_desc'] : '这里显示的是网址导航的一句话描述...' ?></div>
	</div>

	<div class="items">
		<?php
		$cats = get_terms(
			array(
				'taxonomy'     => 'link_category',
				'name__like'   => '',
				'include'      => $link_cat_ids,
				'exclude'      => '',
				'orderby'      => 'include',
				'order'        => 'ASC',
				'hierarchical' => 0,
			)
		);

		$html = '';
		foreach ($cats as $cat) {
			$html .= '<div class="item" data-slug="' . $cat->slug . '">';
			$html .= '<h3>' . $cat->name . '</h3>';
			$html .= '<ul>';
			$list = get_bookmarks(array(
				'category' => $cat->term_id,
				'orderby'  => 'rating',
				'order'    => 'DESC',
			));
			foreach ($list as $one) {
				$html .= '<li><a target="_blank" href="' . $one->link_url . '" ref="nofollow">';
				$html .= '<i class="fa fa-chevron-right"></i>';
				$html .= '<strong>' . $one->link_name . '</strong>';
				//if ($one->link_image) $html .= '<img src="' . $one->link_image . '" alt="' . $one->link_name . '">';
				$html .= '<img src="' . ($one->link_image ? $one->link_image : 'https://api.hexsen.com/api/favicon.php?url='.$one->link_url) . '" alt="' . $one->link_name . '">';
				if ($one->link_description) $html .= '<p>' . $one->link_description . '</p>';
				$html .= '</a></li>';
			}
			$html .= '</ul>';
			$html .= '</div>';
		}

		if (!empty($html)) {
			echo $html;
		} else {
			echo '请在后台-链接中添加链接和链接分类用于展示在这里。';
		}
		?>
	</div>
</section>
<!-- <script id='key_from_target_script'>
// function scrollTo(name, add, speed) {
//     speed || (speed = 300),
//     name ? $(name).length > 0 && $("html,body").animate({
//         scrollTop: $(name).offset().top + (add || 0)
//     }, speed) : $("html,body").animate({
//         scrollTop: 0
//     }, speed)
// }

// if ($('body').hasClass("page-template-pagesnav_links2-php")) {
//     function initializeNavigation() {
//         var side = $("#navs");
//         if (!side.length || $('body').hasClass("is-phone"))
//             return;
//         var headerHeight = $('.header').outerHeight(true) - 20; // 获取header元素的高度
//         var default_hash = location.hash,
//             titles = "";
//         $("#navs .items h3").each((function() {
//             titles += '<li><a href="#' + $(this).parent().data("slug") + '">' + $(this).text() + "</a></li>"
//         })),
//         $("#navs nav ul").html(titles),
//         $("#navs .items a").attr("target", "_blank");
//         var bh = 0;
//         if (default_hash) {
//             var index = $('#navs .items .item[data-slug="' + default_hash.split("#")[1] + '"]').index();
//             $("#navs nav li:eq(" + index + ")").addClass("active"),
//             scrollTo("#navs .items .item:eq(" + index + ")", -20),
//             bh = $("#navs nav li:eq(" + index + ")").offset().top
//         }
//         var menu = $("#navs nav ul");
//         function navinit(side, bh, menu) {
//             var doc = $(document),
//                 dh = doc.height(),
//                 rt = doc.scrollTop(),
//                 st = side.offset().top,
//                 b = $(".footer").outerHeight(true);
//             $(".branding").length && (b += $(".branding").outerHeight(true));
//             var wh = $(window).height();
//             $("#navs .items .item").each((function(index) {
//                 if (rt < $(this).offset().top)
//                     return location.hash = $(this).data("slug"), menu.find("li").eq(index).addClass("active").siblings().removeClass("active"), !1
//             })),
//             rt > st - headerHeight ? rt + wh > dh - b - bh ? (bh = 0, menu.removeClass("-roll-top").addClass("-roll-bottom").css("top", headerHeight).css("bottom", -1 * (dh - b - bh - rt - wh))) : menu.removeClass("-roll-bottom").addClass("-roll-top").css("bottom", "").css("top", headerHeight) : menu.removeClass("-roll-top -roll-bottom").css("top", "").css("bottom", "")
//         }
//         navinit(side, bh, menu),
//         $(window).scroll((function() {
//             navinit(side, bh, menu)
//         })),
//         $("#navs nav a").each((function(e) {
//             $(this).click((function() {
//                 scrollTo("#navs .items .item:eq(" + $(this).parent().index() + ")", 6)
//             }))
//         }))
//     }
    
//     initializeNavigation();
// }
</script>-->
<?php

get_footer();
