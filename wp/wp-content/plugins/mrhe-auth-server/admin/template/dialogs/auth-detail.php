<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|查看详情对话框
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<el-dialog
    v-model="detailDialogVisible"
    title="授权详情"
    width="900px"
    :close-on-click-modal="false">

    <div v-if="currentAuth" class="auth-detail-content">
        <!-- 基本信息 -->
        <el-card class="detail-section" shadow="never">
            <template #header>
                <div class="section-header">
                    <el-icon><InfoFilled /></el-icon>
                    <span>基本信息</span>
                </div>
            </template>

            <el-descriptions :column="2" border size="default">
                <el-descriptions-item label="授权ID" label-align="right">
                    <el-tag type="info" size="large">#{{ currentAuth.id }}</el-tag>
                </el-descriptions-item>
                <el-descriptions-item label="授权状态" label-align="right">
                    <el-tag v-if="currentAuth.is_banned" type="warning" size="large">已封禁</el-tag>
                    <el-tag v-else :type="currentAuth.is_authorized ? 'success' : 'danger'" size="large">
                        {{ status_name[currentAuth.is_authorized] }}
                    </el-tag>
                </el-descriptions-item>
                <el-descriptions-item label="授权码" :span="2" label-align="right">
                    <div class="auth-code-container">
                        <code class="auth-code">{{ currentAuth.auth_code }}</code>
                        <el-button type="primary" link size="small" @click="copyAuthCode">
                            <el-icon><CopyDocument /></el-icon>
                            复制
                        </el-button>
                    </div>
                </el-descriptions-item>
                <el-descriptions-item label="创建时间" label-align="right">
                    {{ formatDate(currentAuth.created_at) }}
                </el-descriptions-item>
                <el-descriptions-item label="更新时间" label-align="right">
                    {{ formatDate(currentAuth.updated_at) }}
                </el-descriptions-item>
            </el-descriptions>
        </el-card>

        <!-- 用户和产品信息 -->
        <el-row :gutter="20">
            <el-col :span="12">
                <el-card class="detail-section" shadow="never">
                    <template #header>
                        <div class="section-header">
                            <el-icon><User /></el-icon>
                            <span>用户信息</span>
                        </div>
                    </template>

                    <div class="user-detail">
                        <el-avatar :size="64" :src="currentAuth.avatar">{{ currentAuth.user_login.charAt(0) }}</el-avatar>
                        <div class="user-info">
                            <h3>{{ currentAuth.user_login }}</h3>
                            <p>{{ currentAuth.user_email }}</p>
                        </div>
                    </div>
                </el-card>
            </el-col>

            <el-col :span="12">
                <el-card class="detail-section" shadow="never">
                    <template #header>
                        <div class="section-header">
                            <el-icon><Box /></el-icon>
                            <span>产品信息</span>
                        </div>
                    </template>

                    <div class="product-detail">
                        <h3>{{ currentAuth.product_name }}</h3>
                        <el-descriptions :column="1" size="small">
                            <el-descriptions-item label="产品ID">{{ currentAuth.post_id }}</el-descriptions-item>
                            <el-descriptions-item label="产品标识">{{ currentAuth.product_id }}</el-descriptions-item>
                        </el-descriptions>
                    </div>
                </el-card>
            </el-col>
        </el-row>

        <!-- 域名信息 -->
        <el-card class="detail-section" shadow="never">
            <template #header>
                <div class="section-header">
                    <el-icon><Link /></el-icon>
                    <span>域名配额</span>
                    <el-tag :type="currentAuth.domain_count >= currentAuth.aut_max_url ? 'danger' : 'success'" style="margin-left: auto;">
                        {{ currentAuth.domain_count }} / {{ currentAuth.aut_max_url }}
                    </el-tag>
                </div>
            </template>

            <div class="domain-detail">
                <el-progress
                    :percentage="Math.round((currentAuth.domain_count / currentAuth.aut_max_url) * 100)"
                    :color="currentAuth.domain_count >= currentAuth.aut_max_url ? '#f56c6c' : '#67c23a'"
                    :stroke-width="10"
                    style="margin-bottom: 20px;">
                </el-progress>

                <div v-if="currentAuth.domain_list && currentAuth.domain_list.length > 0">
                    <el-divider content-position="left">已绑定域名</el-divider>
                    <div class="domain-tags">
                        <el-tag
                            v-for="(domain, index) in currentAuth.domain_list"
                            :key="index"
                            size="large"
                            class="domain-tag">
                            <el-icon><Link /></el-icon>
                            {{ domain }}
                        </el-tag>
                    </div>
                </div>
                <el-empty v-else description="暂无绑定域名" :image-size="60" />
            </div>
        </el-card>
    </div>

    <template #footer>
        <div class="dialog-footer">
            <el-button size="large" @click="detailDialogVisible = false">关闭</el-button>
            <el-button type="primary" size="large" @click="editAuth(currentAuth); detailDialogVisible = false;">
                <el-icon><Edit /></el-icon>
                编辑授权
            </el-button>
        </div>
    </template>
</el-dialog>
