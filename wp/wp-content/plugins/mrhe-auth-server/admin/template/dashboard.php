<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|仪表盘模板
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="dashboard-container">
    <h1 class="wp-heading-inline">授权管理概览</h1>
    <hr class="wp-header-end">

    <!-- 统计卡片 -->
    <div class="stats-grid">
        <el-card class="stats-card" v-for="(stat, index) in stats" :key="index">
            <div class="stats-content-simple">
                <div class="stats-number" :style="{ color: colors[index % colors.length] }">
                    {{ stat.value }}
                </div>
                <div class="stats-label">{{ stat.label }}</div>
            </div>
        </el-card>
    </div>

    <!-- 快速操作 -->
    <el-card class="quick-actions">
        <template #header>
            <div class="card-header">
                <span>快速操作</span>
            </div>
        </template>
        <div class="quick-actions-content">
            <el-button type="success" @click="menuGo('/list')" size="large">查看授权列表</el-button>
            <el-button type="info" @click="refreshStats" size="large">刷新统计数据</el-button>
        </div>
    </el-card>

    <!-- 最近授权记录 -->
    <el-card class="recent-auth">
        <template #header>
            <div class="card-header">
                <span>最近授权记录</span>
                <el-button type="text" @click="menuGo('/list')">查看全部 →</el-button>
            </div>
        </template>
        <el-table :data="recentAuths" style="width: 100%" v-loading="loading">
            <el-table-column prop="user_login" label="用户" width="150">
                <template #default="scope">
                    <div class="user-info">
                        <el-avatar :size="32" :src="scope.row.avatar">{{ scope.row.user_login.charAt(0) }}</el-avatar>
                        <div class="user-details">
                            <div class="user-name">{{ scope.row.user_login }}</div>
                            <div class="user-email">{{ scope.row.user_email }}</div>
                        </div>
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="product_name" label="产品" width="180">
                <template #default="scope">
                    <el-tooltip :content="scope.row.product_name" placement="top">
                        <span class="text-ellipsis">{{ scope.row.product_name }}</span>
                    </el-tooltip>
                </template>
            </el-table-column>
            <el-table-column prop="domain_count" label="域名使用" width="120" align="center">
                <template #default="scope">
                    <el-tag :type="scope.row.domain_count >= scope.row.aut_max_url ? 'danger' : 'success'" size="small">
                        {{ scope.row.domain_count }}/{{ scope.row.aut_max_url }}
                    </el-tag>
                </template>
            </el-table-column>
            <el-table-column prop="is_authorized" label="状态" width="100" align="center">
                <template #default="scope">
                    <el-tag v-if="scope.row.is_banned" type="warning" size="small">已封禁</el-tag>
                    <el-tag v-else :type="scope.row.is_authorized ? 'success' : 'danger'" size="small">
                        {{ status_name[scope.row.is_authorized] }}
                    </el-tag>
                </template>
            </el-table-column>
            <el-table-column prop="created_at" label="创建时间" width="160">
                <template #default="scope">
                    {{ formatDate(scope.row.created_at) }}
                </template>
            </el-table-column>
            <el-table-column label="操作" width="120" fixed="right">
                <template #default="scope">
                    <el-button type="text" size="small" @click="viewAuth(scope.row)">查看</el-button>
                    <el-button type="text" size="small" @click="editAuth(scope.row)">编辑</el-button>
                </template>
            </el-table-column>
        </el-table>
    </el-card>
</div>
