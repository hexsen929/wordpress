$(function () {
    /*替换友情链接页面图片*/
    $(".links-card ul.list-inline img").each(function () {
        var dataSrc = $(this).attr("data-src");
        if (dataSrc == "") {
            var href = $(this).closest('a').attr("href");
            if (href && !href.includes("/go/")) {
                var newSrc = "https://api.hexsen.com/api/favicon.php?url=" + href;
                $(this).attr("src", newSrc);
            }
        }
    });

    /* 设为私密留言! */
    $(document).on("click", ".sm", commentPrivate);
    function commentPrivate() {
        var $this = $(this);
        if ($this.hasClass('private_now')) {
            notyf('您之前已设过私密评论', 'warning');
            return false;
        } else {
            $this.addClass('private_now');
            var idp = $this.data('idp'),
                actionp = $this.data('actionp'),
                rateHolderp = $this.children('.has_set_private'),
                ajax_data = {
                    action: "mrhe_private",
                    p_id: idp,
                    p_action: actionp
                };
            $.post("/wp-admin/admin-ajax.php", ajax_data, function (data) {
                rateHolderp.html(data);
            });
            return false;
        }
    }
});

// if ($('#commentform').length > 0) {
//     /*https://ip.useragentinfo.com/json
//      *https://whois.pconline.com.cn/ipJson.jsp
//      *https://v.api.aa1.cn/api/myip/index.php?aa1=json
//      *https://tbip.alicdn.com/api/queryip
//      *https://r.inews.qq.com/api/ip2city
//      *https://forge.speedtest.cn/api/location/info
//      *https://pubstatic.b0.upaiyun.com/?_upnode&t=1685039986399
//      *https://www.taobao.com/help/getip.php?callback=ipCallback
//      *https://cf-ns.com/cdn-cgi/trace 使用这个需要重写js
//      *https://api.ip.sb/geoip 国外ip
//      *https://pro.ip-api.com/json/?fields=16985625&key=EEKS6bLi6D91G1p 国外ip
//      *https://ipinfo.io/json?token=230de83c74e3f3 国外ip
//      *https://service.hexsen.com/getip.php
//      *https://ip.js.cool/ip 国外
//      *https://ip123.in/ 国外
//      */
//     $.getJSON('https://forge.speedtest.cn/api/location/info', function (data) {
//         var ip = data.ip;
//         $('#commentform #submit').click(function () {
//             $.post(_win.ajax_url, {
//                 action: 'update_comment_ip',
//                 ip: ip
//             },
//                 function (response) {
//                     console.log(response);
//                 });
//         });
//     });
// }

//判断 windows11
// function is_win11() {
//     if (navigator.userAgentData != undefined) {
//         if (navigator.userAgentData.getHighEntropyValues != undefined) {
//             navigator.userAgentData.getHighEntropyValues(["platformVersion"]).then(ua => {
//                 if (navigator.userAgentData.platform === "Windows") {
//                     const majorPlatformVersion = parseInt(ua.platformVersion.split('.')[0]);
//                     if (majorPlatformVersion >= 13) {
//                         // const commentForm = document.getElementById('commentform');
//                         // const input = document.createElement('input');
//                         // input.type = 'hidden';
//                         // input.name = 'os_type';
//                         // input.value = 'win11';
//                         // commentForm.appendChild(input);
//                         document.cookie = "win11=true;path=/";
//                     }
//                 }
//             });
//         }
//     }
// }
// is_win11();//调用函数

// $(function () {
//     const body = $('body');
//     if (body.hasClass('single')) {
//         const push_url = encodeURI(window.location.origin + window.location.pathname);
//         const cookieName = `${push_url}=Mrhe_Baidu_Record`;
//         const $lastSpan = $('.px12-sm.muted-2-color.text-ellipsis span, .forum-article-meta .meta-left span').last();
//         const $recordSpan = $('<span id="Mrhe_Baidu_Record">正在检测百度是否收录</span>').appendTo($lastSpan);

//         function updateRecordSpan(color, text) {
//             $recordSpan.css('color', color).html(text);
//         }
//         /* 获取本篇文章百度收录情况 */
//         $.ajax({
//             url: _win.ajax_url,
//             type: 'POST',
//             dataType: 'json',
//             data: {
//                 action: 'mrhe_baidu_record',
//                 site: push_url
//             },
//             success(res) {
//                 if (res.data && res.data === '已收录') {
//                     updateRecordSpan('#67C23A', '已收录');
//                 } else {
//                     //if(document.cookie.indexOf(cookieName) >= 0){
//                     if (document.cookie.includes(cookieName)) {
//                         updateRecordSpan('#67C23A', '已自动提交');
//                         return false;
//                     }
//                     /* 如果填写了Token，则自动推送给百度 */
//                     if (_mrhe.BAIDU_PUSH) {
//                         updateRecordSpan('#E6A23C', '未收录，推送中...');
//                         setTimeout(function () {
//                             $.ajax({
//                                 url: _win.ajax_url,
//                                 type: 'POST',
//                                 dataType: 'json',
//                                 data: {
//                                     action: 'mrhe_baidu_push',
//                                     /*domain: window.location.protocol + '//' + window.location.hostname,*/
//                                     url: push_url
//                                 },
//                                 success(res) {
//                                     if (res.data.error) {
//                                         if (res.data.message == "over quota") {
//                                             updateRecordSpan('#F56C6C', '推送额度不足！');
//                                         } else {
//                                             updateRecordSpan('#F56C6C', res.data.message);
//                                         }
//                                     } else {
//                                         updateRecordSpan('#67C23A', '推送成功！');
//                                         const now = new Date();
//                                         const oneDay = 24 * 60 * 60 * 1000; // 一天的毫秒数
//                                         const cookieExpires = new Date(now.getTime() + oneDay).toUTCString(); /* 过期时间的UTC字符串格式*/
//                                         document.cookie = `${cookieName}; expires=${cookieExpires}`;
//                                     }
//                                 }
//                             });
//                         }, 1000);
//                     } else {
//                         const url = `https://ziyuan.baidu.com/linksubmit/url?sitename=${push_url}`;
//                         $recordSpan.html(`<a target="_blank" href="${url}" rel="noopener noreferrer nofollow" style="color: #F56C6C">未收录，提交收录</a>`);
//                     }
//                 }
//             }
//         });
//     }
// });

//将上面代码进行重构
$(function () {
    const body = $('body');
    if (!body.hasClass('single')) return;
    
    const post_id = _win.views; // 假设这里是文章ID
    const $lastSpan = $('.px12-sm.muted-2-color.text-ellipsis span, .forum-article-meta .meta-left span').last();
    const $recordSpan = $('<span id="Mrhe_Baidu_Record"> 正在检测百度是否收录</span>').appendTo($lastSpan);
    
    const updateRecordSpan = (color, text) => {
        $recordSpan.css('color', color).html(text);
    };
    
    // 从localStorage读取收录状态
    const getCachedStatus = () => {
        const cached = localStorage.getItem('baidu_record_' + post_id);
        if (cached) {
            try {
                return JSON.parse(cached);
            } catch (e) {
                return null;
            }
        }
        return null;
    };

    // 保存收录状态到localStorage
    const saveCachedStatus = (status) => {
        const data = {
            status: status,
            timestamp: Date.now()
        };
        localStorage.setItem('baidu_record_' + post_id, JSON.stringify(data));
    };
    
    const checkBaiduRecord = async () => {
        try {
            const res = await $.ajax({
                url: _win.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'mrhe_baidu_record',
                    push: post_id
                }
            });
            
            return res.data;
        } catch (error) {
            console.error('Error fetching Baidu record:', error);
            return false;
        }
    };
    
    const pushToBaidu = async () => {
        try {
            const res = await $.ajax({
                url: _win.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'mrhe_baidu_push',
                    push: post_id
                }
            });
            return res;
        } catch (error) {
            // 静默处理错误，不在 console 显示
            return { error: true, message: ' 推送服务暂不可用' };
        }
    };
    
    (async () => {
        // 首先检查localStorage中保存的状态
        const cachedStatus = getCachedStatus();
        
        if (cachedStatus) {
            const { status, timestamp } = cachedStatus;
            const now = Date.now();
            const oneDay = 24 * 60 * 60 * 1000;
            
            // 如果是已收录状态，直接显示（永久有效）
            if (status === 'recorded') {
                updateRecordSpan('#67C23A', ' 已收录');
                return;
            }
            
            // 如果是已提交状态，检查是否在24小时内
            if (status === 'submitted' && (now - timestamp) < oneDay) {
                updateRecordSpan('#67C23A', ' 已自动提交');
                return;
            }
        }
        
        // localStorage中没有有效状态，进行百度收录检查
        const Baidures = await checkBaiduRecord();
        
        if (Baidures === "已收录") {
            updateRecordSpan('#67C23A', ' 已收录');
            // 保存已收录状态到localStorage（永久保存）
            saveCachedStatus('recorded');
        } else if (Baidures === "未收录") {
            if (_mrhe.BAIDU_PUSH) {
                updateRecordSpan('#E6A23C', ' 未收录，推送中...');
                const res = await pushToBaidu();
                if (res.error) {
                    // 错误响应：{ error: true, msg: "..." }
                    updateRecordSpan('#F56C6C', res.msg || ' 推送失败');
                } else {
                    // 成功响应：{ error: false, domain: ..., url: ..., data: {百度API响应} }
                    const baiduResult = res.data || {};
                    if (baiduResult.message === 'over quota') {
                        updateRecordSpan('#F56C6C', ' 推送额度不足！');
                    } else if (baiduResult.error) {
                        // 百度API返回错误
                        updateRecordSpan('#F56C6C', baiduResult.message || ' 推送失败');
                    } else {
                        updateRecordSpan('#67C23A', ' 推送成功！');
                        // 保存已提交状态到localStorage（带时间戳）
                        saveCachedStatus('submitted');
                    }
                }
            } else {
                const push_url = encodeURI(window.location.origin + window.location.pathname);
                const url = `https://ziyuan.baidu.com/linksubmit/url?sitename=${push_url}`;
                $recordSpan.html(`<a target="_blank" href="${url}" rel="noopener noreferrer nofollow" style="color: #F56C6C"> 未收录，提交收录</a>`);
            }
        } else {
            // 其他状态（如检查失败等）
            updateRecordSpan('#F56C6C', ' ' + Baidures);
        }
    })();
});

$(function () {
    // 从多级JSON数据中获取值的函数
    function zib_get_array_value_by_path(array, path) {
        const keys = path.split('.');
        let current = array;
        
        for (const key of keys) {
            if (!current || typeof current !== 'object' || !(key in current)) {
                return null;
            }
            current = current[key];
        }
        
        return current;
    }

    // IP相关函数
    function initIP() {
        
        // 只在评论表单存在且启用了自定义IP API时执行
        if (!$('#commentform').length || !window._mrhe.IP_APIS) {
            return;
        }
        
        // 获取IP地址
        function getIP(api) {
            
            // 提取IP的函数
            function extractIP(data) {
                try {
                    return zib_get_array_value_by_path(data, "ip");
                } catch (error) {
                    return null;
                }
            }
            
            // 处理API响应
            function handleResponse(data) {
                const ip = extractIP(data);
                if (ip) {
                    bindSubmitEvent(ip);
                } else {
                    // 如果当前API获取失败，尝试下一个
                    const nextIndex = window._mrhe.IP_APIS.indexOf(api) + 1;
                    if (nextIndex < window._mrhe.IP_APIS.length) {
                        getIP(window._mrhe.IP_APIS[nextIndex]);
                    }
                }
            }
            
            // 发起请求
            $.getJSON(api.url)
                .done(handleResponse)
                .fail(function(error) {
                    console.warn('API请求失败:', error);
                    // 尝试下一个API
                    const nextIndex = window._mrhe.IP_APIS.indexOf(api) + 1;
                    if (nextIndex < window._mrhe.IP_APIS.length) {
                        getIP(window._mrhe.IP_APIS[nextIndex]);
                    }
                });
        }
        
        // 绑定提交事件
        function bindSubmitEvent(ip) {
            $('#commentform #submit').click(function() {
                $.post(_win.ajax_url, {
                    action: 'update_comment_ip',
                    ip: ip
                }, function(response) {

                });
            });
        }
        
        // 开始尝试第一个API
        if (window._mrhe.IP_APIS && window._mrhe.IP_APIS.length > 0) {
            getIP(window._mrhe.IP_APIS[0]);
        }
    }
    
    // 页面加载完成后初始化所有功能
    $(document).ready(function() {
        initIP();
    });
});