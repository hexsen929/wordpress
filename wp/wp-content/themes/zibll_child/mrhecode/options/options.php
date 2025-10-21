<?php

// 这里我们设置为父主题前缀，将菜单挂载至父主题后台菜单
$prefix = 'mrhe_options';

// 设置图片目录为子主题的路径
$imagepath = get_stylesheet_directory_uri() . '/img/';

//定义mrhe主题常量
$mrhe_theme_data = wp_get_theme();
$mrhe_version   = $mrhe_theme_data['Version'];
define('MRHE_THEME_VERSION', $mrhe_version);

/* 文章分类 */
$options_categories = array();
$options_categories_obj = get_categories();
foreach ($options_categories_obj as $category) {
    $options_categories[$category->cat_ID] = $category->cat_name;
}

//友情链接
$options_linkcats = array();
$options_linkcats_obj = get_terms('link_category', 'orderby=count&hide_empty=0');
foreach ( $options_linkcats_obj as $tag ) {
    $options_linkcats[$tag->term_id] = $tag->name;
}

//自定义获取ipapi
$options_ipapi = array();
$custom_apis = _mrhe('ip_location_custom_apis');

if (!empty($custom_apis) && is_array($custom_apis)) {
    foreach ($custom_apis as $api) {
        if (!empty($api['api_status']) && !empty($api['id']) && !empty($api['api_name'])) {
            $options_ipapi[$api['id']] = $api['api_name'];
        }
    }
}	

//开始构建
CSF::createOptions($prefix, array(
    'menu_title'         => 'mrhe主题设置',
    'menu_slug'          => 'mrhe_options',
    'framework_title'    => 'mrhe主题',
    'show_in_customizer' => true, //在wp-customize中也显示相同的选项
    'footer_text'        => '更优雅的wordpress主题-mrhe主题 V' . wp_get_theme()['Version'],
    'footer_credit'      => '<i class="fa fa-fw fa-heart-o" aria-hidden="true"></i> ',
    'theme'              => 'light',
));

CSF::createSection($prefix, array(
    'title'       => '域名安全设置',
    'icon'        => 'fa fa-shield',
    'description' => '配置官方域名，用于域名安全检测',
    'fields'      => array(
        array(
            'id'      => 'official_domains_s',
            'type'    => 'repeater',
            'title'   => '官方域名列表',
            'desc'    => '添加你的官方域名，需包含完整URL（如：https://www.hexsen.com）',
            'fields'  => array(
                array(
                    'id'    => 'domain',
                    'type'  => 'text',
                    'title' => '域名地址',
                ),
            ),
            'default' => array(
                array(
                    'domain' => 'https://www.hexsen.com',
                ),
            ),
        ),
    )
));

CSF::createSection($prefix, array(
    'title'       => '会员免评论查看',
    'icon'        => 'fa fa-shield',
    'description' => '会员免评论查看功能，持单独控制一级和二级会员的权限',
    'fields'      => array(
        array(
            'title'   => '一级会员专属功能',
            'id'      => 'vip1_skip_comment_view',
            'label'   => '启用一级会员免评论查看功能',
            'desc'    => '开启后，一级会员用户无需评论即可查看"评论可见"的内容',
            'default' => false,
            'type'    => 'switcher',
        ),
        array(
            'title'   => '二级会员专属功能',
            'subtitle' => '二级会员免评论查看',
            'id'      => 'vip2_skip_comment_view',
            'label'   => '启用二级会员免评论查看功能',
            'desc'    => '开启后，二级会员用户无需评论即可查看"评论可见"的内容<br><span style="color:#f97113;">此功能会让会员用户拥有与管理员相同的查看权限，请谨慎开启</span>',
            'default' => false,
            'type'    => 'switcher',
            'class'   => 'compact',
        ),
    )
));

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
                    'id'      => 'translation_default_lang',
                    'type'    => 'select',
                    'title'   => '默认语言',
                    'options' => array(
                        'chinese_simplified'  => '简体中文',
                        'chinese_traditional' => '繁体中文',
                        'english'             => 'English',
                        'japanese'            => '日本語',
                        'korean'              => '한국어',
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
            'dependency' => array('translation_s', '==', 'true'),
        ),
    )
));

CSF::createSection($prefix, array(
    'title'       => '社交登录',
    'icon'        => 'fa fa-share-alt',
    'description' => '配置社交账号登录',
    'fields'      => array(
        array(
            'id'      => 'oauth_google_s',
            'type'    => 'switcher',
            'title'   => 'Google登录',
            'desc'    => '开启后需要配置相关参数',
            'default' => false,
        ),
        array(
            'id'         => 'oauth_google_option',
            'type'       => 'fieldset',
            'title'      => 'Google登录配置',
            'fields'     => array(
                array(
                    'content' => '<h4><b>回调地址：</b>' . esc_url(home_url('/oauth/google/callback')) . '</h4>Google登录申请地址：<a target="_blank" href="https://console.cloud.google.com/apis/credentials">https://console.cloud.google.com/apis/credentials</a>',
                    'style'   => 'info',
                    'type'    => 'submessage',
                ),
                array(
                    'title' => 'Client ID',
                    'id'    => 'appid',
                    'type'  => 'text',
                ),
                array(
                    'title' => 'Client Secret', 
                    'id'    => 'appkey',
                    'type'  => 'text',
                ),
            ),
            'dependency' => array('oauth_google_s', '==', 'true'),
        ),
        array(
            'id'      => 'oauth_mixauthqq_s',
            'type'    => 'switcher',
            'title'   => 'MixAuth QQ登录',
            'desc'    => '第三方QQ登录功能，兼容官方QQ登录',
            'default' => false,
        ),
        array(
            'id'         => 'oauth_mixauthqq_option',
            'type'       => 'fieldset',
            'title'      => 'MixAuth QQ登录配置',
            'fields'     => array(
                array(
                    'content' => '<h4><b>回调地址：</b>' . esc_url(home_url('/oauth/mixauthqq/callback')) . '</h4>MixAuth项目地址：<a target="_blank" href="https://github.com/InvertGeek/mixauth">https://github.com/InvertGeek/mixauth</a><br>此功能基于MixAuth项目提供的第三方QQ登录接口，无需申请QQ互联应用。',
                    'style'   => 'info',
                    'type'    => 'submessage',
                ),
                array(
                        'title' => 'MixAuth服务地址',
                        'id'    => 'server_url',
                        'type'  => 'text',
                        'desc'  => '输入MixAuth服务的完整URL，如：https://mixauthqq.vercel.app 或您自己部署的地址',
                        'default' => 'https://mixauthqq.vercel.app',
                ),
                    array(
                        'title' => '接入方式',
                        'id'    => 'integration_mode',
                        'type'  => 'radio',
                        'options' => array(
                            'api' => 'API接口模式（推荐）- 自定义UI，支持纯QQ登录',
                            'iframe' => 'iframe嵌入模式 - 使用MixAuth完整页面'
                        ),
                        'default' => 'api',
                        'desc'  => 'API模式：自定义二维码UI，更快速，支持纯QQ登录，支持签名验证<br>iframe模式：嵌入完整MixAuth页面，支持QQ/微信切换，必须签名验证',
                    ),
            ),
            'dependency' => array('oauth_mixauthqq_s', '==', 'true'),
        ),
    )
));

    CSF::createSection($prefix, array(
		'title'       => 'gravatar头像',
		'icon'        => 'fa fa-handshake-o',
		'description' => '这里设置gravatar头像源',
		'fields'      => array(
	//         array(
	//             'id'     => 'top_bar_s',
	//             'type'   => 'fieldset',
	// 			'title'  => '公告文章分类选择',
	//             'fields' => array(
	//                 array(
	// 					'id'      => 'top_bar_category',
	// 					'type'    => 'select',
	//                     'title'   => '选择分类',
	//                     //'chosen'      => false,
	//                     'multiple'    => false,
	// 					'options' => 'categories',
	// 					'desc'		 => '注：选中的分类第一篇文章的标题将作为公告显示。',
	// 					'placeholder' => '只可选择一个分类'
	//                 ),
	// 			),
	// 			'dependency' => array('top_bar', '==', 'true'),
	// 		),
			
			array(
				'id'      => 'gravatar_url',
				'type'    => 'radio',
				'title'   => '头像cdn',
				'inline'  => true,
				'options' => array(
					'loli'    => esc_html__('从loli获取','mrhe'),
					'ssl'   => esc_html__('从Gravatar官方ssl获取','mrhe'),
					'v2ex'  => esc_html__('从v2ex获取','mrhe'),
				),
				'default' => 'ssl',
			),
		)
    ));
	
	CSF::createSection($prefix, array(
		'title'       => '评论区增强',
		'icon'        => 'fa fa-handshake-o',
		'description' => '评论者地区和浏览器显示',
		'fields'      => array(
            array(
                'type'    => 'submessage',
                'style'   => 'warning',
                'content' => '<b>移动端底部Tab导航：</b>在移动端固定显示在最底部的tab导航按钮，支持排序和添加删除，注意开启后按钮不宜过多 | <a target="_blank" href="https://www.zibll.com/2983.html">查看官网教程</a>',
            ),
			// 评论者地区和浏览器显示
			array(
				'id'      => 'admin_ip_address_swich',
				'type'    => 'switcher',
				'title'   => '评论者地区、操作系统和浏览器显示',
				'label'   => '',
				'default' => false,
			),
			array(
				'id'      => 'admin_ip_address',
				'type'    => 'text',
				'title'   => '自定义管理员ip地址，留空则显示真实ip地址',
				'default' => 'cloudflare',
				'dependency' => array('admin_ip_address_swich', '==', 'true'),
			),
			// 评论者重定向
			array(
				'id'      => 'links_open',
				'type'    => 'switcher',
				'title'   => '评论者链接超过多少天后不显示',
				'dsec'    => '统一字数可以解决摘要文字太多不美观问题',
				'label'   => '',
				'default' => false,
			),
			array(
				'id'      => 'links_num',
				'type'    => 'text',
				'title'   => '设置天数',
				'default' => '42',
				'dependency' => array('links_open', '==', 'true'),
			),
		)
	));
	
	CSF::createSection($prefix, array(
		'title'       => 'Lsky Pro图床',
		'icon'        => 'fa fa-handshake-o',
		'fields'      => array(
			array(
				'id'      => 'open_upload_url_to_api',
				'type'    => 'switcher',
				'title'   => '开启Lsky Pro图床接管wp媒体库',
				'default' => false,
			),
			array(
				'id'		 => 'api_from_function',
				'type'       => 'fieldset',
				'title'      => 'Lsky Pro图床相关信息',
				'fields'     => array(
					array(
						'id'      => 'api_from_remote_url',
						'type'    => 'text',
						'title'   => 'Lsky Pro图床域名',
						'desc'		 => '域名需要加 http(s)://，不需要在域名后加 /api/v1/upload',
						'default' => 'https://images.hexsen.com',
					),
					array(
						'id'      => 'api_from_Lsky_username',
						'type'    => 'text',
						'title'   => '兰空图床管理员用户名',
						'desc'	  => '站长专用账号，此账号下只保存站长的图片。',
						'default' => 'admin',
					),
					array(
						'id'      => 'api_from_Lsky_password',
						'type'    => 'text',
						'title'   => '兰空图床登录密码',
						'default' => 'password',
					),
					array(
						'id'      => 'api_from_Lsky_other_username',
						'type'    => 'text',
						'title'   => '其他用户登录用户名',
						'desc'	  => '其他所有用户上传图片将使用此账号保存图片。方便后期清理。所以你需要在Lsky Pro图床设置两个账号',
						'default' => 'admin',
					),
					array(
						'id'      => 'api_from_Lsky_other_password',
						'type'    => 'text',
						'title'   => '其他用户登录密码',
						'default' => 'password',
					),
					array(
						'id'      => 'api_from_visit_url',
						'type'    => 'text',
						'title'   => '储存策略中的访问网址',
						'desc'		 => '默认是 http(s)://图床域名/i（此设置将在上传新图片后进行更新）',
						'default' => 'https://images.hexsen.com/i',
					),
					array(
						'id'       => 'api_from_strategy_id',
						'class'    => 'compact',
						'title'    => '储存策略的ID',
						//'subtitle' => 'ID',
						'desc'     => '输入的ID将是图片上传到你的那个储存，默认储存是数字1',
						'default'  => 1,
						'type'     => 'number',
						//'unit'     => '天',
					),
					array(
						'id'       => 'delete_path_img',
						'type'     => 'switcher',
						'title'    => esc_html__( '不保留wp本地文件', 'your-textdomain-here' ),
						'desc'     => '默认启用删除本地文件!如果你在兰空图床启用格式转换请将其启用！',
						'default'  => true,
					),
				),
				'dependency' => array('open_upload_url_to_api', '==', 'true'),
			),  
		)
	));
	
	#文件存储系统设置
	CSF::createSection($prefix, array(
		'title'       => '文件存储系统设置',
		'icon'        => 'fa fa-handshake-o',
		'description' => '',
		'fields'      => array(
			array(
				'id'      => 'open_storage_api',
				'type'    => 'switcher',
				'title'   => '开启文件存储系统接管wp媒体库',
				'default' => false,
			),
			array(
				'id'		 => 'storage_api_function',
				'type'       => 'fieldset',
				'title'      => '文件存储系统相关信息',
				'fields'     => array(
					array(
						'id'      => 'storage_api_url',
						'type'    => 'text',
						'title'   => '文件存储系统域名',
						'desc'	  => '文件存储系统域名，比如：https://nzdata.hexsen.com',
						'default' => 'https://nzdata.hexsen.com',
					),
					array(
						'id'      => 'storage_api_token',
						'type'    => 'text',
						'title'   => '管理员Token',
						'desc'	  => '管理员Token，比如：a55eba2db1dc3a4fa2f4c9815b43402e9ba9e0bcf2bc774f8865123767bc1993',
						'default' => 'a55eba2db1dc3a4fa2f4c9815b43402e9ba9e0bcf2bc774f8865123767bc1993',
					),
					array(
						'id'      => 'storage_api_token_user',
						'type'    => 'text',
						'title'   => '普通用户Token',
						'desc'	  => '普通用户Token，比如：eb011e6e55ff27dd73ca0cf8fa875be61fabd50061185e107c043fa44b396219',
						'default' => 'eb011e6e55ff27dd73ca0cf8fa875be61fabd50061185e107c043fa44b396219',
					),
				),
				'dependency' => array('open_storage_api', '==', 'true'),
			),
		)
	));

    CSF::createSection($prefix, array(
        'title'       => 'IP归属地设置',
        'icon'        => 'fa fa-map-marker',
        'description' => '设置IP归属地查询接口',
        'fields'      => array(
            array(
                'id'      => 'ip_location_s',
                'type'    => 'switcher',
                'title'   => '启用子主题IP归属地',
                'desc'    => '开启后将优先使用子主题的IP归属地查询接口',
                'default' => false,
            ),
            array(
                'id'         => 'ip_location_api',
                'type'       => 'radio',
                'title'      => 'API接口选择',
                'options'    => array(
                    'ip-api' => 'IP-API.com（免费）',
                    'ipapi'  => 'ipapi.co（免费）',
                    'custom' => '自定义API接口'
                ),
                'default'    => 'ip-api',
                'dependency' => array('ip_location_s', '==', 'true'),
            ),
            array(
                'id'         => 'ip_location_polling',
                'type'       => 'switcher',
                'title'      => '启用API轮询',
                'desc'       => '开启后将按照设定的策略轮询使用API接口',
                'default'    => false,
                'dependency' => array(
                    array('ip_location_s', '==', 'true'),
                    array('ip_location_api', '==', 'custom')
                ),
            ),
            array(
                'id'         => 'selected_custom_api',
                'type'       => 'select',
                'title'      => '选择使用的自定义API',
                'desc'       => '非轮询模式下，选择要使用的自定义API',
                'dependency' => array(
                    array('ip_location_s', '==', 'true'),
                    array('ip_location_api', '==', 'custom'),
                    array('ip_location_polling', '==', 'false')
                ),
                'options'    => $options_ipapi,
            ),
            array(
                'id'         => 'ip_location_polling_strategy',
                'type'       => 'radio',
                'title'      => '轮询策略',
                'options'    => array(
                    'sequential' => '顺序轮询',
                    'random'     => '随机轮询'
                ),
                'default'    => 'sequential',
                'dependency' => array(
                    array('ip_location_s', '==', 'true'),
                    array('ip_location_api', '==', 'custom'),
                    array('ip_location_polling', '==', 'true')
                ),
            ),
            array(
                'id'         => 'ip_location_custom_apis',
                'type'       => 'group',
                'title'      => '自定义API接口列表',
                'desc'       => '可添加多个自定义API接口',
                'dependency' => array(
                    array('ip_location_s', '==', 'true'),
                    array('ip_location_api', '==', 'custom')
                ),
                'fields'     => array(
                    array(
                        'id'      => 'id',
                        'type'    => 'text',
                        'title'   => 'API ID',
                        'desc'    => '为该API设置一个唯一标识符',
                    ),
                    array(
                        'id'      => 'api_name',
                        'type'    => 'text',
                        'title'   => 'API名称',
                        'desc'    => '为该API设置一个识别名称',
                    ),
                    array(
                        'id'      => 'api_status',
                        'type'    => 'switcher',
                        'title'   => '启用状态',
                        'desc'    => '是否启用该API接口',
                        'default' => true,
                    ),
                    array(
                        'id'      => 'api_url',
                        'type'    => 'text',
                        'title'   => 'API接口地址',
                        'desc'    => '输入完整的API接口地址，使用{ip}作为IP地址的占位符，例如: https://api.example.com/ip/{ip}',
                    ),
                    array(
                        'id'      => 'nation_key',
                        'type'    => 'text',
                        'title'   => '国家字段名',
                        'desc'    => '返回JSON中表示国家的字段名，支持多级JSON，使用点号分隔，例如：data.location.country',
                        'default' => 'country',
                    ),
                    array(
                        'id'      => 'province_key',
                        'type'    => 'text',
                        'title'   => '省份字段名',
                        'desc'    => '返回JSON中表示省份的字段名，支持多级JSON，使用点号分隔，例如：data.location.region',
                        'default' => 'region',
                    ),
                    array(
                        'id'      => 'city_key',
                        'type'    => 'text',
                        'title'   => '城市字段名',
                        'desc'    => '返回JSON中表示城市的字段名，支持多级JSON，使用点号分隔，例如：data.location.city',
                        'default' => 'city',
                    )
                ),
            ),
        )
    ));

    // 新增获取本机IP的API设置部分
    CSF::createSection($prefix, array(
        'title'  => '本机IP获取设置',
        'icon'   => 'fa fa-globe',
        'fields' => array(
            array(
                'id'      => 'client_ip_api_s',
                'type'    => 'switcher',
                'title'   => '启用自定义本机IP获取API',
                'desc'    => '开启后将使用自定义API获取本机IP',
                'default' => false,
            ),
            array(
                'dependency' => array('client_ip_api_s', '==', 'true'),
                'id'         => 'client_ip_apis',
                'type'       => 'group',
                'title'      => '本机IP获取API列表',
                'desc'       => '可添加多个API，当前一个获取失败时会自动尝试下一个',
                'fields'     => array(
                    array(
                        'id'      => 'api_name',
                        'type'    => 'text',
                        'title'   => 'API名称',
                        'desc'    => '为该API设置一个识别名称',
                    ),
                    array(
                        'id'      => 'api_status',
                        'type'    => 'switcher',
                        'title'   => '启用状态',
                        'desc'    => '是否启用该API',
                        'default' => true,
                    ),
                    array(
                        'id'      => 'api_url',
                        'type'    => 'text',
                        'title'   => 'API接口地址',
                        'desc'    => '输入完整的API接口地址',
                    ),
                    array(
                        'id'      => 'ip_key',
                        'type'    => 'text',
                        'title'   => 'IP字段路径',
                        'desc'    => '返回JSON中IP字段的路径，支持多级，使用点号分隔。例如：data.ip 或 ip。如果是JSONP格式，请填写完整路径，如：IPCallBack.ip',
                        'default' => 'ip',
                    ),
                    array(
                        'id'      => 'is_jsonp',
                        'type'    => 'switcher',
                        'title'   => '是否为JSONP格式',
                        'desc'    => '如果API返回的是JSONP格式（如：IPCallBack({...})），请开启此选项',
                        'default' => false,
                    ),
                    array(
                        'id'      => 'jsonp_callback',
                        'type'    => 'text',
                        'title'   => 'JSONP回调函数名',
                        'desc'    => '如果是JSONP格式，请填写回调函数名，如：IPCallBack',
                        'dependency' => array('is_jsonp', '==', 'true'),
                        'default' => 'IPCallBack',
                    ),
                ),
                'default'    => array(
                    array(
                        'api_name'   => 'useragentinfo',
                        'api_status' => true,
                        'api_url'    => 'https://ip.useragentinfo.com/json',
                        'ip_key'     => 'ip'
                    ),
					array(
                        'api_name'   => 'ip-api',
                        'api_status' => true,
                        'api_url'    => 'https://r.inews.qq.com/api/ip2city',
                        'ip_key'     => 'ip'
                    ),
                    array(
                        'api_name'   => 'ipify',
                        'api_status' => true,
                        'api_url'    => 'https://api.ipify.org?format=json',
                        'ip_key'     => 'ip'
                    ),
                    array(
                        'api_name'   => 'aa1',
                        'api_status' => true,
                        'api_url'    => 'https://v.api.aa1.cn/api/myip/index.php?aa1=json',
                        'ip_key'     => 'myip'
                    ),
                    array(
                        'api_name'   => 'cip',
                        'api_status' => true,
                        'api_url'    => 'https://whois.pconline.com.cn/ipJson.jsp',
                        'ip_key'     => 'ip',
                        'is_jsonp'   => true,
                        'jsonp_callback' => 'IPCallBack'
                    ),
					array(
                        'api_name'   => 'upaiyun',
                        'api_status' => true,
                        'api_url'    => 'https://pubstatic.b0.upaiyun.com/?_upnode&t=1685039986399',
                        'ip_key'     => 'addr'
                    ),
					array(
                        'api_name'   => 'ipsb',
                        'api_status' => true,
                        'api_url'    => 'https://api.ip.sb/geoip',
                        'ip_key'     => 'ip'
                    ),
					array(
                        'api_name'   => 'ipapi',
                        'api_status' => true,
                        'api_url'    => 'https://pro.ip-api.com/json/?fields=16985625&key=EEKS6bLi6D91G1p',
                        'ip_key'     => 'query'
                    ),
					array(
                        'api_name'   => 'ip234',
                        'api_status' => true,
                        'api_url'    => 'https://ip234.in/ip.json',
                        'ip_key'     => 'ip'
                    ),
                )
            ),
        )
    ));

	CSF::createSection($prefix, array(
		'title'       => 'SEO增强',
		'icon'        => 'fa fa-handshake-o',
		'description' => '',
		'fields'      => array(
			array(
				'id'      => 'google_check_url',
				'type'    => 'text',
				'title'   => '启用google网址安全检测',
				'default' => 'AIzaSyDHscRszNTiY6J1CNYVrN0O6JmxfCSnaDk',
				'desc'	  => '设置go.php网址检测google网址安全检测token，比如：AIzaSyDHscRszNTiY6J1CNYVrN0O6JmxfCSnaDk，如果为空则默认使用腾讯网址安全检测。',
			),
			array(
				'id'       => 'baidu_record_cookie',
				'type'     => 'text',
				'title'    => esc_html__( '百度收录检测请求 Cookie 标头', 'your-textdomain-here' ),
				'desc'     => '介绍：检测百度是否收录指定文章时必须带有正确的 Cookie，否则会检测失败<br>
		获取方法：[<a href="https://www.baidu.com/s?wd=www.hexsen.com&rn=1&tn=json&ie=utf-8&cl=3&f=9" target="_blank">进入此网址</a>] 后打开浏览器开发者工具，再次刷新该网址的窗口，查看调试界面网络栏中的原始请求标头中的 Cookie 请求头的值，复制粘贴到这里即可',
				'default'  => false,
			),
			array(
				'id'       => 'baidu_record_user_agent',
				'type'     => 'text',
				'title'    => esc_html__( '百度收录检测请求 User-Agent 标头', 'your-textdomain-here' ),
				'desc'     => '介绍：检测百度是否收录指定文章时必须带有正确的 User-Agent，否则会检测失败<br>
		获取方法：[<a href="https://www.baidu.com/s?wd=www.hexsen.com&rn=1&tn=json&ie=utf-8&cl=3&f=9" target="_blank">进入此网址</a>] 后打开浏览器开发者工具，再次刷新该网址的窗口，查看调试界面网络栏中的原始请求标头中的 User-Agent 请求头的值，复制粘贴到这里即可',
				'default'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
			),
			array(
				'id'       => 'baidu_record',
				'type'     => 'switcher',
				'title'    => esc_html__( '文章页百度自动推送', 'your-textdomain-here' ),
				'desc'     => '启用后需要同步启用zibll本身的百度token和推送按钮！',
				'default'  => false,
			),
		)
	));
	
	CSF::createSection($prefix, array(
		'title'       => '缓存相关',
		'icon'        => 'fa fa-handshake-o',
		'description' => '此处仅限于使用静态缓存的，否则不要开启，会增加两次阅读量。',
		'fields'      => array(
			array(
				'id'       => 'ajax_number_s',
				'type'     => 'switcher',
				'title'    => esc_html__( '静态缓存浏览量', 'your-textdomain-here' ),
				'desc'     => '启用后在静态缓存状态下，可以更新文章浏览量！',
				'default'  => false,
			),
		)
	));
	
	CSF::createSection($prefix, array(
		'id'      => 'ads',
		'title'       => '广告位相关',
		'icon'        => 'fa fa-handshake-o',
	));
	CSF::createSection($prefix, array(
		'parent'      => 'ads',
		'title'       => '评论区',
		'icon'        => 'fa fa-handshake-o',
		'description' => '',
		'fields'      => array(
			//相关文章下广告
			array(
				'id'     => 'adsense_on_related',
				'type'   => 'switcher',
				'title'  => '评论模块上方模块广告',
				'default' => false,
			),		
			array(
				'id'      => 'adsense_js_to_related',
				'type'    => 'code_editor',
				'title'   => '填写代码',
				'settings' => array(
					'theme' => 'dracula',
					'mode'  => 'javascript',
				),
				'sanitize' => false,
				'default' => '支持js、html、广告联盟代码,推荐放置google内容匹配广告。',
				'dependency' => array('adsense_on_related', '==', 'true'),
			),
		)
	));	
	CSF::createSection($prefix, array(
		'parent'      => 'ads',
		'title'       => '文章内容',
		'icon'        => 'fa fa-handshake-o',
		'description' => '',
		'fields'      => array(
			//文章段落随机显示广告
			array(
				'id'     => 'adsense_on',
				'type'   => 'switcher',
				'title'  => '文章段落随机显示广告',
				'default' => false,
			),		
			array(
				'id'      => 'adsense_js',
				'type'    => 'code_editor',
				'title'   => '填写代码',
				'settings' => array(
					'theme' => 'dracula',
					'mode'  => 'javascript',
				),
				'sanitize' => false,
				'default' => '支持js、html、广告联盟代码，文章内容少于8段，则不会显示广告。',
				'dependency' => array('adsense_on', '==', 'true'),
			),
		)
	));
	
	CSF::createSection($prefix, array(
		'id'      => 'links',
		'title'       => '外链',
		'icon'        => 'fa fa-handshake-o',
	));
	CSF::createSection($prefix, array(
		'parent'      => 'links',
		'id'      => 'links_nav',  // 添加唯一 ID
		'title'       => '网址导航',
		'icon'        => 'fa fa-handshake-o',
		'description' => '',
		'fields'      => array(
			//网址导航 - 链接分类目录
			array(
				'id'     => 'navpage_cats_links',
				'type'   => 'fieldset',
				'title'  => '网址导航 - 链接分类目录',
				'fields' => array(
					array(
						'id'      => 'navpage_cats',
						'type'    => 'select',
						'title'   => '选择分类',
						'chosen'      => true,
						'multiple'    => true,
						'sortable'    => true,
						'options' => $options_linkcats,
						'desc'		 => '注：此处如果没有选项，请去后台-链接中添加链接和链接分类目录，只有不为空的链接分类目录才会显示出来。',
						'placeholder' => '可以选无数个分类'
					),
					array(
						'id'      => 'navpage_desc',
						'type'    => 'text',
						'title'   => '网址导航 - 标题下描述文字',
						'default' => '这里显示的是网址导航的一句话描述...',
					),
				),
			),
		)
	));
	CSF::createSection($prefix, array(
		'parent'      => 'links',
		'id'      => 'links_friend',  // 添加唯一 ID
		'title'       => '友情链接',
		'icon'        => 'fa fa-handshake-o',
		'description' => '',
		'fields'      => array(
			//友情链接页面
			array(
				'id'     => 'pages_frends_links',
				'type'    => 'switcher',
				'title'  => '友情链接页面',
				'desc'		 => '注：开启此选项后可以替代原主题友情链接页面不显示链接分类名的问题。',
				'default' => false,
			),
			//底部友情链接页面
			array(
				'id'     => 'flinks_footer_s',
				'type'    => 'switcher',
				'title'  => '底部友情链接',
				'default' => false,
			),
			array(
				'id'     => 'flinks_s',
				'type'   => 'fieldset',
				'title'  => '底部友情链接设置',
				'fields' => array(
					array(
						'id'      => 'flinks_cat',
						'type'    => 'select',
						'title'   => '选择分类',
						'placeholder' => '可以选无数个分类',
						'inline'      => true,
						'chosen'      => true,
						'multiple'    => true,
						'options' => $options_linkcats,
					),
				),
				'dependency' => array('flinks_footer_s', '==', 'true'),
			),
			array(
				'id'      => 'post_from_s',
				'type'    => 'switcher',
				'title'   => '文章来源',
				'default' => true,
			),
			array(
				'id'		 => 'post_from_function',
				'type'       => 'fieldset',
				'title'      => '文章来源',
				'fields'     => array(
					array(
						'id'      => 'post_from_h1',
						'type'    => 'text',
						'title'   => '来源显示字样',
						'default' => '来源：',
					),
					array(
						'id'      => 'post_from_link_s',
						'type'    => 'switcher',
						'title'   => '来源加链接',
						'default' => true,
					),
				),
				'dependency' => array('post_from_s', '==', 'true'),
			),
			
			array(
				'id'     => 'readwall_limit',
				'type'    => 'switcher',
				'title'  => '读者墙',
				'default' => true,
			),
			array(
				'id'         => 'readwall_limit_time',
				'type'       => 'slider',
				'title'      => '限制在多少月内，单位：月',
				'default'    => '200',
				'max'        => '500',
				'min'        => '0',
				'step'       => '1',
				'desc'		 => '最多可设置500个月，默认200个月',
				'dependency' => array('readwall_limit', '==', 'true'),
			),
			array(
				'id'         => 'readwall_limit_number',
				'type'       => 'slider',
				'title'      => '显示个数',
				'default'    => '200',
				'max'        => '2000',
				'min'        => '0',
				'step'       => '10',
				'desc'       => '默认显示200个月内的留言者上墙',
				'dependency' => array('readwall_limit', '==', 'true'),
			),
		)
	));

    CSF::createSection($prefix, array(
        //'parent'      => 'cap',
        'title'       => '扩展权限',
        'icon'        => 'fa fa-fw fa-codiepie',
        'description' => '',
        'fields'      => array(
            array(
                'content' => '<div style="color:#f97113;"><i class="fa fa-fw fa-info-circle fa-fw"></i>设置允许/禁止邮箱注册后缀
            <div class="c-red">留空则允许所有邮箱注册：<code>如下</code></div>
            </div>',
                'style'   => 'warning',
                'type'    => 'submessage',
            ),
            array(
                'id'           => 'register_email',
                'title'        => '邮箱注册[邮箱后缀]限制',
                'subtitle'     => '请填写邮箱后缀，使用英文逗号,隔开',
				'default'      => 'qq.com,vip.qq.com,foxmail.com,163.com,vip.163.com,126.com,vip.126.com,sohu.com,139.com,189.cn,gmail.com,yeah.net,88.com,111.com,email.cn,21cn.com,sina.com,sina.cn,wo.cn,outlook.com,tencent.com,hexsen.com',
                'attributes'  => array(
                    'rows' => 3,
                ),
				'sanitize'   => false,
                'type'         => 'textarea',
            ),
			array(
				'id'     => 'email_switcher',
				'type'    => 'switcher',
				'title'      => '切换白名单/黑名单',
				'subtitle'     => '默认使用白名单模式',
				'text_on'    => '白名单',
				'text_off'   => '黑名单',
				'text_width' => 80,
				'default' => true,
			),
			array(
				'id'      => 'email_err_tip',
				'type'    => 'text',
				'title'   => '提示语',
				'default' => '此邮箱后缀不允许注册，请使用常用邮箱注册。',
				'desc'		 => '设置邮箱错误提示语',
			),
        )
    ));

    CSF::createSection($prefix, array(
        'title'       => '主题&授权',
        'icon'        => 'fa fa-fw fa-gitlab',
        'description' => '',
        'fields'      => array(
            array(
                'type'    => 'submessage',
                'style'   => 'warning',
                'content' => '<h3 style="color:#fd4c73;"><i class="fa fa-heart fa-fw"></i> 感谢您使用mrhe主题</h3>
                <div><b>首次使用请在下方进行授权验证</b></div>
                <p>mrhe主题是一款良心、厚道的好产品！创作不易，支持正版，从我做起！</p>
                <div style="margin:10px 14px;"><li>mrhe主题官网：<a target="_bank" href="https://hexsen.com/">https://hexsen.com</a></li>
                <li>作者联系方式：<a href="http://wpa.qq.com/msgrd?v=3&amp;uin=770349780&amp;site=qq&amp;menu=yes">QQ 770349780</a></li>
                </div>',
            ),
            CFS_Module_mrhe::aut(), // 主题授权模块
        ),
    ));
    $update_icon    = '';
    //$is_update_data = ZibAut::is_update();
    // if ($is_update_data) {
    //     $update_icon = ' c-red';
    // }
    CSF::createSection('zibll_options', array(
        'title'       => '文档&更新',
        'icon'        => 'fa fa-fw fa-cloud-upload' . $update_icon,
        'description' => '',
        //'fields'      => CFS_Module_mrhe::update($is_update_data),
    ));	
    CSF::createSection($prefix, array(
        'title'  => '备份&导入',
        'icon'   => 'fa fa-fw fa-copy',
        'fields' => CFS_Module_mrhe::backupmrhe(),
    ));


//META BOX SETTING
CSF::createMetabox('mrhe_jingxuan', array(
	'title'     => '精选',
	'post_type' => array('post', 'page', 'plate', 'forum_post'),
	'context'   => 'advanced',
	'data_type' => 'unserialize',
));
CSF::createSection('mrhe_jingxuan', array(
	'fields' => array(
		array(
			'title'   => __('是否精选文章', 'https://hexsen.com'),
			'id'      => 'jx',
			'type'    => 'Checkbox',
			'label'   => '是否精选文章',
			'default' => false // or false
		),
	),
));