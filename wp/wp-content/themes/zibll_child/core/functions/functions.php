<?php

// 编写一个函数，用于输出彩色滚动条
// 这里用最简单的方式实现输出功能，挂钩在页面头部钩子wp_head，页面底部钩子为wp_footer
function child_demo_function() {
    // 这里的_child('child_demo_func')为获取子比主题后台菜单配置，其中的child_demo_func为后台菜单功能项的id
    if (_mrhe('child_demo_func')) {
        echo '<style>::-webkit-scrollbar{width:10px;height:1px;}::-webkit-scrollbar-thumb{background-color:#12b7f5;background-image:-webkit-linear-gradient(45deg,rgba(255,93,143,1) 25%,transparent 25%,transparent 50%,rgba(255,93,143,1) 50%,rgba(255,93,143,1) 75%,transparent 75%,transparent);}::-webkit-scrollbar-track{-webkit-box-shadow:inset 0 0 5px rgba(0,0,0,0.2);background:#f6f6f6;}</style>';
    }

}
add_action('wp_head','child_demo_function');