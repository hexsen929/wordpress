<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|导航头部模板
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="menu-container">
    <el-menu
        :default-active="$route.path"
        class="menu-tabs"
        mode="horizontal"
        @select="menuGo">
        <el-menu-item index="/">概览</el-menu-item>
        <el-menu-item index="/list">授权列表</el-menu-item>
        <el-menu-item index="/add">添加授权</el-menu-item>
    </el-menu>
</div>
