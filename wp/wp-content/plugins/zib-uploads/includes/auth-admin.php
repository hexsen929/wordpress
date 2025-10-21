<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2024-01-01 10:00:00
 * @LastEditTime : 2024-01-01 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : 子比主题·功能增强插件
 * @Description   : 插件授权管理界面（加密文件）
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

//授权检查类
class ZibPluginAuth
{
    /**
     * 检查是否已授权
     * @return {bool} 是否已授权
     */
    public static function is_aut()
    {
        if (class_exists('ZibUploadsAuthClient')) {
            return ZibUploadsAuthClient::getInstance()->is_authorized();
        }
        return false;
    }
    
    /**
     * 检查是否为本地环境
     * @param {string} $url 要检查的URL
     * @return {bool} 是否为本地环境
     */
    public static function is_local($url = '')
    {
        if (!$url) {
            $url = home_url();
        }
        if (stristr($url, 'localhost') || stristr($url, '127.') || stristr($url, '192.')) {
            return true;
        }
        return false;
    }
}

//未授权时的拦截逻辑
if (!ZibPluginAuth::is_aut() && !ZibPluginAuth::is_local()) {
    //后台保存拦截
    add_action("csf_zibll_plugin_option_save_before", function() {
        wp_send_json_success(array(
            'notice' => '请先完成插件授权！',
            'errors' => 0
        ));
    });
    
    //后台按钮拦截
    add_action('admin_footer', function() {
        echo '<script type="text/javascript">
        jQuery(document).ready(function($) {
            $(document).on("click", ".csf-buttons", function() {
                var r = confirm("子比功能增强插件还未授权，暂时无法使用此功能。开始授权验证？");
                if (r == true) {
                    self.location.href="/wp-admin/admin.php?page=zibll_plugin_option#tab=authorization";
                }
                return false;
            });
        });
        </script>';
    }, 0);
				
    //前台未授权水印
    add_action('wp_footer', function() {
        echo '<footer class="footer text-center">
            <a class="but c-blue" target="_blank" href="https://hexsen.com">
                本站插件由子比功能增强插件强力驱动
            </a>
            <a class="but c-red ml10" target="_blank" href="https://t.me/gomrhe">
                联系作者
            </a>
        </footer>';
    });
}

//AJAX处理：后台激活授权
function zibll_plugin_admin_curl_aut()
{
    if (!class_exists('ZibUploadsAuthClient')) {
        echo json_encode(array('error' => 1, 'msg' => '授权模块未加载'));
        exit;
    }
    
    $auth_code = isset($_POST['cut_code']) ? sanitize_text_field($_POST['cut_code']) : '';
				
    if (empty($auth_code)) {
        //无授权码 → 自动授权
        $result = ZibUploadsAuthClient::getInstance()->auto_verify();
    } else {
        //有授权码 → 手动授权
        $result = ZibUploadsAuthClient::getInstance()->verify_auth_with_code($auth_code);
    }
    
    if ($result['success']) {
        echo json_encode(array(
            'error' => 0,
            'msg' => $result['message'],
            'reload' => 1
        ));
    } else {
        echo json_encode(array(
            'error' => 1,
            'msg' => $result['message']
        ));
    }
    exit;
}
add_action('wp_ajax_zibll_plugin_admin_curl_aut', 'zibll_plugin_admin_curl_aut');

//AJAX处理：撤销授权
function zibll_plugin_curl_delete_authorization()
{
    if (!class_exists('ZibUploadsAuthClient')) {
        echo json_encode(array('error' => 1, 'msg' => '授权模块未加载'));
        exit;
    }
    
    $result = ZibUploadsAuthClient::getInstance()->revoke_auth();
    
    if ($result['success']) {
        echo json_encode(array(
            'error' => 0,
            'msg' => $result['message'],
            'reload' => 1
        ));
    } else {
        echo json_encode(array(
            'error' => 1,
            'msg' => $result['message']
        ));
    }
    exit;
}
add_action('wp_ajax_zibll_plugin_curl_delete_authorization', 'zibll_plugin_curl_delete_authorization');

//自动授权（首次安装时尝试2次）
function zibll_plugin_auto_aut()
{
    $auto_key = ZibUploadsAuthClient::get_auto_key();
    $option   = (int) get_option($auto_key);
    $index    = 2; //自动尝试的次数
    
    if (!ZibPluginAuth::is_aut() && $option < $index) {
        $ajax_url = admin_url('admin-ajax.php');
        $html = '<script type="text/javascript">
        (function ($, window, document) {
            $(document).ready(function ($) {
                var html = \'<div style="position:fixed;top:0;right:0;z-index:9999999;width:100%;height:100%;background:rgba(0,0,0,0.2);"></div><div style="position:fixed;top:6em;right:-1px;z-index:10000000;"><div style="background:linear-gradient(135deg,rgb(255,163,180),rgb(253,54,110));margin-bottom:.6em;color:#fff;padding:1em 3em;box-shadow:-3px 3px 15px rgba(0,0,0,0.2);"><i class="fa fa-spinner fa-spin fa-fw" style="position:absolute;left:10px;top:24px;font-size:20px;"></i><div style="font-size:1.1em;margin-bottom:6px;">感谢您使用子比功能增强插件</div><div class="a-text">正在为您自动授权，请稍后...</div></div></div>\';
                var _html = $(html);
                $("body").append(_html);
                $.post("' . $ajax_url . '", {
                    action: "zibll_plugin_admin_curl_aut"
                }, function(n) {
                    var msg = n.msg;
                    var error = n.error;
                    _html.find(".a-text").html(msg);
                    if (!error) {
                        _html.find("div:last").css("background", "linear-gradient(135deg,rgb(124,191,251),rgb(10,105,227))");
                    }
                    setTimeout(function() {
                        location.reload();
                    }, 100);
                }, "json");
            });
        })(jQuery, window, document);
        </script>';
        echo $html;
        update_option($auto_key, $option + 1);
    }
}
add_action('admin_notices', 'zibll_plugin_auto_aut', 99);

//CSF模块类
class CFS_Module_ZibPlugin
{
    /**
     * 生成授权界面HTML
     * @return {array} CSF字段配置
     */
    public static function aut()
    {
        $is_aut = ZibPluginAuth::is_aut();
        $is_local = ZibPluginAuth::is_local();
        
        if ($is_aut) {
            $con = '<div id="authorization_form" class="ajax-form" ajax-url="' . esc_url(admin_url('admin-ajax.php')) . '">
            <div class="ok-icon"><svg t="1585712312243" class="icon" style="width: 1em; height: 1em;vertical-align: middle;fill: currentColor;overflow: hidden;" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3845" data-spm-anchor-id="a313x.7781069.0.i0"><path d="M115.456 0h793.6a51.2 51.2 0 0 1 51.2 51.2v294.4a102.4 102.4 0 0 1-102.4 102.4h-691.2a102.4 102.4 0 0 1-102.4-102.4V51.2a51.2 51.2 0 0 1 51.2-51.2z m0 0" fill="#FF6B5A" p-id="3846"></path><path d="M256 13.056h95.744v402.432H256zM671.488 13.056h95.744v402.432h-95.744z" fill="#FFFFFF" p-id="3847"></path><path d="M89.856 586.752L512 1022.72l421.632-435.2z m0 0" fill="#6DC1E2" p-id="3848"></path><path d="M89.856 586.752l235.52-253.952h372.736l235.52 253.952z m0 0" fill="#ADD9EA" p-id="3849"></path><path d="M301.824 586.752L443.136 332.8h137.216l141.312 253.952z m0 0" fill="#E1F9FF" p-id="3850"></path><path d="M301.824 586.752l209.92 435.2 209.92-435.2z m0 0" fill="#9AE6F7" p-id="3851"></path></svg></div>
            <p style=" color: #0087e8; font-size: 15px; "><svg class="icon" style="width: 1em;height: 1em;vertical-align: -.2em;fill: currentColor;overflow: hidden;font-size: 1.4em;" viewBox="0 0 1024 1024"><path d="M492.224 6.72c11.2-8.96 26.88-8.96 38.016 0l66.432 53.376c64 51.392 152.704 80.768 243.776 80.768 27.52 0 55.104-2.624 81.92-7.872a30.08 30.08 0 0 1 24.96 6.4 30.528 30.528 0 0 1 11.008 23.424V609.28c0 131.84-87.36 253.696-228.288 317.824L523.52 1021.248a30.08 30.08 0 0 1-24.96 0l-206.464-94.08C151.36 862.976 64 741.12 64 609.28V162.944a30.464 30.464 0 0 1 36.16-29.888 425.6 425.6 0 0 0 81.92 7.936c91.008 0 179.84-29.504 243.712-80.768z m19.008 62.528l-47.552 38.208c-75.52 60.8-175.616 94.144-281.6 94.144-19.2 0-38.464-1.024-57.472-3.328V609.28c0 107.84 73.92 208.512 192.768 262.72l193.856 88.384 193.92-88.384c118.912-54.208 192.64-154.88 192.64-262.72V198.272a507.072 507.072 0 0 1-57.344 3.328c-106.176 0-206.144-33.408-281.728-94.08l-47.488-38.272z m132.928 242.944c31.424 0 56.832 25.536 56.832 56.832H564.544v90.944h121.92a56.448 56.448 0 0 1-56.384 56.384H564.48v103.424h150.272a56.832 56.832 0 0 1-56.832 56.832H365.056a56.832 56.832 0 0 1-56.832-56.832h60.608v-144c0-33.92 27.52-61.44 61.44-61.44v205.312h71.68V369.024H324.8c0-31.424 25.472-56.832 56.832-56.832z" p-id="4799"></path></svg> 恭喜您! 插件已完成授权</p>
            <input type="hidden" ajax-name="action" value="zibll_plugin_curl_delete_authorization">
            <a id="authorization_submit" class="but c-red ajax-submit">撤销授权</a>
            <div class="ajax-notice"></div>
            </div>';
        } else {
            $con = '<div id="authorization_form" class="ajax-form" ajax-url="' . esc_url(admin_url('admin-ajax.php')) . '">
            <div class="ok-icon"><svg class="icon" style="font-size: 1.2em;width: 1em; height: 1em;vertical-align: middle;fill: currentColor;overflow: hidden;" viewBox="0 0 1024 1024"><path d="M880 502.3V317.1c0-34.9-24.4-66-60.8-77.4l-80.4-30c-37.8-14.1-73.4-32.9-105.7-55.7l-84.6-60c-19.2-15.2-47.8-15.2-67 0l-84.7 59.9c-32.3 22.8-67.8 41.6-105.7 55.7l-80.4 30c-36.4 11.4-60.8 42.5-60.8 77.4v185.2c0 123.2 63.9 239.2 172.5 313.2l158.5 108c20.2 13.7 47.9 13.7 68.1 0l158.5-108C816.1 741.6 880 625.5 880 502.3z" fill="#0DCEA7" p-id="17337"></path><path d="M150 317.1v3.8c13.4-27.6 30-53.3 49.3-76.7C169.4 258 150 286 150 317.1zM880 317.1c0-34.9-24.4-66-60.8-77.4l-43.5-16.2c57.7 60.6 95.8 140 104.2 228.1l0.1-134.5zM572.8 111.2L548.5 94c-19.2-15.2-47.8-15.2-67 0l-15.3 10.8c10-0.8 20.2-1.2 30.5-1.2 26 0.1 51.5 2.7 76.1 7.6zM496.7 873.9c-39.5 0-77.6-5.9-113.4-17l97.7 66.6c20.2 13.7 47.9 13.7 68.1 0l158.5-108c92.3-62.9 152.3-156.1 168.2-258.3C843.5 737.3 686 873.9 496.7 873.9z" fill="#0DCEA7" p-id="17338"></path><path d="M875.8 557.2c2.8-18.1 4.3-36.4 4.3-54.9v-50.8c-8.5-88.1-46.6-167.4-104.2-228.1L739 209.6c-37.8-14.1-73.4-32.9-105.7-55.7l-60.5-42.7c-24.6-4.9-50-7.5-76.1-7.5-10.3 0-20.4 0.4-30.5 1.2l-58.7 41.5c23.4-5.2 47.7-8 72.7-8 183.6 0 332.4 148.8 332.4 332.4S663.9 803 480.3 803c-170.8 0-311.5-128.9-330.2-294.7 2 121 65.6 234.5 172.4 307.2l60.8 41.4c35.9 11 74 17 113.4 17 189.3 0 346.8-136.6 379.1-316.7zM261.2 220.8l-50.4 18.8c-4 1.3-7.8 2.8-11.5 4.5-19.3 23.4-35.9 49.2-49.3 76.7v112.7c9.4-84.5 50.5-159.4 111.2-212.7z" fill="#1DD49C" p-id="17339"></path><path d="M480.3 803c183.6 0 332.4-148.8 332.4-332.4S663.9 138.3 480.3 138.3c-25 0-49.3 2.8-72.7 8l-10.7 7.6c-32.3 22.8-67.8 41.6-105.7 55.7l-30 11.2C200.5 274.1 159.4 349 150 433.6v68.8c0 2 0 4 0.1 6C168.8 674.1 309.5 803 480.3 803z m-16.4-630c154.4 0 279.6 125.2 279.6 279.6S618.3 732.2 463.9 732.2 184.3 607 184.3 452.6 309.5 173 463.9 173z" fill="#2DDB92" p-id="17340"></path><path d="M463.9 732.2c154.4 0 279.6-125.2 279.6-279.6S618.3 173 463.9 173 184.3 298.2 184.3 452.6s125.2 279.6 279.6 279.6z m-16.4-524.5c125.3 0 226.8 101.5 226.8 226.8S572.8 661.3 447.5 661.3 220.7 559.8 220.7 434.5s101.6-226.8 226.8-226.8z" fill="#3DE188" p-id="17341" data-spm-anchor-id="a313x.7781069.0.i7"></path><path d="M447.5 661.3c125.3 0 226.8-101.5 226.8-226.8S572.8 207.7 447.5 207.7 220.7 309.2 220.7 434.5s101.6 226.8 226.8 226.8z m-16.4-419c96.1 0 174 77.9 174 174s-77.9 174-174 174-174-77.9-174-174 77.9-174 174-174z" fill="#4CE77D" p-id="17342"></path><path d="M431.1 590.4c96.1 0 174-77.9 174-174s-77.9-174-174-174-174 77.9-174 174 77.9 174 174 174zM414.7 277c67 0 121.3 54.3 121.3 121.3s-54.3 121.3-121.3 121.3-121.3-54.3-121.3-121.3S347.8 277 414.7 277z" fill="#5CEE73" p-id="17343"></path><path d="M414.7 398.3m-121.3 0a121.3 121.3 0 1 0 242.6 0 121.3 121.3 0 1 0-242.6 0Z" fill="#6CF468" p-id="17344"></path><path d="M515 100.7c8.3 0 16.2 2.7 22.3 7.5l0.4 0.3 0.4 0.3 84.7 59.9c33.5 23.7 70.5 43.2 109.8 57.9l80.4 30 0.4 0.2 0.5 0.1c28.8 9.1 48.2 33.3 48.2 60.3v185.2c0 28.9-3.7 57.8-11.1 86-7.3 27.8-18.1 54.8-32.2 80.4-14.1 25.6-31.5 49.8-51.7 71.8-20.5 22.4-43.9 42.6-69.6 60.1L539 908.6c-6.8 4.6-15.3 7.2-23.9 7.2s-17.1-2.6-23.9-7.2l-158.5-108c-25.7-17.5-49.1-37.7-69.6-60.1-20.2-22-37.6-46.2-51.7-71.8-14.1-25.6-24.9-52.6-32.2-80.4-7.4-28.1-11.1-57-11.1-86V317.1c0-27 19.4-51.2 48.2-60.3l0.5-0.1 0.4-0.2 80.4-30c39.3-14.7 76.2-34.1 109.8-57.9l84.7-59.9 0.4-0.3 0.4-0.3c5.9-4.8 13.9-7.4 22.1-7.4m0-18c-11.9 0-23.9 3.8-33.5 11.4L396.8 154c-32.3 22.8-67.8 41.6-105.7 55.7l-80.4 30c-36.4 11.4-60.8 42.5-60.8 77.4v185.2c0 123.2 63.9 239.2 172.5 313.2l158.5 108c10.1 6.9 22.1 10.3 34 10.3 12 0 24-3.4 34-10.3l158.5-108c108.6-74 172.5-190 172.5-313.2V317.1c0-34.9-24.4-66-60.8-77.4l-80.4-30c-37.8-14.1-73.4-32.9-105.7-55.7l-84.5-60c-9.6-7.5-21.5-11.3-33.5-11.3z" fill="#0EC69A" p-id="17345"></path><path d="M688.8 496.7V406c0-17.1-11.6-32.3-28.9-37.9l-38.3-14.7c-18-6.9-35-16.1-50.3-27.3L531 296.8c-9.1-7.4-22.8-7.4-31.9 0l-40.3 29.3a218.45 218.45 0 0 1-50.3 27.3l-38.3 14.7c-17.3 5.6-28.9 20.8-28.9 37.9v90.7c0 60.3 30.4 117.1 82.1 153.3l75.5 52.9c9.6 6.7 22.8 6.7 32.4 0l75.5-52.9c51.6-36.2 82-93 82-153.3z" fill="#9CFFBD" p-id="17346"></path><path d="M325.6 287.5c-7.2 0-14.1-4.4-16.8-11.6-3.5-9.3 1.1-19.7 10.4-23.2 68.5-26.2 110.5-60.3 110.9-60.6 7.7-6.3 19-5.2 25.3 2.5s5.2 19-2.5 25.3c-1.9 1.5-47 38.2-120.9 66.4-2.1 0.8-4.2 1.2-6.4 1.2z" fill="#FFFFFF" p-id="17347"></path><path d="M260.2 311.7c-7.3 0-14.2-4.5-16.9-11.7-3.5-9.3 1.3-19.7 10.6-23.1l10.5-3.9c9.3-3.5 19.7 1.3 23.1 10.6 3.5 9.3-1.3 19.7-10.6 23.1l-10.5 3.9c-2.1 0.7-4.2 1.1-6.2 1.1z" fill="#FFFFFF" p-id="17348"></path></svg></div>
            <p style="color:#fd4c73;">激动人心的时候到了！即将开启优雅的建站之旅！</p>
            <div class="hide-box">
            <p>请输入购买插件时获取的授权码：</p>
            <input class="regular-text" type="text" ajax-name="cut_code" value="" placeholder="请输入授权码">
            <input type="hidden" ajax-name="action" value="zibll_plugin_admin_curl_aut">
            </div>
            <a id="authorization_submit" class="but c-blue ajax-submit curl-aut-submit" data-depend-id="zibll_plugin_submit_aut">一键授权</a>
            <div class="ajax-notice"></div>
            </div>';
        }
        
        if (!$is_local) {
            return array(
                'type'    => 'content',
                'content' => $con,
            );
        } else {
            return array(
                'type'    => 'content',
                'style'   => 'info',
                'content' => '<div id="authorization_form">
                <p>本地环境无需授权</p>
                </div>',
            );
        }
    }
}
