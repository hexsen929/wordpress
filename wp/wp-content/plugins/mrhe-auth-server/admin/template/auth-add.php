<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|添加授权模板
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="auth-add-container">
    <div class="page-header">
        <div class="header-left">
            <h1 class="wp-heading-inline">添加授权</h1>
        </div>
        <div class="header-right">
            <el-button @click="menuGo('/list')">
                <el-icon><Back /></el-icon>
                返回列表
            </el-button>
        </div>
    </div>
    <hr class="wp-header-end">

    <el-card class="add-form-card">
        <template #header>
            <div class="card-header">
                <el-icon><User /></el-icon>
                <span>基本信息</span>
            </div>
        </template>

        <el-form
            ref="addFormRef"
            :model="addForm"
            :rules="addFormRules"
            label-width="120px"
            @submit.prevent="submitAddForm">
            
            <el-row :gutter="20">
                <el-col :span="12">
                    <el-form-item label="选择用户" prop="user_id" required>
                        <el-select
                            v-model="addForm.user_id"
                            placeholder="请选择用户"
                            filterable
                            remote
                            :remote-method="searchUsers"
                            :loading="userLoading"
                            style="width: 100%">
                            <el-option
                                v-for="user in userOptions"
                                :key="user.ID"
                                :label="user.user_login + ' (' + user.user_email + ')'"
                                :value="user.ID">
                                <div class="user-option">
                                    <el-avatar :size="24" :src="user.avatar">{{ user.user_login.charAt(0) }}</el-avatar>
                                    <div class="user-info">
                                        <div class="user-name">{{ user.user_login }}</div>
                                        <div class="user-email">{{ user.user_email }}</div>
                                    </div>
                                </div>
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                
                <el-col :span="12">
                    <el-form-item label="选择产品" prop="post_id" required>
                        <el-select
                            v-model="addForm.post_id"
                            placeholder="请选择产品"
                            @change="handleProductChange"
                            style="width: 100%">
                            <el-option
                                v-for="product in products"
                                :key="product.ID"
                                :label="product.post_title"
                                :value="product.ID">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>

            <el-row :gutter="20">
                <el-col :span="12">
                    <el-form-item label="域名配额" prop="aut_max_url">
                        <el-input-number
                            v-model="addForm.aut_max_url"
                            :min="1"
                            :max="100"
                            controls-position="right"
                            style="width: 100%;">
                        </el-input-number>
                        <div class="form-tip">用户最多可绑定的域名数量</div>
                    </el-form-item>
                </el-col>

                <el-col :span="12">
                    <el-form-item label="授权状态" prop="is_authorized">
                        <el-radio-group v-model="addForm.is_authorized">
                            <el-radio :label="true">已授权</el-radio>
                            <el-radio :label="false">未授权</el-radio>
                        </el-radio-group>
                        <div class="form-tip">设置初始授权状态</div>
                    </el-form-item>
                </el-col>
            </el-row>

            <el-form-item label="授权码" prop="auth_code">
                <el-input
                    v-model="addForm.auth_code"
                    placeholder="留空将自动生成32位随机授权码"
                    readonly>
                    <template #append>
                        <el-button @click="generateAuthCode" icon="Refresh">生成授权码</el-button>
                    </template>
                </el-input>
                <div class="form-tip">授权码用于客户端验证，建议使用自动生成</div>
            </el-form-item>

            <el-divider content-position="left">
                <el-icon><Link /></el-icon>
                域名管理（可选）
            </el-divider>

            <el-form-item label="预设域名" prop="domains">
                <div class="domain-management">
                    <el-input
                        v-model="domainInput"
                        placeholder="输入域名后点击添加或按回车键，例如：example.com"
                        @keyup.enter="addDomain">
                        <template #prepend>
                            <el-icon><Link /></el-icon>
                        </template>
                        <template #append>
                            <el-button @click="addDomain" icon="Plus">添加</el-button>
                        </template>
                    </el-input>

                    <div class="domain-list" v-if="addForm.domains.length > 0" style="margin-top: 12px;">
                        <el-tag
                            v-for="(domain, index) in addForm.domains"
                            :key="index"
                            closable
                            @close="removeDomain(index)"
                            size="large"
                            class="domain-tag">
                            {{ domain }}
                        </el-tag>
                    </div>
                    <el-empty v-else description="暂未添加域名" :image-size="60" style="padding: 20px 0;" />
                </div>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" size="large" @click="submitAddForm" :loading="submitLoading">
                    <el-icon><Check /></el-icon>
                    创建授权
                </el-button>
                <el-button size="large" @click="resetAddForm">
                    <el-icon><Refresh /></el-icon>
                    重置表单
                </el-button>
            </el-form-item>
        </el-form>
    </el-card>

</div>
