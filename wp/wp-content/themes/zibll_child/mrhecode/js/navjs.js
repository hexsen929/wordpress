function scrollTo(name, add, speed) {
    speed = speed || 300;
    if (name && $(name).length > 0) {
        $("html,body").animate({
            scrollTop: $(name).offset().top + (add || 0)
        }, speed);
    } else {
        $("html,body").animate({
            scrollTop: 0
        }, speed);
    }
}

if ($('body').hasClass("page-template-pagesnav_links2-php")) {
    function initializeNavigation() {
        var side = $("#navs");
        if (!side.length || $('body').hasClass("is-phone")) return;
        
        var headerHeight = $('.header').outerHeight(true) - 20;
        var scrollOffset = 6; // 只用于h标签的额外偏移
        var default_hash = location.hash;
        var titles = "";
        
        // 生成菜单
        $("#navs .items h3").each(function() {
            titles += '<li><a href="#' + $(this).parent().data("slug") + '">' + $(this).text() + "</a></li>";
        });
        $("#navs nav ul").html(titles);
        $("#navs .items a").attr("target", "_blank");

        // 处理默认 hash
        if (default_hash) {
            var index = $('#navs .items .item[data-slug="' + default_hash.split("#")[1] + '"]').index();
            $("#navs nav li:eq(" + index + ")").addClass("active");
            scrollTo("#navs .items .item:eq(" + index + ")", -(headerHeight + scrollOffset));
        }

        var menu = $("#navs nav ul");

        // 简化的菜单固定逻辑
        function navinit() {
            var st = side.offset().top;
            var scrollTop = $(window).scrollTop();
            headerHeight = $('.header').outerHeight(true) - 20; // 动态更新 header 高度

            if (scrollTop > st - headerHeight) {
                menu.addClass('-roll-top').css('top', headerHeight + 'px'); // 保持原有距离
            } else {
                menu.removeClass('-roll-top').css('top', '');
            }

            // 更新激活状态
            var $items = $("#navs .items .item");
            var windowHeight = $(window).height();
            var documentHeight = $(document).height();
            var lastItemActivated = false;

            // 检查是否滚动到底部
            if ((scrollTop + windowHeight) >= documentHeight - 50) { // 50px的容差值
                // 如果到达底部，激活最后一个项目
                menu.find('li').removeClass('active')
                    .eq($items.length - 1).addClass('active');
                lastItemActivated = true;
            }

            // 如果没有在底部激活最后一项，则正常检查每个项目
            if (!lastItemActivated) {
                $items.each(function(index) {
                    if (scrollTop + headerHeight + 50 >= $(this).offset().top) {
                        menu.find('li').removeClass('active')
                            .eq(index).addClass('active');
                    }
                });
            }
        }

        // 滚动处理
        $(window).on('scroll', function() {
            navinit();
        });

        // 点击处理
        $("#navs nav a").on('click', function(e) {
            e.preventDefault();
            var index = $(this).parent().index();
            menu.find('li').removeClass('active');
            $(this).parent().addClass('active');
            scrollTo("#navs .items .item:eq(" + index + ")", -(headerHeight + scrollOffset));
        });
    }
    
    $(document).ready(function() {
        setTimeout(initializeNavigation, 100);
    });
}