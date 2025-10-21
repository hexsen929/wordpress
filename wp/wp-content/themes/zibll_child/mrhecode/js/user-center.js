/**
 * mrhe主题授权系统 - 用户中心JavaScript
 * @Author: mrhe主题授权系统
 * @Description: 处理用户中心的产品下载和授权管理交互
 */

(function($) {
    'use strict';
    
    // 初始化
    $(document).ready(function() {
        initProductActions();
        initModals();
        initCopyFunctions();
    });
    
    /**
     * 初始化产品操作
     */
    function initProductActions() {
        // 下载页面按钮
        $(document).on('click', '.get-download-page', function(e) {
            e.preventDefault();
            var postId = $(this).data('post-id');
            loadDownloadPage(postId);
        });
        
        // 授权页面按钮
        $(document).on('click', '.get-auth-page', function(e) {
            e.preventDefault();
            var postId = $(this).data('post-id');
            loadAuthPage(postId);
        });
        
        // 下载链接按钮
        $(document).on('click', '.download-link-btn', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            if (url) {
                window.open(url, '_blank');
            }
        });
        
        // 复制授权码按钮
        $(document).on('click', '.copy-auth-code', function(e) {
            e.preventDefault();
            var text = $(this).data('clipboard-text');
            copyToClipboard(text);
        });
        
        // 添加域名按钮
        $(document).on('click', '.add-domain-btn', function(e) {
            e.preventDefault();
            var postId = $(this).data('post-id');
            showAddDomainModal(postId);
        });
        
        // 编辑域名按钮
        $(document).on('click', '.edit-domain-btn', function(e) {
            e.preventDefault();
            var domain = $(this).data('domain');
            var postId = $(this).data('post-id');
            showEditDomainModal(postId, domain);
        });
        
        // 删除域名按钮
        $(document).on('click', '.remove-domain-btn', function(e) {
            e.preventDefault();
            var domain = $(this).data('domain');
            var postId = $(this).data('post-id');
            removeDomain(postId, domain);
        });
        
        // 刷新授权按钮
        $(document).on('click', '.refresh-auth-btn', function(e) {
            e.preventDefault();
            var postId = $(this).data('post-id');
            refreshAuth(postId);
        });
    }
    
    /**
     * 初始化模态框
     */
    function initModals() {
        // 添加域名模态框
        if ($('#add-domain-modal').length === 0) {
            $('body').append(createAddDomainModal());
        }
        
        // 编辑域名模态框
        if ($('#edit-domain-modal').length === 0) {
            $('body').append(createEditDomainModal());
        }
    }
    
    /**
     * 初始化复制功能
     */
    function initCopyFunctions() {
        // 使用Clipboard.js如果可用
        if (typeof ClipboardJS !== 'undefined') {
            new ClipboardJS('.copy-auth-code');
        }
    }
    
    /**
     * 加载下载页面
     */
    function loadDownloadPage(postId) {
        showLoading('#mrhe-download-modal .download-content');
        $('#mrhe-download-modal').modal('show');
        
        $.ajax({
            url: mrhe_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mrhe_get_download_page',
                post_id: postId,
                nonce: mrhe_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#mrhe-download-modal .download-content').html(response.data);
                } else {
                    showError('#mrhe-download-modal .download-content', response.data);
                }
            },
            error: function() {
                showError('#mrhe-download-modal .download-content', '加载失败，请重试');
            }
        });
    }
    
    /**
     * 加载授权页面
     */
    function loadAuthPage(postId) {
        showLoading('#mrhe-auth-modal .auth-content');
        $('#mrhe-auth-modal').modal('show');
        
        $.ajax({
            url: mrhe_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mrhe_get_auth_page',
                post_id: postId,
                nonce: mrhe_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#mrhe-auth-modal .auth-content').html(response.data);
                } else {
                    showError('#mrhe-auth-modal .auth-content', response.data);
                }
            },
            error: function() {
                showError('#mrhe-auth-modal .auth-content', '加载失败，请重试');
            }
        });
    }
    
    /**
     * 显示添加域名模态框
     */
    function showAddDomainModal(postId) {
        $('#add-domain-modal').find('input[name="post_id"]').val(postId);
        $('#add-domain-modal').find('input[name="domain"]').val('');
        $('#add-domain-modal').modal('show');
    }
    
    /**
     * 显示编辑域名模态框
     */
    function showEditDomainModal(postId, domain) {
        $('#edit-domain-modal').find('input[name="post_id"]').val(postId);
        $('#edit-domain-modal').find('input[name="old_domain"]').val(domain);
        $('#edit-domain-modal').find('input[name="new_domain"]').val(domain);
        $('#edit-domain-modal').modal('show');
    }
    
    /**
     * 添加域名
     */
    function addDomain(postId, domain) {
        $.ajax({
            url: mrhe_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mrhe_add_auth_domain',
                post_id: postId,
                domain: domain,
                nonce: mrhe_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(response.data);
                    $('#add-domain-modal').modal('hide');
                    loadAuthPage(postId);
                } else {
                    showError('#add-domain-modal .modal-body', response.data);
                }
            },
            error: function() {
                showError('#add-domain-modal .modal-body', '操作失败，请重试');
            }
        });
    }
    
    /**
     * 更新域名
     */
    function updateDomain(postId, oldDomain, newDomain) {
        $.ajax({
            url: mrhe_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mrhe_update_auth_domain',
                post_id: postId,
                old_domain: oldDomain,
                new_domain: newDomain,
                nonce: mrhe_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(response.data);
                    $('#edit-domain-modal').modal('hide');
                    loadAuthPage(postId);
                } else {
                    showError('#edit-domain-modal .modal-body', response.data);
                }
            },
            error: function() {
                showError('#edit-domain-modal .modal-body', '操作失败，请重试');
            }
        });
    }
    
    /**
     * 删除域名
     */
    function removeDomain(postId, domain) {
        if (!confirm('确定要删除域名 "' + domain + '" 吗？')) {
            return;
        }
        
        $.ajax({
            url: mrhe_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mrhe_remove_auth_domain',
                post_id: postId,
                domain: domain,
                nonce: mrhe_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(response.data);
                    loadAuthPage(postId);
                } else {
                    showError(response.data);
                }
            },
            error: function() {
                showError('操作失败，请重试');
            }
        });
    }
    
    /**
     * 刷新授权
     */
    function refreshAuth(postId) {
        if (!confirm('确定要刷新授权吗？这将重新生成授权码。')) {
            return;
        }
        
        $.ajax({
            url: mrhe_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mrhe_refresh_auth',
                post_id: postId,
                nonce: mrhe_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(response.data);
                    loadAuthPage(postId);
                } else {
                    showError(response.data);
                }
            },
            error: function() {
                showError('操作失败，请重试');
            }
        });
    }
    
    /**
     * 复制到剪贴板
     */
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                showSuccess('已复制到剪贴板');
            });
        } else {
            // 降级方案
            var textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                showSuccess('已复制到剪贴板');
            } catch (err) {
                showError('复制失败');
            }
            document.body.removeChild(textArea);
        }
    }
    
    /**
     * 显示加载状态
     */
    function showLoading(selector) {
        $(selector).html('<div class="text-center p20"><i class="fa fa-spinner fa-spin mr6"></i>加载中...</div>');
    }
    
    /**
     * 显示错误信息
     */
    function showError(selector, message) {
        if (typeof selector === 'string') {
            $(selector).html('<div class="alert jb-red">' + message + '</div>');
        } else {
            // 全局错误提示
            if (typeof zib_notice !== 'undefined') {
                zib_notice(message, 'error');
            } else {
                alert(message);
            }
        }
    }
    
    /**
     * 显示成功信息
     */
    function showSuccess(message) {
        if (typeof zib_notice !== 'undefined') {
            zib_notice(message, 'success');
        } else {
            alert(message);
        }
    }
    
    /**
     * 创建添加域名模态框
     */
    function createAddDomainModal() {
        return '<div id="add-domain-modal" class="modal fade" tabindex="-1">' +
            '<div class="modal-dialog">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<h5 class="modal-title">添加授权域名</h5>' +
            '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
            '</div>' +
            '<div class="modal-body">' +
            '<form id="add-domain-form">' +
            '<input type="hidden" name="post_id" value="">' +
            '<div class="form-group">' +
            '<label>域名</label>' +
            '<input type="text" name="domain" class="form-control" placeholder="例如: example.com" required>' +
            '</div>' +
            '</form>' +
            '</div>' +
            '<div class="modal-footer">' +
            '<button type="button" class="but c-gray" data-dismiss="modal">取消</button>' +
            '<button type="button" class="but c-blue" onclick="addDomainSubmit()">添加</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
    }
    
    /**
     * 创建编辑域名模态框
     */
    function createEditDomainModal() {
        return '<div id="edit-domain-modal" class="modal fade" tabindex="-1">' +
            '<div class="modal-dialog">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<h5 class="modal-title">编辑授权域名</h5>' +
            '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
            '</div>' +
            '<div class="modal-body">' +
            '<form id="edit-domain-form">' +
            '<input type="hidden" name="post_id" value="">' +
            '<input type="hidden" name="old_domain" value="">' +
            '<div class="form-group">' +
            '<label>新域名</label>' +
            '<input type="text" name="new_domain" class="form-control" required>' +
            '</div>' +
            '</form>' +
            '</div>' +
            '<div class="modal-footer">' +
            '<button type="button" class="but c-gray" data-dismiss="modal">取消</button>' +
            '<button type="button" class="but c-blue" onclick="updateDomainSubmit()">更新</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
    }
    
    // 全局函数
    window.addDomainSubmit = function() {
        var form = $('#add-domain-form');
        var postId = form.find('input[name="post_id"]').val();
        var domain = form.find('input[name="domain"]').val();
        
        if (!domain) {
            showError('#add-domain-modal .modal-body', '请输入域名');
            return;
        }
        
        addDomain(postId, domain);
    };
    
    window.updateDomainSubmit = function() {
        var form = $('#edit-domain-form');
        var postId = form.find('input[name="post_id"]').val();
        var oldDomain = form.find('input[name="old_domain"]').val();
        var newDomain = form.find('input[name="new_domain"]').val();
        
        if (!newDomain) {
            showError('#edit-domain-modal .modal-body', '请输入新域名');
            return;
        }
        
        updateDomain(postId, oldDomain, newDomain);
    };
    
})(jQuery);

