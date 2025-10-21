<?php
/*
 * 现代化外链跳转页面
 * 保留原有PHP逻辑和安全检查API
 */

// PHP 8+ 兼容性修复：使用 null 合并运算符
if (
    strlen($_SERVER['REQUEST_URI'] ?? '') > 384 ||
    strpos($_SERVER['REQUEST_URI'] ?? '', 'eval(') !== false ||
    strpos($_SERVER['REQUEST_URI'] ?? '', 'base64') !== false
) {
    @header('HTTP/1.1 414 Request-URI Too Long');
    @header('Status: 414 Request-URI Too Long');
    @header('Connection: Close');
    @exit;
}

//通过QUERY_STRING取得完整的传入数据，然后取得url=之后的所有值，兼容性更好
$cars = array(
    'racknerd' => 'https://my.racknerd.com/aff.php?aff=170',
    'racknerd2020' => 'https://my.racknerd.com/aff.php?aff=170&pid=57',
    'vultr25' => 'https://www.vultr.com/?ref=9755598-9J',
    'vultr' => 'https://www.vultr.com/?ref=9755594',
    'baidu' => 'https://www.baidu.com',
    'racknerd768' => 'https://my.racknerd.com/aff.php?aff=170&pid=113',
    'racknerd15' => 'https://my.racknerd.com/aff.php?aff=170&pid=114',
    'racknerd2k201' => 'https://my.racknerd.com/aff.php?aff=170&pid=120',
    'racknerd2k202' => 'https://my.racknerd.com/aff.php?aff=170&pid=121',
    'racknerd2k203' => 'https://my.racknerd.com/aff.php?aff=170&pid=122',
    'racknerd2flashsales1' => 'https://my.racknerd.com/aff.php?aff=170&pid=99',
    'racknerd2flashsales2' => 'https://my.racknerd.com/aff.php?aff=170&pid=98',
    'racknerddyp1' => 'https://my.racknerd.com/aff.php?aff=170&pid=61',
    'racknerddyp2' => 'https://my.racknerd.com/aff.php?aff=170&pid=62',
    'racknerddyp3' => 'https://my.racknerd.com/aff.php?aff=170&pid=63',
    'racknerddyp4' => 'https://my.racknerd.com/aff.php?aff=170&pid=64',
    'racknerd4' => 'https://my.racknerd.com/aff.php?aff=170&pid=4',
    'racknerd5' => 'https://my.racknerd.com/aff.php?aff=170&pid=5',
    'racknerd6' => 'https://my.racknerd.com/aff.php?aff=170&pid=6',
    'racknerd7' => 'https://my.racknerd.com/aff.php?aff=170&pid=7',
    'newwebsite1' => 'https://my.racknerd.com/aff.php?aff=170&pid=128',
    'newwebsite2' => 'https://my.racknerd.com/aff.php?aff=170&pid=129',
    'newwebsite3' => 'https://my.racknerd.com/aff.php?aff=170&pid=130',
    'juneflashsale1g' => 'https://my.racknerd.com/aff.php?aff=170&pid=236',
    'juneflashsale2g' => 'https://my.racknerd.com/aff.php?aff=170&pid=237',
    'dc0518g' => 'https://my.racknerd.com/aff.php?aff=170&pid=213',
    'dc0528g' => 'https://my.racknerd.com/aff.php?aff=170&pid=214',
    'dc053g' => 'https://my.racknerd.com/aff.php?aff=170&pid=234',
    'dc0538g' => 'https://my.racknerd.com/aff.php?aff=170&pid=215',
);

//通过QUERY_STRING取得完整的传入数据，然后取得url=之后的所有值，兼容性更好
@session_start();
$t_url = !empty($_SESSION['GOLINK']) ? $_SESSION['GOLINK'] : preg_replace('/^url=(.*)$/i', '$1', $_SERVER['QUERY_STRING'] ?? '');

//此处可以自定义一些特别的外链，不需要可以删除以下5行
foreach($cars as $k=>$val){
    if($t_url == $k ) {
        $t_url = $val;
    }
}

//数据处理
if (!empty($t_url)) {
    //判断取值是否加密
    if ($t_url == base64_encode(base64_decode($t_url))) {
        $t_url = base64_decode($t_url);
    }

    //防止xss
    $t_url = htmlspecialchars($t_url, ENT_QUOTES, 'UTF-8');
    $t_url = str_replace(array("'", '"'), array('&#39;', '&#34;'), $t_url);
    $t_url = str_replace(array("\r", "\n"), array('&#13;', '&#10;'), $t_url);
    $t_url = str_replace(array("\t"), array('&#9;'), $t_url);
    $t_url = str_replace(array("\x0B"), array('&#11;'), $t_url);
    $t_url = str_replace(array("\x0C"), array('&#12;'), $t_url);
    $t_url = str_replace(array("\x0D"), array('&#13;'), $t_url);

    //对取值进行网址校验和判断
    preg_match('/^(http|https|thunder|qqdl|ed2k|Flashget|qbrowser):\/\//i', $t_url, $matches);
    if ($matches) {
        $url   = $t_url;
        $title = '页面加载中,请稍候...';
    } else {
        preg_match('/\./i', $t_url, $matche);
        if ($matche) {
            $url   = 'http://' . $t_url;
            $title = '页面加载中,请稍候...';
        } else {
            $url   = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $title = '参数错误，正在返回首页...';
        }
    }
} else {
    $title = '参数缺失，正在返回首页...';
    $url   = ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

//禁止其它网站跳转此页面
$host    = zib_get_url_top_host($_SERVER['HTTP_HOST']);
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
if (!empty($referer) && !preg_match('/' . preg_quote($host, '/') . '/i', $referer)) {
    $url   = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    $title = '非法请求，正在返回首页...';
}

//验证nonce
if (_pz('go_link_nonce_s')) {
    $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';
    if (empty($nonce) || !wp_verify_nonce($nonce, 'go_link_nonce')) {
        $url   = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        $title = '非法请求，正在返回首页...';
    }
}

$url = str_replace(['&amp;amp;', '&amp;'], '&', $url);

// 在$url变量定义后添加
function getDomainFromUrl($url) {
    $parsed = parse_url($url);
    if (isset($parsed['host'])) {
        // 只返回协议和主机名
        return $parsed['scheme'] . '://' . $parsed['host'];
    }
    return $url;
}

function set_data() {
    // 修复：使用条件运算符检查函数是否存在
    if (function_exists('_mrhe') && _mrhe('google_check_url')) {
        $checkname = '谷歌（Google）';
        $filename = 'google_check_url';
    } else {
        $checkname = '腾讯（Tencent）';
        $filename = 'tencent_check_url';
    }
    return array($checkname, $filename);
}

$result = set_data();
$checkname = $result[0];
$filename = $result[1];
?>
<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta http-equiv="Cache-Control" content="no-transform" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <title><?php echo $title; ?></title>
    <?php if(function_exists('wp_head')) wp_head();?>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #6366f1;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --border-radius: 16px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: "Open Sans", Arial, "Hiragino Sans GB", "Microsoft YaHei", "微软雅黑", "STHeiti", "WenQuanYi Micro Hei", SimSun, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* 动态背景 */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: backgroundMove 60s linear infinite;
            z-index: 0;
        }

        @keyframes backgroundMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        /* 浮动气泡背景 */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }

        .bubble:nth-child(1) {
            width: 80px;
            height: 80px;
            left: 10%;
            animation-duration: 25s;
            animation-delay: 0s;
        }

        .bubble:nth-child(2) {
            width: 120px;
            height: 120px;
            right: 15%;
            animation-duration: 30s;
            animation-delay: 2s;
        }

        .bubble:nth-child(3) {
            width: 60px;
            height: 60px;
            left: 70%;
            animation-duration: 20s;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.5;
            }
            50% {
                transform: translateY(-100px) rotate(180deg);
                opacity: 0.8;
            }
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px;
            max-width: 500px;
            width: 100%;
            box-shadow: var(--shadow-xl);
            position: relative;
            z-index: 1;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 32px;
            animation: fadeIn 0.8s ease-out 0.2s backwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--primary-color), #a78bfa);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-lg);
            animation: pulse 2s infinite;
            position: relative;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        /* 自定义图标 */
        .logo::after {
            content: '🛡️';
            font-size: 36px;
        }

        h1 {
            font-size: 24px;
            color: var(--text-primary);
            margin-bottom: 12px;
            font-weight: 700;
            animation: fadeIn 0.8s ease-out 0.3s backwards;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 32px;
            animation: fadeIn 0.8s ease-out 0.4s backwards;
        }

        .status-card {
            background: var(--light-color);
            border-radius: var(--border-radius);
            padding: 24px;
            margin-bottom: 32px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            animation: fadeIn 0.8s ease-out 0.5s backwards;
            position: relative;
            overflow: hidden;
        }

        .status-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .status-card.checking {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(99, 102, 241, 0.1));
        }

        .status-card.safe {
            border-color: var(--success-color);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.1));
        }

        .status-card.warning {
            border-color: var(--warning-color);
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05), rgba(245, 158, 11, 0.1));
        }

        .status-card.danger {
            border-color: var(--danger-color);
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(239, 68, 68, 0.1));
        }

        .status-card.error {
            border-color: #78909c;
            background: linear-gradient(135deg, rgba(120, 144, 156, 0.05), rgba(120, 144, 156, 0.1));
        }

        /* 状态图标使用CSS绘制 */
        .status-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 24px;
            transition: all 0.3s ease;
            position: relative;
        }

        .status-icon-loading {
            background: linear-gradient(135deg, var(--primary-color), #a78bfa);
            animation: rotate 1s linear infinite;
        }

        .status-icon-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid white;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
        }

        .status-icon-check {
            background: linear-gradient(135deg, var(--success-color), #34d399);
            animation: checkmark 0.5s ease-out;
        }

        .status-icon-check::after {
            content: '✓';
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .status-icon-warning {
            background: linear-gradient(135deg, var(--warning-color), #fbbf24);
        }

        .status-icon-warning::after {
            content: '!';
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .status-icon-danger {
            background: linear-gradient(135deg, var(--danger-color), #f87171);
            animation: shake 0.5s ease-out;
        }

        .status-icon-danger::after {
            content: '✕';
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .status-icon-error {
            background: linear-gradient(135deg, #78909c, #90a4ae);
        }

        .status-icon-error::after {
            content: '?';
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes checkmark {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .status-text {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .status-desc {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .url-display {
            background: var(--light-color);
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            word-break: break-all;
            font-size: 14px;
            color: var(--text-secondary);
            animation: fadeIn 0.8s ease-out 0.6s backwards;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .url-display::before {
            content: '🔗';
            flex-shrink: 0;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            animation: fadeIn 0.8s ease-out 0.7s backwards;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #a78bfa);
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: white;
            color: var(--text-primary);
            border: 2px solid #e5e7eb;
        }

        .btn-secondary:hover {
            background: var(--light-color);
            transform: translateY(-2px);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .countdown {
            display: inline-block;
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-color);
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 700;
            margin-left: 4px;
        }

        .provider-info {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: var(--text-secondary);
            animation: fadeIn 0.8s ease-out 0.8s backwards;
        }

        /* 隐藏元素 */
        .hidden {
            display: none !important;
        }

        /* 移动端适配 */
        @media (max-width: 640px) {
            body {
                padding: 16px;
            }

            .container {
                padding: 32px 24px;
                border-radius: 20px;
            }

            h1 {
                font-size: 20px;
            }

            .logo {
                width: 64px;
                height: 64px;
            }

            .logo::after {
                font-size: 28px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .url-display {
                font-size: 12px;
                padding: 12px;
            }
        }

        /* 深色模式支持 */
        @media (prefers-color-scheme: dark) {
            :root {
                --text-primary: #f9fafb;
                --text-secondary: #d1d5db;
                --light-color: #1f2937;
                --dark-color: #f9fafb;
            }

            body {
                background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            }

            .container {
                background: rgba(30, 41, 59, 0.95);
            }

            .status-card {
                background: #1e293b;
            }

            .url-display {
                background: #1e293b;
                border-color: #374151;
            }

            .btn-secondary {
                background: #1e293b;
                color: var(--text-primary);
                border-color: #374151;
            }

            .btn-secondary:hover {
                background: #374151;
            }

            .provider-info {
                border-top-color: #374151;
            }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <!-- 浮动气泡背景 -->
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>

    <div class="container">
        <div class="logo-container">
            <div class="logo"></div>
            <h1>正在离开本站</h1>
            <p class="subtitle">安全检查服务由 <strong><?php echo $checkname; ?></strong> 提供</p>
        </div>

        <div class="status-card checking" id="alert">
            <div class="status-icon status-icon-loading" id="alert-i"></div>
            <div class="status-text" id="check_url">正在检查目标网站安全性...</div>
            <div class="status-desc" id="status-desc">请稍候，正在为您进行安全检测</div>
        </div>

        <div class="url-display">
            <span><?php echo getDomainFromUrl($url); ?></span>
        </div>

        <div class="action-buttons">
            <button class="btn btn-secondary" onclick="history.back()">
                ← 返回上页
            </button>
            <a href="javascript:void(0)" class="btn btn-primary" id="leave">
                <span id="leave-text">继续访问 →</span>
            </a>
        </div>

        <div class="provider-info">
            ℹ️ 提示：我们会检查所有外部链接的安全性
        </div>
    </div>

    <script>
    // 保留原有的防跳转检查
    function link_jump() {
        var MyHOST = new RegExp("<?php echo $_SERVER['HTTP_HOST'] ?? 'localhost'; ?>");
        if (!MyHOST.test(document.referrer)) {
            alert('禁止非法使用本站跳转！');
            location.href = "https://" + MyHOST;
        }
    }

    var url = "<?php echo $url;?>",
        autog = 0,
        gototurl;

    // 使用原有的API调用逻辑
    $.post('<?php echo get_stylesheet_directory_uri();?>/<?php echo $filename ?>.php?url=' + url)
    .done(function(data) {
        const check_url = $('#check_url'),
              alertc = $('#alert'),
              alert_i = $('#alert-i'),
              leave = $('#leave'),
              leave_text = $('#leave-text'),
              status_desc = $('#status-desc');
    
        // 移除加载状态
        alertc.removeClass('checking');
        alert_i.removeClass('status-icon-loading');
    
        // 修复：同时检查两种API的响应格式
        if (!data || (!data.result && !data.type)) {
            // 检测服务器错误
            check_url.text('检测服务器错误！请检查网址是否正确。');
            status_desc.text('无法连接到安全检测服务');
            alertc.addClass('error');
            alert_i.addClass('status-icon-error');
            leave_text.text('手动前往');
            gototurl = 'javascript:history.go(-1)';
        } else {
            var info = <?php echo json_encode($cars); ?>;
            var isWhitelisted = false;
            
            // 检查白名单
            $.each(info, function(i, n){
                if(n == url){
                    check_url.text('认证网站，访问安全');
                    status_desc.text('此网站已通过安全认证');
                    alertc.addClass('safe');
                    alert_i.addClass('status-icon-check');
                    leave_text.html('5s 后自动跳转');
                    autog = 1;
                    isWhitelisted = true;
                    return false;
                }
            });
    
            // 如果不在白名单中，处理API响应
            if (!isWhitelisted) {
                // 腾讯API响应处理
                if (data.result == "域名正常") {
                    check_url.text(data.reason || '暂未发现风险！');
                    status_desc.text('腾讯安全检测通过');
                    alertc.addClass('safe');
                    alert_i.addClass('status-icon-check');
                    leave_text.text('5s 后自动跳转');
                    autog = 1;
                } else if (data.result == "域名拦截") {
                    check_url.text(data.reason || '监测到站点存在风险');
                    status_desc.text('建议谨慎访问此网站');
                    alertc.addClass('danger');
                    alert_i.addClass('status-icon-danger');
                    leave_text.text('不建议访问');
                } 
                // Google API响应处理
                else if (data.type == "safe") {
                    check_url.text('暂未发现风险！');
                    status_desc.text('Google 安全检测通过');
                    alertc.addClass('safe');
                    alert_i.addClass('status-icon-check');
                    leave_text.text('5s 后自动跳转');
                    autog = 1;
                } else if (data.type && data.type !== "safe") {
                    check_url.text('监测到站点存在风险');
                    status_desc.text('Google 检测发现安全问题');
                    alertc.addClass('danger');
                    alert_i.addClass('status-icon-danger');
                    leave_text.text('点击继续前往');
                } else {
                    // 默认情况 - 数据格式未知但有响应
                    check_url.text('安全状态未知');
                    status_desc.text('无法确定网站安全性');
                    alertc.addClass('warning');
                    alert_i.addClass('status-icon-warning');
                    leave_text.text('点击继续前往');
                }
            }
        }
        
        // 设置跳转链接
        if (url.length > 1) {
            gototurl = url;
        } else {
            gototurl = 'javascript:history.go(-1)';
        }
        
        leave.attr('href', gototurl);
        
        // 自动跳转逻辑
        if (autog) {
            var time = 5;
            var interval = setInterval(function() {
                time -= 1;
                leave_text.html(time + 's 后自动跳转');
                if (time <= 0) {
                    location.href = gototurl;
                    clearInterval(interval);
                    leave_text.text('正在跳转...');
                }
            }, 1000);
        }
    })
    .fail(function() {
        // AJAX 请求失败
        const check_url = $('#check_url'),
              alertc = $('#alert'),
              alert_i = $('#alert-i'),
              leave_text = $('#leave-text'),
              status_desc = $('#status-desc');

        alertc.removeClass('checking');
        alert_i.removeClass('status-icon-loading');
        
        check_url.text('网络连接错误');
        status_desc.text('无法连接到安全检测服务');
        alertc.addClass('warning');
        alert_i.addClass('status-icon-warning');
        leave_text.text('点击继续前往');
        
        $('#leave').attr('href', url.length > 1 ? url : 'javascript:history.go(-1)');
    });

    //延时50S关闭跳转页面，用于文件下载后不会关闭跳转页的问题
    setTimeout(function(){
        window.opener = null;
        window.close();
    }, 50000);
    </script>
</body>
</html>
