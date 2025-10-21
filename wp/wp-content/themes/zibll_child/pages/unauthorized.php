<?php
/*
 * @Author        : mrhe主题授权系统
 * @Description   : 未授权页面模板
 * @Date          : 2024-01-01
 * @Project       : mrhe主题授权系统
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="text-center" style="padding: 100px 20px;">
                <div class="mb-4">
                    <i class="fa fa-shield" style="font-size: 80px; color: #dc3545;"></i>
                </div>
                
                <h1 class="mb-4">主题未授权</h1>
                
                <p class="lead mb-4">
                    抱歉，您当前使用的主题未获得授权，无法正常访问。
                </p>
                
                <div class="alert alert-warning mb-4" style="max-width: 600px; margin: 0 auto;">
                    <h5>如何获得授权？</h5>
                    <ol class="text-left">
                        <li>购买主题后，您将收到授权码</li>
                        <li>登录网站后台，进入"主题设置" → "主题&授权"</li>
                        <li>输入授权码完成激活</li>
                        <li>在用户中心管理授权域名</li>
                    </ol>
                </div>
                
                <div class="mb-4">
                    <a href="<?php echo admin_url('admin.php?page=mrhe_options#tab=主题&授权'); ?>" 
                       class="btn btn-primary btn-lg mr-3">
                        <i class="fa fa-key"></i> 立即授权
                    </a>
                    
                    <a href="https://hexsen.com" target="_blank" class="btn btn-outline-primary btn-lg">
                        <i class="fa fa-shopping-cart"></i> 购买主题
                    </a>
                </div>
                
                <div class="text-muted">
                    <p>如有问题，请联系技术支持：<a href="https://t.me/gomrhe" target="_blank">@gomrhe</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.375rem;
}

.alert-warning {
    color: #856404;
    background-color: #fff3cd;
    border-color: #ffeaa7;
}

.btn {
    display: inline-block;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    text-align: center;
    text-decoration: none;
    vertical-align: middle;
    cursor: pointer;
    user-select: none;
    background-color: transparent;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    border-radius: 0.375rem;
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.btn-primary {
    color: #fff;
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-outline-primary {
    color: #0d6efd;
    border-color: #0d6efd;
}

.btn-lg {
    padding: 0.5rem 1rem;
    font-size: 1.25rem;
    border-radius: 0.5rem;
}

.mr-3 {
    margin-right: 1rem !important;
}

.mb-4 {
    margin-bottom: 1.5rem !important;
}

.text-center {
    text-align: center !important;
}

.text-left {
    text-align: left !important;
}

.text-muted {
    color: #6c757d !important;
}

.lead {
    font-size: 1.25rem;
    font-weight: 300;
}
</style>

<?php
get_footer();
