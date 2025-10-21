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

//载入文件
$functions = array(
	'block',
	'module',
    'integral',
	'websitestat',
);

foreach ($functions as $function) {
	$path = 'mrhecode/widgets/widgets-' . $function . '.php';
	require get_theme_file_path($path);
}