/**
 * 翻译功能 JavaScript - 完全按照子主题 mrheall.js 的优雅逻辑重构
 */
(function($) {
    'use strict';

    // 翻译功能初始化 - 优先检查子主题配置
    function initTranslation() {
        // 优先检查子主题配置
        const themeConfig = window._mrhe?.TRANSLATION;
        if (themeConfig && themeConfig.enabled) {
            return;
        }
        
        // 使用插件自己的配置
        const pluginConfig = window._zibll_plugin?.TRANSLATION;
        if (!pluginConfig || !pluginConfig.enabled) {
            return;
        }
        

        // 动态加载 translate.js
        const script = document.createElement('script');
        script.src = pluginConfig.cdn_url;
        script.onload = function() {
            // translate.js 加载完成后进行初始化
            if (typeof translate === 'undefined') {
                console.error('translate.js 加载失败');
                return;
            }


            // 设置翻译服务
            translate.service.use(pluginConfig.service);

            // 设置本地语言
            translate.language.setLocal(pluginConfig.default_language);

            // 自动检测用户语言
            if (pluginConfig.auto_detect) {
                translate.setAutoDiscriminateLocalLanguage();
            }

            if (pluginConfig.translate_js) {
                translate.listener.start();
            }

            // 隐藏默认语言选择框（我们使用自定义按钮）
            translate.selectLanguageTag.show = false;

            // 应用忽略设置
            if (pluginConfig.ignore) {
                // 忽略HTML标签
                if (pluginConfig.ignore.tags && pluginConfig.ignore.tags.length > 0) {
                    pluginConfig.ignore.tags.forEach(function(tag) {
                        if (tag && tag.trim()) {
                            translate.ignore.tag.push(tag.trim());
                        }
                    });
                }

                // 忽略CSS类名（translate.js默认已忽略 class="ignore"）
                if (pluginConfig.ignore.classes && pluginConfig.ignore.classes.length > 0) {
                    pluginConfig.ignore.classes.forEach(function(className) {
                        if (className && className.trim()) {
                            translate.ignore.class.push(className.trim());
                        }
                    });
                }

                // 忽略元素ID
                if (pluginConfig.ignore.ids && pluginConfig.ignore.ids.length > 0) {
                    pluginConfig.ignore.ids.forEach(function(id) {
                        if (id && id.trim()) {
                            translate.ignore.id.push(id.trim());
                        }
                    });
                }

                // 忽略文本内容
                if (pluginConfig.ignore.texts && pluginConfig.ignore.texts.length > 0) {
                    pluginConfig.ignore.texts.forEach(function(text) {
                        if (text && text.trim()) {
                            translate.ignore.text.push(text.trim());
                        }
                    });
                }

                // 正则表达式忽略
                if (pluginConfig.ignore.regexs && pluginConfig.ignore.regexs.length > 0) {
                    var regexArray = [];
                    pluginConfig.ignore.regexs.forEach(function(regexStr) {
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

            // 调试信息
        };

        script.onerror = function() {
            console.error('translate.js 加载失败');
        };

        document.head.appendChild(script);
    }

    // 页面加载完成后初始化翻译功能
    $(document).ready(function() {
        initTranslation();
    });

})(jQuery);