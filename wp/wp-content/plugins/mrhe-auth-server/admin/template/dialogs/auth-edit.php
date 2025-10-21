<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|编辑对话框
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<el-dialog
    v-model="editDialogVisible"
    title="编辑授权"
    width="700px"
    top="8vh"
    :close-on-click-modal="false"
    :destroy-on-close="true">

    <el-form
        ref="editFormRef"
        :model="editForm"
        :rules="editFormRules"
        label-width="100px"
        @submit.prevent="submitEditForm">

        <el-row :gutter="20">
            <el-col :span="12">
                <el-form-item label="授权状态" prop="is_authorized">
                    <el-radio-group v-model="editForm.is_authorized">
                        <el-radio :label="true">已授权</el-radio>
                        <el-radio :label="false">未授权</el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-col>

            <el-col :span="12">
                <el-form-item label="域名配额" prop="aut_max_url">
                    <el-input-number
                        v-model="editForm.aut_max_url"
                        :min="1"
                        :max="100"
                        controls-position="right"
                        style="width: 100%;">
                    </el-input-number>
                </el-form-item>
            </el-col>
        </el-row>

        <el-form-item label="授权码" prop="auth_code">
            <el-input
                v-model="editForm.auth_code"
                placeholder="授权码"
                readonly>
                <template #append>
                    <el-button @click="regenerateAuthCode" icon="Refresh">重新生成</el-button>
                </template>
            </el-input>
        </el-form-item>

        <el-divider content-position="left">
            <el-icon><Link /></el-icon>
            域名管理
        </el-divider>

        <el-form-item label="域名列表" prop="domains">
            <div class="domain-management">
                <el-input
                    v-model="domainInput"
                    placeholder="输入域名后点击添加或按回车键"
                    @keyup.enter="addDomainToEdit">
                    <template #prepend>
                        <el-icon><Link /></el-icon>
                    </template>
                    <template #append>
                        <el-button @click="addDomainToEdit" icon="Plus">添加</el-button>
                    </template>
                </el-input>

                <div class="domain-list" v-if="editForm.domains.length > 0" style="margin-top: 12px;">
                    <el-tag
                        v-for="(domain, index) in editForm.domains"
                        :key="index"
                        closable
                        @close="removeDomainFromEdit(index)"
                        size="large"
                        class="domain-tag">
                        {{ domain }}
                    </el-tag>
                </div>
                <el-empty v-else description="暂无域名" :image-size="60" style="padding: 20px 0;" />
            </div>
        </el-form-item>
    </el-form>

    <template #footer>
        <div class="dialog-footer">
            <el-button size="large" @click="editDialogVisible = false">取消</el-button>
            <el-button type="primary" size="large" @click="submitEditForm" :loading="editLoading">
                <el-icon><Check /></el-icon>
                保存修改
            </el-button>
        </div>
    </template>
</el-dialog>
