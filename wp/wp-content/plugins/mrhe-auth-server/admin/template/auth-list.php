<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|授权列表模板
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="auth-list-container">
    <div class="page-header">
        <div class="header-left">
            <h1 class="wp-heading-inline">授权列表</h1>
            <span class="total-count">共 {{ pagination.total }} 条记录</span>
        </div>
        <div class="header-right">
            <el-button type="primary" @click="menuGo('/add')">添加授权</el-button>
        </div>
    </div>
    <hr class="wp-header-end">

    <!-- 搜索和筛选 -->
    <el-card class="search-filters">
        <el-form :model="searchForm" :inline="true" @submit.prevent="searchAuths">
            <el-form-item>
                <el-input
                    v-model="searchForm.keyword"
                    placeholder="搜索用户名、邮箱、授权码、产品名"
                    clearable
                    @clear="searchAuths"
                    @keyup.enter="searchAuths"
                    style="width: 320px;">
                </el-input>
            </el-form-item>
            <el-form-item>
                <el-select v-model="searchForm.status" placeholder="全部状态" clearable style="width: 130px;" @change="searchAuths">
                    <el-option label="全部状态" value=""></el-option>
                    <el-option label="已授权" value="1"></el-option>
                    <el-option label="未授权" value="0"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-select v-model="searchForm.product_id" placeholder="全部产品" clearable filterable style="width: 180px;" @change="searchAuths">
                    <el-option label="全部产品" value=""></el-option>
                    <el-option
                        v-for="product in products"
                        :key="product.ID"
                        :label="product.post_title"
                        :value="product.ID">
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="searchAuths" :loading="loading">
                    搜索
                </el-button>
                <el-button @click="resetSearch">
                    重置
                </el-button>
            </el-form-item>
        </el-form>
    </el-card>

    <!-- 批量操作 -->
    <el-card class="batch-actions" v-if="selectedAuths.length > 0">
        <div class="batch-content">
            <span>已选择 {{ selectedAuths.length }} 项</span>
            <el-button type="danger" @click="batchDelete" :loading="batchLoading">批量删除</el-button>
        </div>
    </el-card>

    <!-- 授权列表表格 -->
    <el-card class="auth-table-card">
        <el-table
            :data="authList"
            v-loading="loading"
            @selection-change="handleSelectionChange"
            style="width: 100%"
            :default-sort="{ prop: 'created_at', order: 'descending' }">
            
            <el-table-column type="selection" width="55" />
            
            <el-table-column prop="id" label="ID" width="80" sortable />
            
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
            
            <el-table-column prop="product_name" label="产品" width="200">
                <template #default="scope">
                    <div class="product-info">
                        <div class="product-name">{{ scope.row.product_name }}</div>
                        <div class="product-id">ID: {{ scope.row.product_id }}</div>
                    </div>
                </template>
            </el-table-column>
            
            <el-table-column prop="auth_code" label="授权码" width="150">
                <template #default="scope">
                    <el-tooltip :content="scope.row.auth_code" placement="top">
                        <code>{{ scope.row.auth_code.substring(0, 12) }}...</code>
                    </el-tooltip>
                </template>
            </el-table-column>
            
            <el-table-column prop="domains" label="域名" width="200">
                <template #default="scope">
                    <div class="domain-info">
                        <el-tag v-if="scope.row.domain_count > 0" type="info" size="small">
                            {{ scope.row.domain_count }} 个域名
                        </el-tag>
                        <el-tag v-else type="warning" size="small">未绑定域名</el-tag>
                        <div class="domain-list" v-if="scope.row.domain_list && scope.row.domain_list.length > 0">
                            <el-tag 
                                v-for="domain in scope.row.domain_list.slice(0, 2)" 
                                :key="domain" 
                                size="small" 
                                class="domain-tag">
                                {{ domain }}
                            </el-tag>
                            <el-tag v-if="scope.row.domain_list.length > 2" size="small" type="info">
                                +{{ scope.row.domain_list.length - 2 }}
                            </el-tag>
                        </div>
                    </div>
                </template>
            </el-table-column>
            
            <el-table-column prop="quota" label="配额" width="100" align="center">
                <template #default="scope">
                    <div class="quota-info">
                        <el-progress 
                            :percentage="Math.round((scope.row.domain_count / scope.row.aut_max_url) * 100)"
                            :color="scope.row.domain_count >= scope.row.aut_max_url ? '#f56c6c' : '#67c23a'"
                            :show-text="false"
                            :stroke-width="6">
                        </el-progress>
                        <div class="quota-text">{{ scope.row.domain_count }}/{{ scope.row.aut_max_url }}</div>
                    </div>
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
            
            <el-table-column prop="created_at" label="创建时间" width="150" sortable>
                <template #default="scope">
                    {{ formatDate(scope.row.created_at) }}
                </template>
            </el-table-column>
            
            <el-table-column label="操作" width="240" fixed="right">
                <template #default="scope">
                    <div class="action-buttons">
                        <el-button type="primary" link size="small" @click="viewAuth(scope.row)">
                            <el-icon><View /></el-icon>
                            查看
                        </el-button>
                        <el-button type="primary" link size="small" @click="editAuth(scope.row)">
                            <el-icon><Edit /></el-icon>
                            编辑
                        </el-button>
                        <el-dropdown trigger="click" @command="(cmd) => handleAction(cmd, scope.row)">
                            <el-button type="info" link size="small">
                                更多
                                <el-icon><ArrowDown /></el-icon>
                            </el-button>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item
                                        v-if="!scope.row.is_banned"
                                        command="ban"
                                        icon="Lock">
                                        封禁授权
                                    </el-dropdown-item>
                                    <el-dropdown-item
                                        v-if="scope.row.is_banned"
                                        command="unban"
                                        icon="Unlock">
                                        解封授权
                                    </el-dropdown-item>
                                    <el-dropdown-item
                                        command="delete"
                                        icon="Delete"
                                        divided>
                                        删除授权
                                    </el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                    </div>
                </template>
            </el-table-column>
        </el-table>

        <!-- 分页 -->
        <div class="pagination-container">
            <el-pagination
                v-model:current-page="pagination.page"
                v-model:page-size="pagination.per_page"
                :page-sizes="[10, 20, 50, 100]"
                :total="pagination.total"
                layout="total, sizes, prev, pager, next, jumper"
                @size-change="handleSizeChange"
                @current-change="handleCurrentChange">
            </el-pagination>
        </div>
    </el-card>
</div>
