<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2025-09-18
 * @LastEditTime: 2025-09-18
 * MixAuth QQ登录处理文件 - API接入方式
 */

require_once dirname(__FILE__) . '/../oauth.php';

// 获取配置
$config = get_oauth_config('mixauthqq');
if (!$config['server_url']) {
    zib_oauth_die('MixAuth QQ登录未设置服务地址');
}

// 启用session
@session_start();

// 保存回调地址
if (!empty($_REQUEST['rurl'])) {
    $_SESSION['oauth_rurl'] = $_REQUEST['rurl'];
}

// 生成状态码
$state = md5(uniqid(rand(), true));
$_SESSION['mixauthqq_state'] = $state;

$server_url = rtrim($config['server_url'], '/');
$integration_mode = $config['integration_mode'] ?? 'api'; // 默认使用API模式

// 根据设置选择接入方式
if ($integration_mode === 'api') {
    // API模式：调用MixAuth API获取二维码
    $qr_response = wp_remote_post($server_url . '/api/qr', array(
        'body' => json_encode(array('type' => 'qq')),
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'timeout' => 30,
    ));

    if (is_wp_error($qr_response)) {
        zib_oauth_die('获取二维码失败：' . $qr_response->get_error_message());
    }

    $response_body = wp_remote_retrieve_body($qr_response);
    $qr_data = json_decode($response_body, true);

    // 调试输出
    if (!$qr_data) {
        zib_oauth_die('获取二维码失败：API返回数据为空或格式错误<br>原始响应：<pre>' . htmlspecialchars($response_body) . '</pre>');
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
        $debug_info = '<br>调试信息：<pre>' . print_r($qr_data, true) . '</pre>';
        zib_oauth_die('获取二维码失败：无法获取二维码ID' . $debug_info);
    }
}
// iframe模式无需预处理

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QQ登录 - <?php echo get_bloginfo('name'); ?></title>
    <style>
        body { 
            margin: 0; padding: 20px; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f5f5;
        }
        .login-container { 
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
        .qr-container { 
            margin: 20px 0; 
            min-height: 320px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        #qrcode-canvas {
            margin: 20px auto;
        }
        #qrcode-canvas img {
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
        .tips { 
            color: #6c757d; font-size: 14px; 
            line-height: 1.5; margin-top: 20px;
        }
        .retry-btn {
            background: #007bff; color: white; border: none;
            padding: 10px 20px; border-radius: 6px; cursor: pointer;
            font-size: 14px; margin-top: 10px;
            transition: background-color 0.2s;
        }
        .retry-btn:hover { background: #0056b3; }
        .loading { 
            display: inline-block; 
            animation: spin 1s linear infinite; 
            margin-right: 8px;
        }
        .success { color: #28a745; background: #d4edda; }
        .error { color: #dc3545; background: #f8d7da; }
        .scanned { color: #17a2b8; background: #d1ecf1; }
        @keyframes spin { 
            0% { transform: rotate(0deg); } 
            100% { transform: rotate(360deg); } 
        }
        
        /* iframe模式样式 */
        .iframe-container {
            margin: 20px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 0;
            height: 500px;
        }
        iframe,
        #mixauth-iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="header">
            <h2>🎯 MixAuth QQ登录</h2>
            <p>快速、安全的第三方QQ登录</p>
            <div class="mode-indicator">
                当前模式：<?php echo $integration_mode === 'api' ? 'API接口模式' : 'iframe嵌入模式'; ?>
            </div>
        </div>
        
        <?php if ($integration_mode === 'api'): ?>
        <!-- API模式：自定义二维码UI -->
        <div class="qr-container">
            <div id="qrcode-canvas"></div>
            <div class="status-text">
                <span class="loading">⟳</span> 请使用手机QQ扫描二维码
            </div>
            <div class="tips">
                扫码后请在手机上确认登录<br>
                二维码有效期约2分钟，请及时扫描
            </div>
        </div>
        <?php else: ?>
        <!-- iframe模式：嵌入MixAuth完整页面 -->
        <div class="iframe-container">
            <iframe src="<?php echo esc_url($server_url); ?>" id="mixauth-iframe"></iframe>
        </div>
        <div class="tips">
            扫码后请在手机上确认登录<br>
            二维码有效期约2分钟，请及时扫描
        </div>
        <?php endif; ?>
    </div>

    <script>
        const integrationMode = '<?php echo esc_js($integration_mode); ?>';
        
        if (integrationMode === 'api') {
            // API模式的JavaScript代码
            initApiMode();
        } else {
            // iframe模式的JavaScript代码
            initIframeMode();
        }
        
        // API模式初始化
        function initApiMode() {
            let statusInterval = null;
            let isPolling = false;
            
            const serverUrl = '<?php echo esc_js($server_url); ?>';
            const qrId = '<?php echo isset($qr_id) ? esc_js($qr_id) : ''; ?>';
            const qrDataUrl = '<?php echo isset($qr_data_url) ? esc_js($qr_data_url) : ''; ?>';
            const statusText = document.querySelector('.status-text');
            const qrcodeDiv = document.getElementById('qrcode-canvas');

            // 显示二维码
            function generateQRCode() {
                try {
                    qrcodeDiv.innerHTML = '';
                    
                    const img = document.createElement('img');
                    
                    // 如果MixAuth提供了base64二维码，直接使用
                    if (qrDataUrl) {
                        img.src = qrDataUrl;
                    } else {
                        // 兜底方案：如果没有base64，使用Google Charts API
                        statusText.textContent = '二维码数据异常，正在尝试其他方式...';
                        return;
                    }
                    
                    img.alt = 'QQ登录二维码';
                    img.style.width = '280px';
                    img.style.height = '280px';
                    img.style.border = '1px solid #ddd';
                    img.style.borderRadius = '8px';
                    
                    img.onload = function() {
                        statusText.innerHTML = '<span class="loading">⟳</span> 请使用手机QQ扫描二维码';
                        statusText.className = 'status-text';
                        startPolling();
                    };
                    
                    img.onerror = function() {
                        qrcodeDiv.innerHTML = '<div style="padding: 20px; border: 2px dashed #ccc; border-radius: 8px; text-align: center;"><p>二维码图片加载失败</p><p>请刷新页面重试</p></div>';
                        statusText.innerHTML = '⚠ 二维码加载失败，请刷新页面';
                        statusText.className = 'status-text error';
                    };
                    
                    qrcodeDiv.appendChild(img);
                    
                } catch (error) {
                    console.error('显示二维码失败:', error);
                    qrcodeDiv.innerHTML = '<div style="padding: 20px; border: 2px dashed #ccc; border-radius: 8px; text-align: center;"><p>二维码显示失败</p><p>请刷新页面重试</p></div>';
                    statusText.textContent = '显示二维码失败，请刷新页面';
                }
            }

            // 检查二维码状态
            async function checkStatus() {
                if (!isPolling) return;
                
                // 页面不可见时暂停检查，节省资源
                if (document.hidden) return;
                
                try {
                    // 使用WordPress AJAX来代理请求，避免CORS问题
                    const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'mixauthqq_check_status',
                            server_url: serverUrl,
                            qr_id: qrId,
                            nonce: '<?php echo wp_create_nonce('mixauthqq_status'); ?>'
                        })
                    });
                    
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                    }
                    
                    const data = await response.json();
                    
                    
                    // 检查API返回的登录结果
                    if (data && typeof data === 'object') {
                        // 检查MixAuth的具体状态码
                        if (data.data && data.data.code) {
                            const code = data.data.code;
                            const message = data.data.message || '';
                            
                            switch (code) {
                                case '66':
                                    statusText.innerHTML = '<span class="loading">⟳</span> 等待扫描二维码...';
                                    statusText.className = 'status-text';
                                    break;
                                case '67':
                                    statusText.innerHTML = '✓ 已扫描，请在手机上确认登录';
                                    statusText.className = 'status-text scanned';
                                    break;
                                case '0':
                                    // 登录成功 - 检查是否有签名数据
                                    if (data.data.username && (data.data.qq || data.data.openid)) {
                                        if (data.signData) {
                                            // 有签名数据，需要验证
                                            statusText.innerHTML = '✅ 登录成功，正在验证...';
                                            statusText.className = 'status-text success';
                                            stopPolling();
                                            handleLoginSuccessWithSign(data.signData);
                                        } else {
                                            // 没有签名数据，直接使用用户数据
                                            statusText.innerHTML = '✅ 登录成功，正在跳转...';
                                            statusText.className = 'status-text success';
                                            stopPolling();
                                            handleLoginSuccess(data.data);
                                        }
                                    } else {
                                        statusText.innerHTML = '❌ 登录数据异常，请重试';
                                        statusText.className = 'status-text error';
                                    }
                                    break;
                                case '68':
                                    statusText.innerHTML = '✗ 登录被拒绝';
                                    statusText.className = 'status-text error';
                                    stopPolling();
                                    showRetryOption();
                                    break;
                                case '65':
                                    statusText.innerHTML = '⚠ 二维码已过期，正在刷新...';
                                    statusText.className = 'status-text error';
                                    stopPolling();
                                    location.reload();
                                    break;
                                default:
                                    statusText.innerHTML = '<span class="loading">⟳</span> ' + (message || '等待扫描二维码...');
                                    statusText.className = 'status-text';
                            }
                        } else {
                            // 没有明确状态，显示等待
                            statusText.textContent = '等待扫描...';
                        }
                    }
                } catch (error) {
                    console.error('状态检查出错:', error);
                    
                    // 网络错误超过3次，停止轮询并显示重试选项
                    if (!window.networkErrorCount) window.networkErrorCount = 0;
                    window.networkErrorCount++;
                    
                    if (window.networkErrorCount >= 3) {
                        statusText.textContent = '网络连接异常';
                        stopPolling();
                        showRetryOption();
                    } else {
                        statusText.textContent = `连接异常，正在重试(${window.networkErrorCount}/3)...`;
                        // 连接异常时稍后重试
                        setTimeout(() => {
                            if (isPolling) {
                                statusText.textContent = '等待扫描...';
                            }
                        }, 5000);
                    }
                }
            }

            // 开始轮询
            function startPolling() {
                isPolling = true;
                window.networkErrorCount = 0; // 重置网络错误计数
                statusInterval = setInterval(checkStatus, 3000); // 每3秒检查一次，减少服务器压力
            }

            // 停止轮询
            function stopPolling() {
                isPolling = false;
                if (statusInterval) {
                    clearInterval(statusInterval);
                    statusInterval = null;
                }
            }

            // 处理登录成功（直接用户数据）
            function handleLoginSuccess(userData) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo esc_url(home_url('/oauth/mixauthqq/callback')); ?>';
                
                const stateInput = document.createElement('input');
                stateInput.type = 'hidden';
                stateInput.name = 'state';
                stateInput.value = '<?php echo esc_js($state); ?>';
                
                const userDataInput = document.createElement('input');
                userDataInput.type = 'hidden';
                userDataInput.name = 'mixauth_result';
                userDataInput.value = JSON.stringify(userData);
                
                form.appendChild(stateInput);
                form.appendChild(userDataInput);
                
                document.body.appendChild(form);
                form.submit();
            }
            
            // 处理登录成功（签名数据，需要验证）
            function handleLoginSuccessWithSign(signData) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo esc_url(home_url('/oauth/mixauthqq/callback')); ?>';
                
                const stateInput = document.createElement('input');
                stateInput.type = 'hidden';
                stateInput.name = 'state';
                stateInput.value = '<?php echo esc_js($state); ?>';
                
                const signInput = document.createElement('input');
                signInput.type = 'hidden';
                signInput.name = 'sign';
                signInput.value = signData;
                
                form.appendChild(stateInput);
                form.appendChild(signInput);
                
                document.body.appendChild(form);
                form.submit();
            }

            // 显示重试选项
            function showRetryOption() {
                const retryHtml = '<div style="margin-top: 15px;"><button class="retry-btn" onclick="location.reload()">重新获取二维码</button></div>';
                qrcodeDiv.innerHTML += retryHtml;
            }

            // 页面加载时生成二维码
            window.addEventListener('load', generateQRCode);
            window.addEventListener('beforeunload', stopPolling);
        }
        
        // iframe模式初始化
        function initIframeMode() {
            // 监听来自MixAuth iframe的消息
            window.addEventListener('message', function(event) {
                // 验证消息来源
                const serverOrigin = new URL('<?php echo esc_js($server_url); ?>').origin;
                if (event.origin !== serverOrigin) {
                    return;
                }
                
                const message = event.data;
                
                if (message && message.type === 'mixauth_login_result') {
                    const loginResult = message.data;
                    
                    // 将结果发送到回调处理页面
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?php echo esc_url(home_url('/oauth/mixauthqq/callback')); ?>';
                    
                    const stateInput = document.createElement('input');
                    stateInput.type = 'hidden';
                    stateInput.name = 'state';
                    stateInput.value = '<?php echo esc_js($state); ?>';
                    
                    // 处理不同格式的登录结果
                    if (typeof loginResult === 'string') {
                        // 如果是签名字符串
                        const signInput = document.createElement('input');
                        signInput.type = 'hidden';
                        signInput.name = 'sign';
                        signInput.value = loginResult;
                        form.appendChild(signInput);
                    } else {
                        // 如果是JSON数据
                        const dataInput = document.createElement('input');
                        dataInput.type = 'hidden';
                        dataInput.name = 'mixauth_result';
                        dataInput.value = JSON.stringify(loginResult);
                        form.appendChild(dataInput);
                    }
                    
                    form.appendChild(stateInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>