/**
 * Cookie操作工具函数
 */
function handleCookie(action, name, value = '', days = 1) {
    switch (action) {
        case 'set':
            try {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                document.cookie = name + "=" + encodeURIComponent(value) + 
                                "; expires=" + date.toUTCString() + 
                                "; path=/" + 
                                "; SameSite=Lax";
                return true;
            } catch (e) {
                console.error('Cookie设置失败:', e);
                return false;
            }

        case 'get':
            try {
                const nameEQ = name + "=";
                const ca = document.cookie.split(';');
                for(let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) === 0) {
                        return decodeURIComponent(c.substring(nameEQ.length, c.length));
                    }
                }
                return null;
            } catch (e) {
                console.error('Cookie获取失败:', e);
                return null;
            }

        case 'isValid':
            return handleCookie('get', name) !== null;

        default:
            console.error('未知的Cookie操作类型');
            return null;
    }
}

//修改子比弹窗登陆页面免密码登陆动态获取 _wpnonce
document.addEventListener('DOMContentLoaded', function () {
    // 监听点击“发送验证码”的按钮
    document.querySelectorAll('.captchsubmit').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var actionName = btn.getAttribute('form-action');

            // 在按钮所在的容器里查找对应的 sign_nonce input
            var container = btn.closest('.line-form');
            var nonceField = container.querySelector('.sign_nonce');

            if (!nonceField) {
                console.error("没有找到对应的 sign_nonce 隐藏字段");
                return;
            }

            fetch('/wp-admin/admin-ajax.php?action=get_sign_nonce&action_name=' + actionName)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        nonceField.value = data.data;
                    }
                });
        });
    });
});

(function($) {
    'use strict';

    // 判断是否是移动端
    function is_mobile() {
        return /Android|webOS|iPhone|iPod|BlackBerry/i.test(navigator.userAgent);
    }

    // 移动端移除target属性
    if(is_mobile()){
        $('a').removeAttr('target');
    }

    // 切换logo图片
    function switch_logo_img() {
        const switchImgs = $('img[switch-src]');
        switchImgs.each(function () {
            const currentImg = $(this);
            const currentSrc = currentImg.attr('src');
            const currentSwitchSrc = currentImg.attr('switch-src');
            currentImg.attr('src', currentSwitchSrc);
            currentImg.attr('switch-src', currentSrc);
        });
    }
    
    // 执行域名检测
    function checkDomain() {
        try {
            // 从配置中提取官方站点列表
            const officialSitesConfig = window._mrhe?.OFFICIALSITE || [];
            
            // 如果没有配置，则不启用检测
            if (!officialSitesConfig || officialSitesConfig.length === 0) {
                return;
            }
            
            // 提取domain字段的值
            const officialSites = officialSitesConfig.map(item => item.domain).filter(Boolean);
    
            const currentUrl = window.location.href;
            const currentDomain = window.location.hostname;
    
            // 提取官方域名
            const officialDomains = officialSites
                .map(site => {
                    try {
                        return new URL(site).hostname;
                    } catch {
                        return null;
                    }
                })
                .filter(Boolean);
    
            const isOfficialDomain = officialDomains.includes(currentDomain);
            const isLocalDev = ["localhost", "127.0.0.1"].includes(currentDomain);
    
            // 非官方域名，且不是本地开发环境时警告
            if (!isOfficialDomain && !isLocalDev) {
                const shouldRedirect = confirm(
                    `⚠️ 域名安全警告\n\n` +
                    `您当前访问的域名：${currentDomain}\n` +
                    `官方域名：${officialDomains.join(", ")}\n\n` +
                    `您可能正在访问非官方网站，存在安全风险！\n\n` +
                    `点击 "确定" 跳转到官方网站\n` +
                    `点击 "取消" 继续访问当前网站（不推荐）`
                );
    
                if (shouldRedirect) {
                    const currentPath = window.location.pathname + window.location.search + window.location.hash;
                    const officialPageUrl = officialSites[0] + currentPath;
                    window.location.href = officialPageUrl;
                }
            }
        } catch (error) {
            console.warn("域名检测失败:", error);
        }
    }
    
    // 页面加载完成后执行
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", checkDomain);
    } else {
        checkDomain();
    }


    // 主题相关函数
    function initTheme() {
        function updateTimeBasedTheme() {
            const hour = new Date().getHours();
            const currentTheme = handleCookie('get', 'theme_mode') || "white-theme";
            const newTheme = hour > 7 && hour < 22 ? "white-theme" : "dark-theme";

            if (handleCookie('get', 'toggle-theme') === "true") {
                return;
            }

            if (currentTheme !== newTheme) {
                handleCookie('set', 'theme_mode', newTheme, 1);
                switch_logo_img();
                
                if (newTheme === 'dark-theme') {
                    document.body.classList.add('dark-theme');
                } else {
                    document.body.classList.remove('dark-theme');
                }
                
                notyf(`已切换为${newTheme === "dark-theme" ? "夜间" : "白天"}模式`);
            }
        }

        function updateThemeOnTimeChange() {
            const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
            if (mediaQuery.media) {
                mediaQuery.addEventListener("change", e => {
                    const newTheme = e.matches ? "dark-theme" : "white-theme";
                    handleCookie('set', 'theme_mode', newTheme, 1);
                    
                    if (e.matches) {
                        document.body.classList.add('dark-theme');
                        notyf("已切换为夜间模式");
                    } else {
                        document.body.classList.remove('dark-theme');
                        notyf("已切换为白天模式");
                    }
                    
                    switch_logo_img();
                });
            } else {
                if (handleCookie('get', 'toggle-theme') !== "true") {
                    setInterval(updateTimeBasedTheme, 60000);
                }
            }
        }

        // 初始化主题
        updateThemeOnTimeChange();

        // 绑定主题切换事件
        $('.toggle-theme').click(function () {
            handleCookie('set', 'toggle-theme', 'true', 1);
        });
    }

    // 翻译功能初始化
    function initTranslation() {
        // 检查翻译配置
        const translationConfig = window._mrhe?.TRANSLATION;
        if (!translationConfig || !translationConfig.enabled) {
            return;
        }

        // 动态加载 translate.js
        const script = document.createElement('script');
        script.src = translationConfig.cdn_url;
        script.onload = function() {
            // translate.js 加载完成后进行初始化
            if (typeof translate === 'undefined') {
                console.error('translate.js 加载失败');
                return;
            }

            // 设置翻译服务
            translate.service.use(translationConfig.service);

            // 设置本地语言
            translate.language.setLocal(translationConfig.default_language);

            // 自动检测用户语言
            if (translationConfig.auto_detect) {
                translate.setAutoDiscriminateLocalLanguage();
            }

            if (translationConfig.translate_js) {
                translate.listener.start();
            }

            // 隐藏默认语言选择框（我们使用自定义按钮）
            translate.selectLanguageTag.show = false;

            // 应用忽略设置
            if (translationConfig.ignore) {
                // 忽略HTML标签
                if (translationConfig.ignore.tags && translationConfig.ignore.tags.length > 0) {
                    translationConfig.ignore.tags.forEach(function(tag) {
                        if (tag && tag.trim()) {
                            translate.ignore.tag.push(tag.trim());
                        }
                    });
                }

                // 忽略CSS类名（translate.js默认已忽略 class="ignore"）
                if (translationConfig.ignore.classes && translationConfig.ignore.classes.length > 0) {
                    translationConfig.ignore.classes.forEach(function(className) {
                        if (className && className.trim()) {
                            translate.ignore.class.push(className.trim());
                        }
                    });
                }

                // 忽略元素ID
                if (translationConfig.ignore.ids && translationConfig.ignore.ids.length > 0) {
                    translationConfig.ignore.ids.forEach(function(id) {
                        if (id && id.trim()) {
                            translate.ignore.id.push(id.trim());
                        }
                    });
                }

                // 忽略文本内容
                if (translationConfig.ignore.texts && translationConfig.ignore.texts.length > 0) {
                    translationConfig.ignore.texts.forEach(function(text) {
                        if (text && text.trim()) {
                            translate.ignore.text.push(text.trim());
                        }
                    });
                }

                // 正则表达式忽略
                if (translationConfig.ignore.regexs && translationConfig.ignore.regexs.length > 0) {
                    var regexArray = [];
                    translationConfig.ignore.regexs.forEach(function(regexStr) {
                        if (regexStr && regexStr.trim()) {
                            try {
                                // 创建正则表达式对象，添加全局匹配标志
                                var regex = new RegExp(regexStr.trim(), 'g');
                                regexArray.push(regex);
                            } catch (e) {
                                console.warn('无效的正则表达式:', regexStr, e);
                            }
                        }
                    });
                    
                    if (regexArray.length > 0) {
                        translate.ignore.setTextRegexs(regexArray);
                    }
                }
            }
            
            // 注意：translate.js 默认已经忽略 class="ignore" 的元素，无需手动设置

            // 翻译完成回调
            translate.listener.finish = function() {
                console.log('翻译完成，当前语言:', translate.language.getCurrent());
            };

            // 执行翻译初始化
            translate.execute();

            // Ajax兼容性处理 - 对动态加载的内容重新翻译
            if (typeof jQuery !== 'undefined') {
                jQuery(document).ajaxComplete(function() {
                    setTimeout(function() {
                        if (typeof translate !== 'undefined' && translate.execute) {
                            translate.execute();
                        }
                    }, 100);
                });
            }
        };

        script.onerror = function() {
            console.error('translate.js 加载失败');
        };

        document.head.appendChild(script);
    }

    // 页面加载完成后初始化所有功能
    $(document).ready(function() {
        initTheme();
        initTranslation();
    });

})(jQuery);