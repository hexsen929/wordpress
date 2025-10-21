/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|Vue.js管理后台应用
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

(function() {
    'use strict';

    // 初始化函数
    function initApp() {
        // 检查必要的依赖
        if (typeof Vue === 'undefined' || typeof ElementPlus === 'undefined') {
            console.error('Vue.js 或 Element Plus 未加载');
            return;
        }

        // 获取配置数据
        const config = window.mrheAuthAdmin || {};
        
        // 创建 Vue 应用
        const { createApp } = Vue;
        const { createRouter, createWebHashHistory } = VueRouter;
        const { ElMessage, ElMessageBox, ElLoading } = ElementPlus;

        // 路由配置 - 简化配置，依靠 v-if 控制显示
        const routes = [
            { path: '/' },
            { path: '/list' },
            { path: '/add' }
        ];

        const router = createRouter({
            history: createWebHashHistory(),
            routes
        });

        // 创建 Vue 应用
        const app = createApp({
            data() {
                return {
                    // 配置
                    config: config.config || {},
                    status_name: config.status_name || {},
                    colors: config.colors || [],
                    
                    // 加载状态
                    loading: false,
                    userLoading: false,
                    orderLoading: false,
                    submitLoading: false,
                    editLoading: false,
                    batchLoading: false,
                    quickCreateLoading: false,
                    
                    // 统计数据
                    stats: [
                        { label: '总授权数', value: 0 },
                        { label: '已封禁', value: 0 },
                        { label: '未授权', value: 0 },
                        { label: '已绑定域名', value: 0 }
                    ],
                    
                    // 最近授权记录
                    recentAuths: [],
                    
                    // 授权列表
                    authList: [],
                    selectedAuths: [],
                    pagination: {
                        page: 1,
                        per_page: 20,
                        total: 0
                    },
                    
                    // 搜索表单
                    searchForm: {
                        keyword: '',
                        status: '',
                        product_id: ''
                    },
                    
                    // 产品列表
                    products: [],
                    
                    // 添加授权表单
                    addForm: {
                        user_id: '',
                        post_id: '',
                        product_id: '',
                        aut_max_url: 3,
                        is_authorized: true,
                        auth_code: '',
                        domains: []
                    },
                    addFormRules: {
                        user_id: [{ required: true, message: '请选择用户', trigger: 'change' }],
                        post_id: [{ required: true, message: '请选择产品', trigger: 'change' }],
                        aut_max_url: [{ required: true, message: '请输入域名配额', trigger: 'blur' }]
                    },
                    domainInput: '',
                    userOptions: [],
                    
                    // 快速创建
                    showQuickCreate: false,
                    orderList: [],
                    selectedOrders: [],
                    
                    // 对话框
                    detailDialogVisible: false,
                    editDialogVisible: false,
                    currentAuth: null,
                    
                    // 编辑表单
                    editForm: {
                        id: '',
                        is_authorized: true,
                        aut_max_url: 3,
                        auth_code: '',
                        domains: [],
                        remark: ''
                    },
                    editFormRules: {
                        aut_max_url: [{ required: true, message: '请输入域名配额', trigger: 'blur' }]
                    },
                    
                    // 操作记录
                    operationRecords: []
                };
            },
            
            mounted() {
                this.init();
            },
            
            methods: {
                // 初始化
                async init() {
                    this.loading = true;
                    try {
                        await Promise.all([
                            this.loadStats(),
                            this.loadRecentAuths(),
                            this.loadProducts(),
                            this.loadAuthList() // 添加授权列表加载
                        ]);
                    } catch (error) {
                        this.$message.error('初始化失败：' + error.message);
                    } finally {
                        this.loading = false;
                    }
                },
                
                // 路由导航
                menuGo(path) {
                    this.$router.push(path);
                },
                
                // 加载统计数据
                async loadStats() {
                    try {
                        const response = await this.apiRequest('mrhe_admin_get_auth_stats');
                        if (response.code === 0) {
                            this.stats[0].value = response.data.total || 0;
                            this.stats[1].value = response.data.banned || 0;
                            this.stats[2].value = response.data.inactive || 0;
                            this.stats[3].value = response.data.with_domains || 0;
                        }
                    } catch (error) {
                        console.error('加载统计数据失败:', error);
                    }
                },
                
                // 加载最近授权记录
                async loadRecentAuths() {
                    try {
                        const response = await this.apiRequest('mrhe_admin_get_auth_list', {
                            per_page: 5,
                            page: 1
                        });
                        if (response.code === 0) {
                            this.recentAuths = response.data.list || [];
                        }
                    } catch (error) {
                        console.error('加载最近授权记录失败:', error);
                    }
                },
                
                // 加载产品列表
                async loadProducts() {
                    try {
                        const response = await this.apiRequest('mrhe_admin_get_products');
                        if (response.code === 0) {
                            this.products = response.data || [];
                        }
                    } catch (error) {
                        console.error('加载产品列表失败:', error);
                    }
                },
                
                // 加载授权列表
                async loadAuthList() {
                    this.loading = true;
                    try {
                        const params = {
                            page: this.pagination.page,
                            per_page: this.pagination.per_page,
                            ...this.searchForm
                        };
                        const response = await this.apiRequest('mrhe_admin_get_auth_list', params);
                        if (response.code === 0) {
                            this.authList = response.data.list || [];
                            this.pagination.total = response.data.total || 0;
                        }
                    } catch (error) {
                        this.$message.error('加载授权列表失败：' + error.message);
                    } finally {
                        this.loading = false;
                    }
                },
                
                // 搜索授权
                searchAuths() {
                    this.pagination.page = 1;
                    this.loadAuthList();
                },
                
                // 重置搜索
                resetSearch() {
                    this.searchForm = {
                        keyword: '',
                        status: '',
                        product_id: ''
                    };
                    this.searchAuths();
                },
                
                // 分页处理
                handleSizeChange(size) {
                    this.pagination.per_page = size;
                    this.pagination.page = 1;
                    this.loadAuthList();
                },
                
                handleCurrentChange(page) {
                    this.pagination.page = page;
                    this.loadAuthList();
                },
                
                // 选择处理
                handleSelectionChange(selection) {
                    this.selectedAuths = selection;
                },
                
                // 查看授权详情
                async viewAuth(auth) {
                    this.currentAuth = auth;
                    this.detailDialogVisible = true;
                    await this.loadOperationRecords(auth.id);
                },
                
                // 编辑授权
                editAuth(auth) {
                    this.currentAuth = auth;
                    this.editForm = {
                        id: auth.id,
                        is_authorized: !!auth.is_authorized, // 确保转换为布尔值
                        aut_max_url: auth.aut_max_url,
                        auth_code: auth.auth_code,
                        domains: auth.domain_list || [],
                        remark: auth.remark || ''
                    };
                    this.editDialogVisible = true;
                },
                
                // 删除授权
                async deleteAuth(auth) {
                    try {
                        await this.$confirm('确定要删除此授权记录吗？', '确认删除', {
                            type: 'warning'
                        });
                        
                        const response = await this.apiRequest('mrhe_admin_delete_auth', {
                            id: auth.id
                        });
                        
                        if (response.code === 0) {
                            this.$message.success('删除成功');
                            this.loadAuthList();
                            this.loadStats();
                        } else {
                            this.$message.error(response.msg || '删除失败');
                        }
                    } catch (error) {
                        if (error !== 'cancel') {
                            this.$message.error('删除失败：' + error.message);
                        }
                    }
                },
                
                // 封禁授权
                async banAuth(auth) {
                    try {
                        await this.$confirm(
                            `确定要封禁用户 ${auth.user_login} 的授权吗？封禁后将清空所有已绑定域名。`, 
                            '封禁授权', 
                            { 
                                type: 'warning',
                                confirmButtonText: '确认封禁',
                                cancelButtonText: '取消'
                            }
                        );
                        
                        const response = await this.apiRequest('mrhe_admin_ban_auth', { id: auth.id });
                        if (response.code === 0) {
                            this.$message.success('授权已封禁');
                            this.loadAuthList();
                            this.loadStats();
                        } else {
                            this.$message.error(response.msg || '封禁失败');
                        }
                    } catch (error) {
                        if (error !== 'cancel') {
                            this.$message.error('封禁失败：' + error.message);
                        }
                    }
                },
                
                // 解封授权
                async unbanAuth(auth) {
                    try {
                        await this.$confirm(
                            `确定要解封用户 ${auth.user_login} 的授权吗？`,
                            '解封授权',
                            { type: 'info' }
                        );

                        const response = await this.apiRequest('mrhe_admin_unban_auth', { id: auth.id });
                        if (response.code === 0) {
                            this.$message.success('授权已解封');
                            this.loadAuthList();
                            this.loadStats();
                        } else {
                            this.$message.error(response.msg || '解封失败');
                        }
                    } catch (error) {
                        if (error !== 'cancel') {
                            this.$message.error('解封失败：' + error.message);
                        }
                    }
                },

                // 处理下拉菜单操作
                handleAction(command, auth) {
                    switch(command) {
                        case 'ban':
                            this.banAuth(auth);
                            break;
                        case 'unban':
                            this.unbanAuth(auth);
                            break;
                        case 'delete':
                            this.deleteAuth(auth);
                            break;
                    }
                },
                
                // 批量删除
                async batchDelete() {
                    if (this.selectedAuths.length === 0) {
                        this.$message.warning('请选择要删除的记录');
                        return;
                    }
                    
                    try {
                        await this.$confirm(`确定要删除选中的 ${this.selectedAuths.length} 条记录吗？`, '确认批量删除', {
                            type: 'warning'
                        });
                        
                        const ids = this.selectedAuths.map(item => item.id);
                        const response = await this.apiRequest('mrhe_admin_batch_delete_auth', {
                            ids: ids
                        });
                        
                        if (response.code === 0) {
                            this.$message.success('批量删除成功');
                            this.loadAuthList();
                            this.loadStats();
                        } else {
                            this.$message.error(response.msg || '批量删除失败');
                        }
                    } catch (error) {
                        if (error !== 'cancel') {
                            this.$message.error('批量删除失败：' + error.message);
                        }
                    }
                },
                
                // 提交添加表单
                async submitAddForm() {
                    if (!this.$refs.addFormRef) return;
                    
                    try {
                        await this.$refs.addFormRef.validate();
                        this.submitLoading = true;
                        
                        const response = await this.apiRequest('mrhe_admin_add_auth', this.addForm);
                        
                        if (response.code === 0) {
                            this.$message.success('创建授权成功');
                            this.resetAddForm();
                            this.menuGo('/list');
                            this.loadStats();
                        } else {
                            this.$message.error(response.msg || '创建失败');
                        }
                    } catch (error) {
                        this.$message.error('创建失败：' + error.message);
                    } finally {
                        this.submitLoading = false;
                    }
                },
                
                // 重置添加表单
                resetAddForm() {
                    this.addForm = {
                        user_id: '',
                        post_id: '',
                        product_id: '',
                        aut_max_url: 3,
                        is_authorized: true,
                        auth_code: '',
                        domains: []
                    };
                    this.domainInput = '';
                    if (this.$refs.addFormRef) {
                        this.$refs.addFormRef.resetFields();
                    }
                },
                
                // 提交编辑表单
                async submitEditForm() {
                    if (!this.$refs.editFormRef) return;
                    
                    try {
                        await this.$refs.editFormRef.validate();
                        this.editLoading = true;
                        
                        // 确保 is_authorized 转换为整数
                        const submitData = {
                            ...this.editForm,
                            is_authorized: this.editForm.is_authorized ? 1 : 0
                        };
                        
                        const response = await this.apiRequest('mrhe_admin_update_auth', submitData);
                        
                        if (response.code === 0) {
                            this.$message.success('修改成功');
                            this.editDialogVisible = false;
                            this.loadAuthList();
                            this.loadStats();
                        } else {
                            this.$message.error(response.msg || '修改失败');
                        }
                    } catch (error) {
                        this.$message.error('修改失败：' + error.message);
                    } finally {
                        this.editLoading = false;
                    }
                },
                
                // 域名管理
                addDomain() {
                    if (this.domainInput.trim()) {
                        this.addForm.domains.push(this.domainInput.trim());
                        this.domainInput = '';
                    }
                },
                
                removeDomain(index) {
                    this.addForm.domains.splice(index, 1);
                },
                
                addDomainToEdit() {
                    if (this.domainInput.trim()) {
                        this.editForm.domains.push(this.domainInput.trim());
                        this.domainInput = '';
                    }
                },
                
                removeDomainFromEdit(index) {
                    this.editForm.domains.splice(index, 1);
                },
                
                // 生成授权码
                generateAuthCode() {
                    this.addForm.auth_code = this.generateRandomString(32);
                },
                
                regenerateAuthCode() {
                    this.editForm.auth_code = this.generateRandomString(32);
                },
                
                // 搜索用户
                async searchUsers(query) {
                    if (!query) return;
                    
                    this.userLoading = true;
                    try {
                        const response = await this.apiRequest('mrhe_admin_search_users', {
                            keyword: query
                        });
                        if (response.code === 0) {
                            this.userOptions = response.data || [];
                        }
                    } catch (error) {
                        console.error('搜索用户失败:', error);
                    } finally {
                        this.userLoading = false;
                    }
                },
                
                // 产品变更处理
                handleProductChange(productId) {
                    const product = this.products.find(p => p.ID == productId);
                    if (product) {
                        this.addForm.product_id = product.meta?.product_id || `post_${productId}`;
                    }
                },
                
                // 工具方法
                generateRandomString(length) {
                    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                    let result = '';
                    for (let i = 0; i < length; i++) {
                        result += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    return result;
                },
                
                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleString('zh-CN');
                },
                
                // API 请求
                async apiRequest(action, data = {}) {
                    const formData = new FormData();
                    formData.append('action', action);
                    formData.append('nonce', this.config.nonce);
                    
                    Object.keys(data).forEach(key => {
                        if (Array.isArray(data[key])) {
                            data[key].forEach((item, index) => {
                                formData.append(`${key}[${index}]`, item);
                            });
                        } else {
                            formData.append(key, data[key]);
                        }
                    });
                    
                    const response = await fetch(this.config.ajax_url, {
                        method: 'POST',
                        body: formData
                    });
                    
                    return await response.json();
                },
                
                // 加载操作记录
                async loadOperationRecords(authId) {
                    try {
                        const response = await this.apiRequest('mrhe_admin_get_operation_records', {
                            auth_id: authId
                        });
                        if (response.code === 0) {
                            this.operationRecords = response.data || [];
                        }
                    } catch (error) {
                        console.error('加载操作记录失败:', error);
                    }
                },
                
                // 刷新统计数据
                refreshStats() {
                    this.loadStats();
                    this.loadRecentAuths();
                },

                // 复制授权码
                copyAuthCode() {
                    if (!this.currentAuth || !this.currentAuth.auth_code) {
                        this.$message.warning('授权码不存在');
                        return;
                    }

                    const authCode = this.currentAuth.auth_code;

                    // 使用现代 Clipboard API
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(authCode).then(() => {
                            this.$message.success('授权码已复制到剪贴板');
                        }).catch(() => {
                            this.fallbackCopyAuthCode(authCode);
                        });
                    } else {
                        this.fallbackCopyAuthCode(authCode);
                    }
                },

                // 备用复制方法
                fallbackCopyAuthCode(text) {
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    textarea.select();

                    try {
                        document.execCommand('copy');
                        this.$message.success('授权码已复制到剪贴板');
                    } catch (err) {
                        this.$message.error('复制失败，请手动复制');
                    }

                    document.body.removeChild(textarea);
                }
            }
        });

        // 使用插件
        app.use(router);
        app.use(ElementPlus);
        
        // 挂载应用
        app.mount('#mrhe_auth_app');
        
        // 隐藏加载遮罩
        setTimeout(() => {
            const loadingMask = document.querySelector('.auth-page-loading');
            if (loadingMask) {
                loadingMask.style.display = 'none';
            }
        }, 1000);
    }

    // 等待 DOM 加载完成或立即执行
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initApp);
    } else {
        initApp();
    }
})();
