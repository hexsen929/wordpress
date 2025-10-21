<?php
/**
 * MixAuth QQ OAuth 登录页面
 * 基于子比主题官方机制实现
 */

// 安全检测
if (!defined('ABSPATH')) {
    exit;
}

// 检查功能是否启用
if (!zibll_plugin_option('mixauthqq_enable', false)) {
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('MixAuth QQ登录功能未启用');
    } else {
        wp_die('MixAuth QQ登录功能未启用');
    }
}

// 获取配置 - 使用统一配置函数
$config = zibll_plugin_get_oauth_config('mixauthqq');

if (!$config['server_url']) {
    if (function_exists('zib_oauth_die')) {
        zib_oauth_die('MixAuth QQ登录配置不完整', '服务器地址未设置');
    } else {
        wp_die('MixAuth QQ登录配置不完整');
    }
}

// 启用session
@session_start();

// 保存回调地址
if (!empty($_REQUEST['rurl'])) {
    $_SESSION['oauth_rurl'] = $_REQUEST['rurl'];
}

// 生成状态参数
$state = md5(uniqid(rand(), true));
$_SESSION['mixauthqq_state'] = $state;

$server_url = rtrim($config['server_url'], '/');
$integration_mode = $config['integration_mode'];

if ($integration_mode === 'iframe') {
    // iframe模式 - 直接嵌入MixAuth页面
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MixAuth QQ登录</title>
        <style>
            body { 
                margin: 0; padding: 20px; 
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background: #f5f5f5;
            }
            .container { 
                max-width: 400px; margin: 50px auto; 
                background: white; border-radius: 12px; 
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                padding: 30px; text-align: center;
                overflow: visible;
            }
            .header {
                background: none;
                color: #333;
                padding: 0;
                text-align: center;
                margin-bottom: 30px;
            }
            .header h2 { 
                font-size: 24px; font-weight: bold; 
                color: #333; margin-bottom: 10px; 
            }
            .header p { 
                color: #666; margin-bottom: 10px; 
                font-size: 16px;
            }
            .mode-indicator {
                background: #e8f4fd;
                border: 1px solid #bee5eb;
                border-radius: 8px;
                padding: 10px 15px;
                margin-bottom: 20px;
                font-size: 13px;
                color: #0c5460;
                font-weight: 500;
            }
            .iframe-container {
                margin: 20px 0;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                padding: 0;
                height: 500px;
            }
            iframe {
                width: 100%;
                height: 100%;
                border: none;
                background: white;
            }
            .tips { 
                color: #6c757d; font-size: 14px; 
                line-height: 1.5; margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>🎯 MixAuth QQ登录</h2>
                <p>快速、安全的第三方QQ登录</p>
                <div class="mode-indicator">
                    当前模式：iframe嵌入模式
                </div>
            </div>
            <div class="iframe-container">
                <iframe src="<?php echo esc_url($server_url); ?>" width="100%" height="500" frameborder="0"></iframe>
            </div>
            <div class="tips">请在上方窗口中完成QQ登录<br>扫码后请在手机上确认登录</div>
        </div>
        
        <script>
        // 监听iframe消息
        window.addEventListener('message', function(event) {
            if (event.origin !== '<?php echo esc_js(parse_url($server_url, PHP_URL_SCHEME) . '://' . parse_url($server_url, PHP_URL_HOST)); ?>') {
                return;
            }
            
            if (event.data && event.data.type === 'MIXAUTH_SUCCESS') {
                // 处理登录成功
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo esc_url(home_url('/oauth/mixauthqq/callback')); ?>';
                
                var stateInput = document.createElement('input');
                stateInput.type = 'hidden';
                stateInput.name = 'state';
                stateInput.value = '<?php echo esc_js($state); ?>';
                
                var dataInput = document.createElement('input');
                dataInput.type = 'hidden';
                dataInput.name = 'mixauth_data';
                dataInput.value = JSON.stringify(event.data.data);
                
                form.appendChild(stateInput);
                form.appendChild(dataInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
        </script>
    </body>
    </html>
    <?php
    exit;
} else {
    // API模式 - 自定义UI
    
    // 获取二维码
    $qr_response = wp_remote_post($server_url . '/api/qr', array(
        'body' => json_encode(array('type' => 'qq')),
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'timeout' => 30,
    ));
    
    if (is_wp_error($qr_response)) {
        if (function_exists('zib_oauth_die')) {
            zib_oauth_die('获取二维码失败', $qr_response->get_error_message());
        } else {
            wp_die('获取二维码失败: ' . $qr_response->get_error_message());
        }
    }
    
    $response_body = wp_remote_retrieve_body($qr_response);
    $qr_data = json_decode($response_body, true);
    
    // 调试输出
    if (!$qr_data) {
        if (function_exists('zib_oauth_die')) {
            zib_oauth_die('获取二维码失败：API返回数据为空或格式错误<br>原始响应：<pre>' . htmlspecialchars($response_body) . '</pre>');
        } else {
            wp_die('获取二维码失败：数据格式错误');
        }
    }

    // 根据MixAuth的实际返回格式处理数据
    $qr_id = '';
    $qr_data_url = '';
    
    // MixAuth返回格式：{"data": {"id": "xxx", "qrcode": "data:image/png;base64,xxx", "authType": "qq"}, "success": true}
    if (isset($qr_data['success']) && $qr_data['success'] && isset($qr_data['data'])) {
        $data = $qr_data['data'];
        
        if (isset($data['id'])) {
            $qr_id = $data['id'];
        }
        
        if (isset($data['qrcode'])) {
            $qr_data_url = $data['qrcode'];
        }
    }
    
    if (empty($qr_id)) {
        if (function_exists('zib_oauth_die')) {
            zib_oauth_die('获取二维码失败：无法获取二维码ID');
        } else {
            wp_die('获取二维码失败：无法获取二维码ID');
        }
    }
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MixAuth QQ登录</title>
        <style>
            body { 
                margin: 0; padding: 20px; 
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background: #f5f5f5;
            }
            .container { 
                max-width: 400px; margin: 50px auto; 
                background: white; border-radius: 12px; 
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                padding: 30px; text-align: center;
            }
            .logo { font-size: 24px; font-weight: bold; color: #333; margin-bottom: 10px; }
            .subtitle { color: #666; margin-bottom: 30px; }
            .qr-container { margin: 20px 0; }
            .qr-image { 
                max-width: 280px; width: 100%; height: 280px;
                border: 2px solid #f0f0f0; border-radius: 8px;
                object-fit: contain;
            }
            .status-text { 
                margin: 20px 0; padding: 15px; 
                background: #f8f9fa; border-radius: 8px;
                color: #495057; font-size: 16px;
                font-weight: 500;
            }
            .success { color: #28a745; background: #d4edda; }
            .error { color: #dc3545; background: #f8d7da; }
            .scanned { color: #17a2b8; background: #d1ecf1; }
            .tips { color: #6c757d; font-size: 14px; line-height: 1.5; }
            .retry-btn {
                background: #007bff; color: white; border: none;
                padding: 10px 20px; border-radius: 6px; cursor: pointer;
                font-size: 14px; margin-top: 10px;
            }
            .retry-btn:hover { background: #0056b3; }
            .loading { display: inline-block; animation: spin 1s linear infinite; }
            @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>🎯 MixAuth QQ登录</h2>
                <p>快速、安全的第三方QQ登录</p>
                <div class="mode-indicator">
                    当前模式：API接口模式
                </div>
            </div>
            
            <div class="qr-container">
                <div id="qrcode-canvas"></div>
            </div>
            
            <div id="status" class="status-text">
                <span class="loading">⟳</span> 请使用手机QQ扫描二维码
            </div>
            
            <div class="tips">
                扫码后请在手机上确认登录<br>
                二维码有效期约2分钟，请及时扫描
            </div>
        </div>
        
        <script>
        let statusInterval = null;
        let isPolling = false;
        let networkErrorCount = 0;
        
        const serverUrl = '<?php echo esc_js($server_url); ?>';
        const qrId = '<?php echo esc_js($qr_id); ?>';
        const qrDataUrl = '<?php echo isset($qr_data_url) ? esc_js($qr_data_url) : ''; ?>';
        const state = '<?php echo esc_js($state); ?>';
        const statusElement = document.getElementById('status');
        const qrcodeDiv = document.getElementById('qrcode-canvas');
        
        // 显示二维码
        function generateQRCode() {
            try {
                qrcodeDiv.innerHTML = '';
                
                const img = document.createElement('img');
                
                // 如果MixAuth提供了base64二维码，直接使用
                if (qrDataUrl) {
                    // 如果数据不包含data:image前缀，添加它
                    if (qrDataUrl.startsWith('data:image')) {
                        img.src = qrDataUrl;
                    } else {
                        img.src = 'data:image/png;base64,' + qrDataUrl;
                    }
                } else {
                    statusElement.innerHTML = '⚠ 二维码数据获取失败';
                    return;
                }
                
                img.alt = 'QQ登录二维码';
                img.style.width = '280px';
                img.style.height = '280px';
                img.style.border = '1px solid #ddd';
                img.style.borderRadius = '8px';
                
                img.onload = function() {
                    statusElement.innerHTML = '<span class="loading">⟳</span> 请使用手机QQ扫描二维码';
                    startPolling();
                };
                
                img.onerror = function() {
                    statusElement.innerHTML = '⚠ 二维码加载失败';
                    console.error('二维码加载失败，URL:', img.src);
                };
                
                qrcodeDiv.appendChild(img);
                
            } catch (error) {
                console.error('生成二维码失败:', error);
                statusElement.innerHTML = '⚠ 生成二维码失败: ' + error.message;
            }
        }
        
        function startPolling() {
            isPolling = true;
            networkErrorCount = 0;
            statusInterval = setInterval(checkStatus, 3000);
        }
        
        function stopPolling() {
            isPolling = false;
            if (statusInterval) {
                clearInterval(statusInterval);
                statusInterval = null;
            }
        }
        
        async function checkStatus() {
            if (!isPolling || document.hidden) return;
            
            try {
                const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'mixauthqq_check_status',
                        server_url: serverUrl,
                        qr_id: qrId,
                        nonce: '<?php echo wp_create_nonce('mixauthqq_status'); ?>'
                    })
                });
                
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                
                const data = await response.json();
                networkErrorCount = 0;
                
                if (data && data.data && data.data.code !== undefined) {
                    const code = data.data.code;
                    const message = data.data.message || '';
                    
                    switch (code) {
                        case '66':
                            statusElement.innerHTML = '<span class="loading">⟳</span> 等待扫描...';
                            statusElement.className = 'status-text';
                            break;
                        case '67':
                            statusElement.innerHTML = '✓ 已扫描，请在手机上确认登录';
                            statusElement.className = 'status-text scanned';
                            break;
                        case '0':
                            if (data.data.username && (data.data.qq || data.data.openid)) {
                                statusElement.innerHTML = '✅ 登录成功，正在跳转...';
                                statusElement.className = 'status-text success';
                                stopPolling();
                                handleLoginSuccess(data.data, data.signData);
                            } else {
                                statusElement.innerHTML = '❌ 登录数据异常，请重试';
                                statusElement.className = 'status-text error';
                                showRetryOption();
                            }
                            break;
                        case '68':
                            statusElement.innerHTML = '❌ 登录被拒绝';
                            statusElement.className = 'status-text error';
                            stopPolling();
                            showRetryOption();
                            break;
                        case '65':
                            statusElement.innerHTML = '⏰ 二维码已过期，正在刷新...';
                            statusElement.className = 'status-text error';
                            stopPolling();
                            setTimeout(() => location.reload(), 2000);
                            break;
                        default:
                            statusElement.innerHTML = message || '等待扫描...';
                    }
                }
            } catch (error) {
                console.error('状态检查失败:', error);
                networkErrorCount++;
                
                if (networkErrorCount >= 3) {
                    statusElement.innerHTML = '❌ 网络连接异常';
                    stopPolling();
                    showRetryOption();
                } else {
                    statusElement.innerHTML = `⚠️ 连接异常，正在重试(${networkErrorCount}/3)...`;
                }
            }
        }
        
        function handleLoginSuccess(userData, signData) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo esc_url(home_url('/oauth/mixauthqq/callback')); ?>';
            
            const stateInput = document.createElement('input');
            stateInput.type = 'hidden';
            stateInput.name = 'state';
            stateInput.value = state;
            
            const dataInput = document.createElement('input');
            dataInput.type = 'hidden';
            dataInput.name = 'mixauth_result';
            dataInput.value = JSON.stringify(userData);
            
            form.appendChild(stateInput);
            form.appendChild(dataInput);
            
            if (signData) {
                const signInput = document.createElement('input');
                signInput.type = 'hidden';
                signInput.name = 'sign';
                signInput.value = signData;
                form.appendChild(signInput);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
        
        function showRetryOption() {
            statusElement.innerHTML += '<br><button class="retry-btn" onclick="location.reload()">重新获取二维码</button>';
        }
        
        // 页面加载完成后生成二维码
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', generateQRCode);
        } else {
            generateQRCode();
        }
        
        // 页面可见性变化时暂停/恢复轮询
        window.addEventListener('beforeunload', stopPolling);
        
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // 页面隐藏时暂停轮询
            } else {
                // 页面显示时恢复轮询
                if (!isPolling) {
                    startPolling();
                }
            }
        });
        </script>
    </body>
    </html>
    <?php
    exit;
}
?>
