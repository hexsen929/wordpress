<?php

/*
 * @Author        : ZbApe
 * @Url           : ZbApe.com
 * @Date          : 2022-10-02 18:08:54
 * @LastEditTime: 2022-08-01 18:08:54
 * @Email         : dhp110623@163.com
 * @Project       : ZbApe
 * @Description   : 一款极其优雅的mrhe子主题
 * @Read me       : 感谢您使用ZbApe，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//载入文件
$require_once = array(
	'mrhecode/options/options-module',
	'mrhecode/options/options',
	'mrhecode/attachment/options',             // 前端附件管理设置（提前加载以显示在顶部）
	'mrhecode/options/action',
	'mrhecode/widgets/widgets-index',
	'mrhecode/function-mrhe',
	'mrhecode/user-center-functions',
	// 'mrhecode/auth-management-functions', // 已移到插件 mrhe-auth-server
	'mrhecode/attachment/attachment-manager',  // 前端附件管理功能
	'mrhecode/attachment/user',                // 用户中心和作者相册
);

foreach ($require_once as $require) {
	$path = $require . '.php';
    require get_theme_file_path($path);
}