<?php

$prefix = 'zibll_plugin_option';

CSF::createOptions($prefix, array(
    'menu_title' => '子比主题·功能增强插件',
    'menu_slug' => 'zibll_plugin_option',
    'framework_title' => '子比主题·功能增强插件 <small>v2.0</small>',
    'show_in_customizer' => true,
    'footer_text' => '由何先生开发的子比主题·功能增强插件',
    'footer_credit' => '<i class="fa fa-heart" style="color:#ff4757"></i> 感谢使用',
    'theme' => 'light'
));

// 前端附件管理设置
CSF::createSection($prefix, array(
    'id'      => 'attachment',
    'title'       => '前端附件管理',
    'icon'        => 'fa fa-fw fa-file-image-o',
    'description' => '前端附件管理设置，包括用户中心和作者相册',
    'fields'      => array(
        array(
            'id'      => 'attachment_manager_enable',
            'type'    => 'switcher',
            'title'   => '启用前端附件管理功能',
            'desc'    => '⚠️ 如果子主题已启用附件管理功能，请关闭此选项避免冲突。开启后用户可在用户中心和作者主页查看和管理附件。',
            'default' => true,
        ),
        array(
            'id'         => 'img_list_number',
            'type'       => 'spinner',
            'title'      => '单页加载数量',
            'default'    => 16,
            'step'       => 4,
            'unit'       => '张',
            'dependency' => array('attachment_manager_enable', '==', 'true'),
        ),
        array(
            'id'         => 'paging_ajax_s',
            'type'       => 'radio',
            'title'      => '列表翻页模式',
            'default'    => '1',
            'inline'     => true,
            'desc'       => '您可以在上面选项，以调整单页加载数量',
            'options'    => array(
                '1' => __('AJAX追加列表翻页', 'zib_language'),
                '0' => __('数字翻页按钮', 'zib_language'),
            ),
            'dependency' => array('attachment_manager_enable', '==', 'true'),
        ),
        array(
            'id'         => 'user_delete_image',
            'type'       => 'switcher',
            'title'      => '允许用户删除附件',
            'subtitle'   => '',
            'default'    => true,
            'dependency' => array('attachment_manager_enable', '==', 'true'),
        ),
        array(
            'id'         => 'author_album_enable',
            'type'       => 'switcher',
            'title'      => '启用作者个人相册',
            'subtitle'   => '控制作者主页"个人相册"标签是否显示',
            'default'    => true,
            'dependency' => array('attachment_manager_enable', '==', 'true'),
        ),
        array(
            'id'         => 'author_album_type',
            'type'       => 'radio',
            'title'      => '作者相册显示类别',
            'subtitle'   => '选择非管理员在作者主页相册中显示的文件类型',
            'inline'     => true,
            'default'    => 'image_video',
            'options'    => array(
                'image'       => '图片',
                'video'       => '视频',
                'image_video' => '图片和视频',
            ),
            'dependency' => array(
                array('attachment_manager_enable', '==', 'true'),
                array('author_album_enable', '==', 'true'),
            ),
        ),
    )
));

// 核心功能设置
CSF::createSection($prefix, array(
    'id' => 'core_settings',
    'title' => '核心功能',
    'icon' => 'fa fa-cube',
    'fields' => array(
        array(
            'id' => 'prohibited_balance_pay_types',
            'type' => 'checkbox',
            'title' => '禁止使用余额支付的类型',
            'subtitle' => '勾选不允许使用余额支付的场景',
            'options' => array(
                '1' => '付费阅读',
                '2' => '付费资源',
                '3' => '产品购买',
                '4' => '购买会员',
                '5' => '付费图片',
                '6' => '付费视频',
                '7' => '自动售卡',
                '8' => '余额充值',
                '9' => '购买积分',
            ),
            'default' => array('8'),
            'help' => '防止用户用余额购买余额的套娃行为',
        ),
        array(
            'type' => 'submessage',
            'style' => 'danger',
            'content' => '<div style="padding:10px;background:#fff8f8;border-left:3px solid #ff4757">
                <b>⚠️ 注意</b>
                <p>你已禁止使用余额进行余额充值，这是明智的选择！</p>
                <p>否则用户可以用余额购买余额，形成无限套娃...</p>
            </div>',
            'dependency' => array('prohibited_balance_pay_types', '==', '8'),
        ),
        array(
            'id' => 'show_copyright',
            'type' => 'switcher',
            'title' => '文章版权信息',
            'label' => '在文章底部显示版权信息',
            'default' => false,
        ),
        array(
            'id' => 'copyright_text',
            'type' => 'text',
            'title' => '版权文字内容',
            'dependency' => array('show_copyright', '==', '1'),
            'default' => '本文来自子比主题演示插件',
            'placeholder' => '输入要显示的版权信息',
            'validate' => 'wp_kses_post',
        ),
    )
));

// 外观设置
CSF::createSection($prefix, array(
    'id' => 'appearance',
    'title' => '外观设置',
    'icon' => 'fa fa-paint-brush',
    'fields' => array(
        array(
            'id' => 'site_greyscale',
            'type' => 'switcher',
            'title' => '全站变灰模式',
            'label' => '特殊日期开启全站灰色效果',
            'default' => false,
            'help' => '适用于纪念日等特殊场景',
        ),
        array(
            'id' => 'admin_css',
            'type' => 'code_editor',
            'title' => '后台自定义CSS',
            'subtitle' => '修改WordPress后台样式',
            'settings' => array(
                'theme' => 'dracula',
                'mode' => 'css',
                'tabSize' => 4,
            ),
            'default' => '/* 在这里添加CSS代码 */',
            'sanitize' => 'wp_strip_all_tags',
        ),
        array(
            'type' => 'submessage',
            'style' => 'warning',
            'content' => '<div style="padding:10px;background:#fff8e1;border-left:3px solid #ffc107">
                <b><i class="fa fa-exclamation-triangle"></i> 重要提示</b>
                <p>1. 修改前建议备份当前CSS</p>
                <p>2. 错误的CSS可能导致后台显示异常</p>
            </div>',
            'dependency' => array('admin_css', '!=', ''),
        ),
    )
));

// 多语言翻译设置
CSF::createSection($prefix, array(
    'title'       => '多语言翻译',
    'icon'        => 'fa fa-globe',
    'description' => '基于 translate.js 的网站多语言翻译功能设置，支持12种语言自动翻译',
    'fields'      => array(
        array(
            'type'    => 'submessage',
            'style'   => 'info',
            'content' => '<h4><i class="fa fa-info-circle"></i> 功能说明</h4>
                         <p>• 基于开源 translate.js v3 项目，支持两种翻译服务模式</p>
                         <p>• <strong>client.edge</strong>：直接调用微软翻译，无字符限制，更稳定（推荐）</p>
                         <p>• <strong>translate.service</strong>：默认翻译服务，支持更多语种，200万字符/日</p>
                         <p>• 自动翻译网页内容，支持73-450种语言，无需手动配置语言文件</p>
                         <p>• translate.js 默认忽略 <code>class="ignore"</code> 的元素，可在下方自定义更多忽略规则</p>
                         <p>• 项目文档：<a href="http://translate.zvo.cn/43086.html" target="_blank">翻译服务设置说明</a></p>',
        ),
        array(
            'id'      => 'translation_s',
            'type'    => 'switcher',
            'title'   => '启用多语言翻译功能',
            'desc'    => '开启后将在导航栏显示多语言切换按钮，并自动加载翻译脚本',
            'default' => false,
        ),
        array(
            'id'         => 'translation_options',
            'type'       => 'fieldset',
            'title'      => '翻译功能设置',
            'fields'     => array(
                array(
                    'id'      => 'translation_service',
                    'type'    => 'radio',
                    'title'   => '翻译服务',
                    'options' => array(
                        'client.edge'       => 'Microsoft Edge（推荐）- 直接调用微软翻译，无字符限制',
                        'translate.service' => 'translate.service - 默认翻译服务，200万字符/日',
                    ),
                    'default' => 'client.edge',
                    'desc'    => '根据官方文档，推荐使用 client.edge 模式，更稳定且无字符数限制',
                ),
                array(
                    'id'      => 'translation_enabled_languages',
                    'type'    => 'checkbox',
                    'title'   => '启用的语言',
                    'options' => array(
                        'chinese_simplified'  => '🇨🇳 简体中文',
                        'chinese_traditional' => '🇹🇼 繁体中文',
                        'english'             => '🇺🇸 English',
                        'japanese'            => '🇯🇵 日本語',
                        'korean'              => '🇰🇷 한국어',
                        'vietnamese'          => '🇻🇳 Vietnamese',
                        'russian'             => '🇷🇺 Русский',
                        'spanish'             => '🇪🇸 Español',
                        'french'              => '🇫🇷 Français',
                        'deutsch'             => '🇩🇪 Deutsch',
                        'portuguese'          => '🇧🇷 Português',
                        'thai'                => '🇹🇭 ไทย',
                        'hindi'               => '🇮🇳 हिन्दी',
                        'arabic'              => '🇸🇦 العربية',
                    ),
                    'default' => array(
                        'chinese_simplified',
                        'chinese_traditional',
                        'english',
                        'japanese',
                        'korean',
                    ),
                    'desc'    => '选择要在切换按钮中显示的语言选项，建议不要选择太多语言',
                ),
                array(
                    'id'      => 'translation_default_lang',
                    'type'    => 'select',
                    'title'   => '默认语言',
                    'options' => array(
                        'chinese_simplified'  => '简体中文',
                        'chinese_traditional' => '繁体中文',
                        'english'             => 'English',
                        'japanese'            => '日本語',
                        'korean'              => '한국어',
                        'vietnamese'          => 'Vietnamese',
                        'russian'             => 'Русский',
                        'spanish'             => 'Español',
                        'french'              => 'Français',
                        'deutsch'             => 'Deutsch',
                        'portuguese'          => 'Português',
                        'thai'                => 'ไทย',
                        'hindi'               => 'हिन्दी',
                        'arabic'              => 'العربية',
                    ),
                    'default' => 'chinese_simplified',
                    'desc'    => '设置网站的默认显示语言',
                ),
                array(
                    'id'      => 'translation_auto_detect',
                    'type'    => 'switcher',
                    'title'   => '自动检测用户语言',
                    'desc'    => '根据用户浏览器语言自动切换网站语言',
                    'default' => true,
                ),
                array(
                    'id'      => 'translate_js',
                    'type'    => 'switcher',
                    'title'   => '页面元素动态监控',
                    'desc'    => '开启页面元素动态监控，js改变的内容也会被翻译',
                    'default' => false,
                ),
                array(
                    'id'      => 'translation_button_position',
                    'type'    => 'radio',
                    'title'   => '按钮显示位置',
                    'options' => array(
                        'header'  => '导航栏右侧',
                        'footer'  => '右侧浮动按钮区域',
                        'both'    => '导航栏和浮动按钮都显示',
                    ),
                    'default' => 'header',
                    'desc'    => '选择多语言切换按钮的显示位置。右侧浮动按钮会显示在页面右下角的浮动工具栏中',
                ),
                // 翻译忽略设置
                array(
                    'type'    => 'subheading',
                    'content' => '翻译忽略设置',
                ),
                array(
                    'id'      => 'translation_ignore_tags',
                    'type'    => 'text',
                    'title'   => '忽略HTML标签',
                    'desc'    => '指定不翻译的HTML标签，多个标签请用英文逗号分隔。例如：code,pre,script,style',
                    'default' => 'code,pre,script,style',
                    'placeholder' => 'code,pre,script,style',
                ),
                array(
                    'id'      => 'translation_ignore_classes',
                    'type'    => 'text',
                    'title'   => '忽略CSS类名',
                    'desc'    => '指定不翻译的CSS类名，多个类名请用英文逗号分隔。注意：translate.js默认已忽略ignore类',
                    'default' => 'no-translate',
                    'placeholder' => 'ignore,no-translate,code-block',
                ),
                array(
                    'id'      => 'translation_ignore_ids',
                    'type'    => 'text',
                    'title'   => '忽略元素ID',
                    'desc'    => '指定不翻译的元素ID，多个ID请用英文逗号分隔。例如：header,footer,navigation',
                    'default' => '',
                    'placeholder' => 'header,footer,navigation',
                ),
                array(
                    'id'      => 'translation_ignore_texts',
                    'type'    => 'textarea',
                    'title'   => '忽略文本内容',
                    'desc'    => '指定不翻译的文本内容，每行一个。支持完整匹配，遇到这些文本内容时不会翻译',
                    'default' => '',
                    'placeholder' => '版权所有©' . "\n" . 'All Rights Reserved' . "\n" . 'TODO:',
                    'attributes' => array(
                        'rows' => 5,
                    ),
                ),
                array(
                    'id'      => 'translation_ignore_regexs',
                    'type'    => 'textarea',
                    'title'   => '正则表达式忽略',
                    'desc'    => '使用正则表达式忽略匹配的文本内容，每行一个正则表达式。无需添加斜杠和修饰符，系统会自动处理',
                    'default' => 'TODO:\n\\d{4}-\\d{2}-\\d{2}\n@\\w+\n#\\w+',
                    'placeholder' => 'TODO:' . "\n" . '\\d{4}-\\d{2}-\\d{2}' . "\n" . '@\\w+' . "\n" . '#\\w+',
                    'attributes' => array(
                        'rows' => 6,
                    ),
                    'subtitle' => '常用示例：TODO: (匹配TODO开头)、\\d{4}-\\d{2}-\\d{2} (匹配日期)、@\\w+ (匹配@用户名)、#\\w+ (匹配#标签)',
                ),
            ),
            'dependency' => array('translation_s', '==', '1'),
        ),
    )
));

// 会员权限管理
CSF::createSection($prefix, array(
    'id' => 'vip_settings',
    'title' => '会员权限管理',
    'icon' => 'fa fa-crown',
    'description' => '会员特权功能设置，可单独控制不同级别会员的权限',
    'fields' => array(
        array(
            'id' => 'vip1_skip_comment_view',
            'type' => 'switcher',
            'title' => '一级会员免评论查看',
            'label' => '启用一级会员免评论查看功能',
            'desc' => '开启后，一级会员用户无需评论即可查看"评论可见"的内容',
            'default' => false,
        ),
        array(
            'id' => 'vip2_skip_comment_view',
            'type' => 'switcher',
            'title' => '二级会员免评论查看',
            'label' => '启用二级会员免评论查看功能',
            'desc' => '开启后，二级会员用户无需评论即可查看"评论可见"的内容<br><span style="color:#f97113;">此功能会让会员用户拥有与管理员相同的查看权限，请谨慎开启</span>',
            'default' => false,
        ),
        array(
            'type' => 'submessage',
            'style' => 'info',
            'content' => '<div style="padding:10px;background:#f0f8ff;border-left:3px solid #007cba">
                <b><i class="fa fa-info-circle"></i> 功能说明</b>
                <p>• 此功能需要配合子比主题的VIP系统使用</p>
                <p>• 会员等级判断基于子比主题的会员系统</p>
                <p>• 开启后对应等级的会员可以直接查看评论可见内容</p>
            </div>',
            'dependency' => array(
                array('vip1_skip_comment_view', '==', '1'),
                array('vip2_skip_comment_view', '==', '1'),
            ),
        ),
    )
));

// Google 登录设置
CSF::createSection($prefix, array(
    'title' => 'Google 登录',
    'icon' => 'fa fa-google',
    'fields' => array(
        array(
            'id' => 'google_enable',
            'type' => 'switcher',
            'title' => '启用 Google 登录',
            'desc' => '基于 Google OAuth 2.0 的官方登录功能',
            'default' => false,
        ),
        array(
            'id' => 'google_client_id',
            'type' => 'text',
            'title' => 'Google Client ID',
            'desc' => '从 Google Cloud Console 获取的客户端ID',
            'dependency' => array('google_enable', '==', 'true'),
        ),
        array(
            'id' => 'google_client_secret',
            'type' => 'text',
            'title' => 'Google Client Secret',
            'desc' => '从 Google Cloud Console 获取的客户端密钥',
            'dependency' => array('google_enable', '==', 'true'),
        ),
        array(
            'type' => 'submessage',
            'style' => 'info',
            'content' => '<h4><b>Google OAuth 配置步骤：</b></h4>
                <p>1. <strong>回调地址：</strong>' . home_url('/oauth/google/callback') . '</p>
                <p>2. 访问 <a target="_blank" href="https://console.cloud.google.com/">Google Cloud Console</a></p>
                <p>3. 创建新项目或选择现有项目</p>
                <p>4. 启用 Google+ API 或 People API</p>
                <p>5. 创建 OAuth 2.0 客户端ID</p>
                <p>6. 设置重定向URI为上方显示的回调地址</p>
                <p>7. 将获取的 Client ID 和 Client Secret 填入上方设置</p>',
            'dependency' => array('google_enable', '==', 'true'),
        ),
    )
));

// MixAuth QQ登录设置
CSF::createSection($prefix, array(
    'title' => 'MixAuth QQ登录',
    'icon' => 'fa fa-qq',
    'fields' => array(
        array(
            'id' => 'mixauthqq_enable',
            'type' => 'switcher',
            'title' => '启用MixAuth QQ登录',
            'desc' => '基于MixAuth项目的第三方QQ登录功能，无需申请QQ互联应用',
            'default' => false,
        ),
        array(
            'id' => 'mixauthqq_server_url',
            'type' => 'text',
            'title' => 'MixAuth服务地址',
            'desc' => '输入MixAuth服务的完整URL，如：https://mixauth.onrender.com 或您自己部署的地址',
            'default' => 'https://mixauth.onrender.com',
            'dependency' => array('mixauthqq_enable', '==', 'true'),
        ),
        array(
            'id' => 'mixauthqq_integration_mode',
            'type' => 'radio',
            'title' => '接入方式',
            'options' => array(
                'api' => 'API接口模式（推荐）- 自定义UI，支持纯QQ登录',
                'iframe' => 'iframe嵌入模式 - 使用MixAuth完整页面'
            ),
            'default' => 'api',
            'desc' => 'API模式：自定义二维码UI，更快速，支持纯QQ登录，支持签名验证<br>iframe模式：嵌入完整MixAuth页面，支持QQ/微信切换，必须签名验证',
            'dependency' => array('mixauthqq_enable', '==', 'true'),
        ),
        array(
            'type' => 'submessage',
            'style' => 'info',
            'content' => '<h4><b>使用说明：</b></h4>
                <p>1. <strong>回调地址：</strong>' . home_url('/oauth/mixauthqq/callback') . '</p>
                <p>2. <strong>项目地址：</strong><a target="_blank" href="https://github.com/InvertGeek/mixauth">https://github.com/InvertGeek/mixauth</a></p>
                <p>3. <strong>功能特点：</strong>无需申请QQ互联应用，基于官方接口逆向实现</p>
                <p>4. <strong>安全提示：</strong>签名验证由MixAuth服务端自动处理，无需手动配置密钥</p>',
            'dependency' => array('mixauthqq_enable', '==', 'true'),
        ),
    )
));

CSF::createSection($prefix, array(
    'id' => 'authorization',
    'title' => '授权管理',
    'icon' => 'fa fa-shield',
    'fields' => array(
        CFS_Module_ZibPlugin::aut(),
    )
));