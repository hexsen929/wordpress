<?php
/**
 * 翻译功能核心文件
 * 
 * 本文件包含多语言翻译功能的所有核心代码
 */

// 安全检测 - 防止直接访问文件
if (!defined('ABSPATH')) {
    die('禁止直接访问');
}

/**
 * 语言配置数组（与子主题完全一致）
 */
function zibll_get_language_config() {
    return array(
        'chinese_simplified' => array(
            'name' => '简体中文',
            'code' => 'chinese_simplified',
            'icon_class' => 'c-red',
            'icon' => '<svg t="1721624071609" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="33018" width="200" height="200"><path d="M0 166.144h1026.702v694.3H0v-694.3z" fill="#DE2910" p-id="33019"></path><path d="M170.268 245.077l21.647 67.584h70.343l-56.832 41.814 21.646 67.726-56.832-41.813-56.832 41.813 21.646-67.726-56.832-41.814h70.343l21.703-67.584z m183.211-35.3l-1.365 23.382 21.418 8.59-22.442 5.831-1.508 23.382-12.373-19.94-22.329 5.831 14.791-17.977-12.373-19.825 21.418 8.59 14.763-17.863z m78.933 75.748l-10.552 20.85 16.384 16.612-22.813-3.783-10.667 20.85-3.555-23.268-22.813-3.67 20.623-10.666-3.499-23.126 16.27 16.612 20.622-10.41z m-23.608 94.863l6.997 22.328h23.154l-18.688 13.74 6.997 22.214-18.688-13.852-18.688 13.738 7.111-22.1-18.688-13.74h23.154l7.339-22.328z m-55.439 71.367l-1.479 23.381 21.419 8.59-22.329 5.831-1.48 23.382-12.372-19.826-22.443 5.831 14.791-17.977-12.373-19.826 21.532 8.59 14.734-17.976z" fill="#FFDE00" p-id="33020"></path></svg>'
        ),
        'chinese_traditional' => array(
            'name' => '繁體中文',
            'code' => 'chinese_traditional',
            'icon_class' => 'c-red',
            'icon' => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M32 16C32 24.8365 24.8365 32 16 32C7.1635 32 0 24.8365 0 16C5.49563 10.5044 10.1565 5.8435 16 0C24.8365 0 32 7.1635 32 16Z" fill="#D80027"></path><path d="M16 16C16 7.1635 16 6.12175 16 0C7.1635 0 0 7.1635 0 16H16Z" fill="#0052B4"></path><path d="M13.9081 9.36358L11.9541 10.2828L12.9946 12.1753L10.8727 11.7693L10.6038 13.9128L9.12594 12.3363L7.64794 13.9128L7.37912 11.7693L5.25725 12.1751L6.29775 10.2827L4.34375 9.36358L6.29781 8.44451L5.25725 6.55202L7.37906 6.95795L7.648 4.81445L9.12594 6.39095L10.6039 4.81445L10.8727 6.95795L12.9946 6.55202L11.9541 8.44458L13.9081 9.36358Z" fill="#F0F0F0"></path><path d="M9.13457 12.3421C10.7794 12.3421 12.1129 11.0087 12.1129 9.36381C12.1129 7.71893 10.7794 6.3855 9.13457 6.3855C7.48969 6.3855 6.15625 7.71893 6.15625 9.36381C6.15625 11.0087 7.48969 12.3421 9.13457 12.3421Z" fill="#0052B4"></path><path d="M9.126 10.9506C8.25 10.9506 7.543 10.244 7.543 9.36381C7.543 8.48369 8.25 7.77706 9.126 7.77706C10.002 7.77706 10.709 8.48369 10.709 9.36381C10.709 10.244 10.002 10.9506 9.126 10.9506Z" fill="#F0F0F0"></path></svg>'
        ),
        'english' => array(
            'name' => 'English',
            'code' => 'english',
            'icon_class' => 'c-blue',
            'icon' => '<svg t="1721624135832" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="36359" width="200" height="200"><path d="M0 512a512 512 0 1 0 1024 0A512 512 0 1 0 0 512z" fill="#F0F0F0" p-id="36360"></path><path d="M489.74 512H1024c0-46.212-6.16-90.98-17.638-133.566H489.74V512z m0-267.13h459.112a514.7 514.7 0 0 0-118.14-133.566H489.74V244.87zM512 1024c120.498 0 231.252-41.648 318.712-111.304H193.288C280.748 982.352 391.502 1024 512 1024zM75.148 779.13h873.704a508.948 508.948 0 0 0 57.51-133.566H17.638a508.948 508.948 0 0 0 57.51 133.566z" fill="#D80027" p-id="36361"></path><path d="M237.168 79.956h46.658l-43.4 31.53 16.578 51.018-43.398-31.53-43.398 31.53 14.32-44.074a514.814 514.814 0 0 0-99.304 110.674h14.95l-27.626 20.07A511.16 511.16 0 0 0 60.16 271.05l13.192 40.602L48.74 293.77a507.134 507.134 0 0 0-16.744 39.746l14.534 44.736h53.644l-43.4 31.53L73.352 460.8l-43.398-31.53-25.996 18.888A516.936 516.936 0 0 0 0 512h512V0C410.02 1.366 318.012 43.27 237.168 79.956z" fill="#0052B4" p-id="36362"></path></svg>'
        ),
        'japanese' => array(
            'name' => '日本語',
            'code' => 'japanese',
            'icon_class' => 'c-blue-2',
            'icon' => '<svg t="1721624155578" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="37966" width="200" height="200"><path d="M957 512c0-245-198-443.7-442.6-445h-4.7C265 68.3 67 267 67 512s198 443.7 442.6 445h4.7C759 955.7 957 757 957 512z m-709 0c0-145.9 118.2-264.1 264.1-264.1 145.8 0 264 118.2 264 264.1 0 145.8-118.2 264-264 264C366.2 776 248 657.8 248 512z" fill="#FFFFFF" p-id="37967"></path><path d="M776 512c0-145.9-118.2-264.1-264-264.1-145.8 0.1-264 118.3-264 264.1s118.2 264 264.1 264C657.8 776 776 657.8 776 512z" fill="#EB0000" p-id="37968"></path></svg>'
        ),
        'korean' => array(
            'name' => '한국어',
            'code' => 'korean',
            'icon_class' => 'c-yellow',
            'icon' => '<svg class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="39278" width="200" height="200"><path d="M0.1024 0h1024v1024H0.1024z" fill="#FFFFFF" p-id="39279"></path><path d="M513.150493 515.29434m-205.789468 18.764523a206.6432 206.6432 0 1 0 411.578935-37.529047 206.6432 206.6432 0 1 0-411.578935 37.529047Z" fill="#CE1126" p-id="39280"></path><path d="M336.384 408.576a103.2192 103.2192 0 1 0 176.5376 107.008 103.1168 103.1168 0 0 1 176.4352 106.9056A206.336 206.336 0 1 1 336.384 408.576" fill="#003F87" p-id="39281"></path></svg>'
        ),
        'vietnamese' => array(
            'name' => 'Vietnamese',
            'code' => 'vietnamese',
            'icon_class' => 'c-purple',
            'icon' => '<svg class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="40331" width="200" height="200"><path d="M0 512a512 512 0 1 0 1024 0 512 512 0 0 0-1024 0z" fill="#F42F4C" p-id="40332"></path><path d="M512 630.848l168.96 120.128-64.256-195.285333 168.32-124.629334H576.853333L512 238.976l-62.954667 192.085333h-210.069333l167.68 124.629334-63.616 195.285333L512 630.848z" fill="#FFE62E" p-id="40333"></path></svg>'
        ),
        'russian' => array(
            'name' => 'Русский',
            'code' => 'russian',
            'icon_class' => 'c-purple',
            'icon' => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 32C24.8366 32 32 24.8366 32 16C32 7.16344 24.8366 0 16 0C7.16344 0 0 7.16344 0 16C0 24.8366 7.16344 32 16 32Z" fill="#F0F0F0"></path><path d="M31.0048 21.5652C31.648 19.8319 32 17.9571 32 16C32 14.0429 31.648 12.1681 31.0048 10.4348H0.995188C0.352063 12.1681 0 14.0429 0 16C0 17.9571 0.352063 19.8319 0.995188 21.5652L16 22.9565L31.0048 21.5652Z" fill="#0052B4"></path><path d="M16.0048 32C22.8842 32 28.7489 27.658 31.0096 21.5652H1C3.26069 27.658 9.12537 32 16.0048 32Z" fill="#D80027"></path></svg>'
        ),
        'spanish' => array(
            'name' => 'Español',
            'code' => 'spanish',
            'icon_class' => 'c-red',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" id="flag-icon-css-pa" viewBox="0 0 640 480"><defs><clipPath id="a"><path fill-opacity=".7" d="M0 0h640v480H0z"></path></clipPath></defs><g clip-path="url(#a)"><path fill="#fff" d="M0 0h640v480H0z"></path><path fill="#fff" fill-rule="evenodd" d="M92.5 0h477.2v480H92.4z"></path><path fill="#db0000" fill-rule="evenodd" d="M323 3.6h358v221.7H323z"></path><path fill="#0000ab" fill-rule="evenodd" d="M3.2 225.3h319.9V480H3.2zm211.6-47.6l-42-29.4-41.7 29.6 15.5-48L105 100l51.6-.4 16-48 16.3 47.9h51.6l-41.5 30 15.9 48z"></path><path fill="#d80000" fill-rule="evenodd" d="M516.9 413.9l-42.4-27.7-42.1 28 15.6-45.6-42-28 52-.5 16.2-45.4 16.4 45.3h52l-41.8 28.5 16 45.4z"></path></g></svg>'
        ),
        'french' => array(
            'name' => 'Français',
            'code' => 'french',
            'icon_class' => 'c-blue',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" id="flag-icon-css-fr" viewBox="0 0 640 480"><path fill="#fff" d="M0 0h640v480H0z"></path><path fill="#000091" d="M0 0h213.3v480H0z"></path><path fill="#e1000f" d="M426.7 0H640v480H426.7z"></path></svg>'
        ),
        'deutsch' => array(
            'name' => 'Deutsch',
            'code' => 'deutsch',
            'icon_class' => 'c-yellow',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" id="flag-icon-css-de" viewBox="0 0 640 480"><path fill="#ffce00" d="M0 320h640v160H0z"></path><path d="M0 0h640v160H0z"></path><path fill="#d00" d="M0 160h640v160H0z"></path></svg>'
        ),
        'portuguese' => array(
            'name' => 'Português',
            'code' => 'portuguese',
            'icon_class' => 'c-green',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" id="flag-icon-css-pt" viewBox="0 0 640 480"><path fill="#f00" d="M640 0H213.3v480H640z"></path><path fill="#060" d="M0 0h213.3v480H0z"></path><path d="M196.2 196.2a106.7 106.7 0 1 1 0 87.6 120 120 0 1 0 0-87.6" fill="#ff0" stroke="#000" stroke-width=".4"></path></svg>'
        ),
        'thai' => array(
            'name' => 'ไทย',
            'code' => 'thai',
            'icon_class' => 'c-blue',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" id="flag-icon-css-th" viewBox="0 0 640 480"><path fill="#f4f5f8" d="M0 0h640v480H0z"></path><path fill="#2d2a4a" d="M0 162.5h640v160H0z"></path><path fill="#a51931" d="M0 0h640v80H0zm0 400h640v80H0z"></path></svg>'
        ),
        'hindi' => array(
            'name' => 'हिन्दी',
            'code' => 'hindi',
            'icon_class' => 'c-orange',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" id="flag-icon-css-in" viewBox="0 0 640 480"><path fill="#f93" d="M0 0h640v160H0z"></path><path fill="#fff" d="M0 160h640v160H0z"></path><path fill="#128807" d="M0 320h640v160H0z"></path><g transform="matrix(3.2 0 0 3.2 320 240)"><circle r="20" fill="#008"></circle><circle r="17.5" fill="#fff"></circle><circle r="3.5" fill="#008"></circle><g id="d"><g id="c"><g id="b"><g id="a" fill="#008"><circle r=".9" transform="rotate(7.5 -8.8 133.5)"></circle><path d="M0 17.5L.6 7 0 2l-.6 5L0 17.5z"></path></g><use width="100%" height="100%" transform="rotate(15)" xlink:href="#a"></use></g><use width="100%" height="100%" transform="rotate(30)" xlink:href="#b"></use></g><use width="100%" height="100%" transform="rotate(60)" xlink:href="#c"></use></g><use width="100%" height="100%" transform="rotate(120)" xlink:href="#d"></use><use width="100%" height="100%" transform="rotate(240)" xlink:href="#d"></use></g></svg>'
        ),
        'arabic' => array(
            'name' => 'العربية',
            'code' => 'arabic',
            'icon_class' => 'c-green',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" id="flag-icon-css-sa" viewBox="0 0 640 480"><path fill="#006c35" d="M0 0h640v480H0z"></path><g transform="translate(320 240) scale(7.1111)"><g fill="#fff"><path d="M-18 0l4.47 4-4.47 4-4.47-4 4.47-4z"></path></g></g></svg>'
        ),
    );
}

/**
 * 获取当前语言代码
 */
function zibll_get_current_language() {
    $locale = get_locale();
    $lang_map = array(
        'zh_CN' => 'chinese_simplified',
        'zh_TW' => 'chinese_traditional',
        'en_US' => 'english',
        'ja' => 'japanese',
        'ko_KR' => 'korean',
        'ru_RU' => 'russian',
        'es_ES' => 'spanish',
        'fr_FR' => 'french',
        'de_DE' => 'deutsch',
        'pt_PT' => 'portuguese',
        'th' => 'thai',
        'hi_IN' => 'hindi',
        'ar' => 'arabic',
    );
    
    return isset($lang_map[$locale]) ? $lang_map[$locale] : 'chinese_simplified';
}

/**
 * 生成语言切换器HTML（头部导航栏）- 与子主题完全一致
 */
function zibll_get_language_switcher_html() {
    $translation_options = get_option('zibll_plugin_option');
    
    if (empty($translation_options['translation_s'])) {
        return '';
    }

    // 获取嵌套配置
    $trans_opts = !empty($translation_options['translation_options']) 
        ? $translation_options['translation_options'] 
        : array();

    // 获取语言配置
    $language_config = zibll_get_language_config();
    
    // 获取启用的语言
    $enabled_languages = !empty($trans_opts['translation_enabled_languages']) 
        ? $trans_opts['translation_enabled_languages'] 
        : array('chinese_simplified', 'chinese_traditional', 'english', 'japanese', 'korean');
    
    // 获取默认语言
    $default_language = !empty($trans_opts['translation_default_lang']) 
        ? $trans_opts['translation_default_lang'] 
        : 'chinese_simplified';
    
    // 生成主按钮
    $default_lang_data = isset($language_config[$default_language]) ? $language_config[$default_language] : $language_config['english'];
    
    // 语言标识映射
    $lang_labels = array(
        'chinese_simplified'  => 'CN',
        'chinese_traditional' => 'TW',
        'english'             => 'EN',
        'japanese'            => 'JP',
        'korean'              => 'KR',
        'vietnamese'          => 'VN',
        'russian'             => 'RU',
        'spanish'             => 'ES',
        'french'              => 'FR',
        'deutsch'             => 'DE',
        'portuguese'          => 'PT',
        'thai'                => 'TH',
        'hindi'               => 'HI',
        'arabic'              => 'AR',
    );
    
    $default_label = isset($lang_labels[$default_language]) ? $lang_labels[$default_language] : 'EN';
    
    $html  = '<span class="hover-show inline-block ml10 ignore">';
    $html .= '<a href="javascript:translate.changeLanguage(\'' . esc_js($default_lang_data['code']) . '\');"';
    $html .= ' class="toggle-radius" style="overflow:hidden;position:relative;width:50px;">';
    
    // 使用 Google Translate 图标作为主按钮图标（与子主题完全一致）
    $translate_icon = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 998.1 998.3" xml:space="preserve" width="24" height="24"><defs><linearGradient id="translate-grad-a" gradientUnits="userSpaceOnUse" x1="534.3" y1="433.2" x2="998.1" y2="433.2"><stop offset="0" stop-color="#fff" stop-opacity=".2"></stop><stop offset="1" stop-color="#fff" stop-opacity=".02"></stop></linearGradient><radialGradient id="translate-grad-b" cx="65.208" cy="19.366" r="1398.271" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#fff" stop-opacity=".1"></stop><stop offset="1" stop-color="#fff" stop-opacity="0"></stop></radialGradient></defs><path fill="#DBDBDB" d="M931.7 998.3c36.5 0 66.4-29.4 66.4-65.4V265.8c0-36-29.9-65.4-66.4-65.4H283.6l260.1 797.9h388z"></path><path fill="#DCDCDC" d="M931.7 230.4c9.7 0 18.9 3.8 25.8 10.6 6.8 6.7 10.6 15.5 10.6 24.8v667.1c0 9.3-3.7 18.1-10.6 24.8-6.9 6.8-16.1 10.6-25.8 10.6H565.5L324.9 230.4h606.8m0-30H283.6l260.1 797.9h388c36.5 0 66.4-29.4 66.4-65.4V265.8c0-36-29.9-65.4-66.4-65.4z"></path><polygon fill="#4352B8" points="482.3,809.8 543.7,998.3 714.4,809.8"></polygon><path fill="#607988" d="M936.1 476.1V437H747.6v-63.2h-61.2V437H566.1v39.1h239.4c-12.8 45.1-41.1 87.7-68.7 120.8-48.9-57.9-49.1-76.7-49.1-76.7h-50.8s2.1 28.2 70.7 108.6c-22.3 22.8-39.2 36.3-39.2 36.3l15.6 48.8s23.6-20.3 53.1-51.6c29.6 32.1 67.8 70.7 117.2 116.7l32.1-32.1c-52.9-48-91.7-86.1-120.2-116.7 38.2-45.2 77-102.1 85.2-154.2H936v.1z"></path><path fill="#4285F4" d="M66.4 0C29.9 0 0 29.9 0 66.5v677c0 36.5 29.9 66.4 66.4 66.4h648.1L454.4 0h-388z"></path><path fill="url(#translate-grad-a)" d="M534.3 200.4h397.4c36.5 0 66.4 29.4 66.4 65.4V666L534.3 200.4z"></path><path fill="#EEEEEE" d="M371.4 430.6c-2.5 30.3-28.4 75.2-91.1 75.2-54.3 0-98.3-44.9-98.3-100.2s44-100.2 98.3-100.2c30.9 0 51.5 13.4 63.3 24.3l41.2-39.6c-27.1-25-62.4-40.6-104.5-40.6-86.1 0-156 69.9-156 156s69.9 156 156 156c90.2 0 149.8-63.3 149.8-152.6 0-12.8-1.6-22.2-3.7-31.8h-146v53.4l91 .1z"></path><path fill="url(#translate-grad-b)" d="M931.7 200.4H518.8L454.4 0h-388C29.9 0 0 29.9 0 66.5v677c0 36.5 29.9 66.4 66.4 66.4h415.9l61.4 188.4h388c36.5 0 66.4-29.4 66.4-65.4V265.8c0-36-29.9-65.4-66.4-65.4z"></path></svg>';
    
    $html .= '<span class="icon" style="margin-left:-20px;">' . $translate_icon . '</span>';
    $html .= '<span class="icon" style="margin-left:20px;">' . $default_label . '</span>';
    $html .= '</a>';

    $html .= '<div class="hover-show-con dropdown-menu drop-newadd">';
    foreach ($enabled_languages as $code) {
        if (empty($language_config[$code])) {
            continue;
        }
        $lang = $language_config[$code];
        $html .= '<a rel="nofollow" class="btn-newadd" ';
        $html .= 'href="javascript:translate.changeLanguage(\'' . esc_js($lang['code']) . '\');">';
        $html .= '<icon class="' . esc_attr($lang['icon_class']) . '">';
        $html .= $lang['icon'];
        $html .= '</icon>';
        $html .= '<text class="ignore">' . esc_html($lang['name']) . '</text>';
        $html .= '</a>';
    }
    $html .= '</div>';
    $html .= '</span><span class="ml10"></span>';

    return $html;
}

/**
 * 生成右侧浮动按钮样式的多语言切换按钮HTML（与子主题完全一致）
 */
function zibll_get_language_switcher_float_html() {
    $translation_options = get_option('zibll_plugin_option');
    
    // 修复：使用 translation_s 而不是 translation_enabled
    if (empty($translation_options['translation_s'])) {
        return '';
    }

    // 获取嵌套配置
    $trans_opts = !empty($translation_options['translation_options']) 
        ? $translation_options['translation_options'] 
        : array();

    // 获取语言配置
    $language_config = zibll_get_language_config();
    
    // 获取启用的语言
    $enabled_languages = !empty($trans_opts['translation_enabled_languages']) 
        ? $trans_opts['translation_enabled_languages'] 
        : array('chinese_simplified', 'chinese_traditional', 'english', 'japanese', 'korean');
    
    // 获取默认语言
    $default_language = !empty($trans_opts['translation_default_lang']) 
        ? $trans_opts['translation_default_lang'] 
        : 'chinese_simplified';
    
    // 生成主按钮
    $default_lang_data = isset($language_config[$default_language]) ? $language_config[$default_language] : $language_config['english'];
    
    // 检测是否是移动端
    $is_mobile = wp_is_mobile();
    $tooltip = !$is_mobile ? ' data-toggle="tooltip"' : '';
    
    // 使用 FontAwesome 图标，与父主题风格保持一致
    $translate_icon = '<i class="fa fa-language"></i>';
    
    // 删除单语言特殊处理，始终使用完整的 hover-show 结构
    
    // 构建下拉菜单内容
    $hover_content = '';
    foreach ($enabled_languages as $code) {
        if (empty($language_config[$code])) {
            continue;
        }
        $lang = $language_config[$code];
        $hover_content .= '<a class="btn-newadd" href="javascript:translate.changeLanguage(\'' . esc_js($lang['code']) . '\');">';
        $hover_content .= '<icon class="' . esc_attr($lang['icon_class']) . '">';
        $hover_content .= $lang['icon'];
        $hover_content .= '</icon>';
        $hover_content .= '<text class="ignore">' . esc_html($lang['name']) . '</text>';
        $hover_content .= '</a>';
    }
    
    // 按照子主题的确切结构构建
    $html = '<span class="newadd-btns hover-show float-btn translation-float-btn"' . $tooltip . ' data-placement="left">';
    $html .= $translate_icon;
    $html .= '<div class="hover-show-con dropdown-menu drop-newadd translation-dropdown">';
    $html .= $hover_content;
    $html .= '</div>';
    $html .= '</span>';
    
    return $html;
}

/**
 * 添加语言切换器到头部导航（过滤器版本，与子主题一致）
 */
function zibll_add_language_switcher_to_header($radius_but, $user_id) {
    $translation_options = get_option('zibll_plugin_option');
    
    // 修复：使用 translation_s 而不是 translation_enabled
    if (empty($translation_options['translation_s']) || 
        (function_exists('_mrhe') && _mrhe('translation_s'))) {
        return $radius_but;
    }
    
    // 获取嵌套配置
    $trans_opts = !empty($translation_options['translation_options']) 
        ? $translation_options['translation_options'] 
        : array();
    
    // 获取按钮显示位置设置
    $button_position = !empty($trans_opts['translation_button_position']) 
        ? $trans_opts['translation_button_position'] 
        : 'header';
    
    // 只在 header 或 both 模式下显示
    if ($button_position !== 'header' && $button_position !== 'both') {
        return $radius_but;
    }
    
    // 生成多语言切换按钮HTML
    $language_button = zibll_get_language_switcher_html();
    
    // 将多语言按钮添加到现有的radius按钮之前（与子主题顺序一致）
    return $language_button . $radius_but;
}
add_filter('zib_nav_radius_button', 'zibll_add_language_switcher_to_header', 10, 2);

/**
 * 添加语言切换器到浮动按钮（过滤器版本，与子主题一致）
 */
function zibll_add_language_switcher_to_float($btn) {
    $translation_options = get_option('zibll_plugin_option');
    
    // 修复：使用 translation_s 而不是 translation_enabled
    if (empty($translation_options['translation_s']) || 
        (function_exists('_mrhe') && _mrhe('translation_s'))) {
        return $btn;
    }
    
    // 获取嵌套配置
    $trans_opts = !empty($translation_options['translation_options']) 
        ? $translation_options['translation_options'] 
        : array();
    
    // 获取按钮显示位置设置
    $button_position = !empty($trans_opts['translation_button_position']) 
        ? $trans_opts['translation_button_position'] 
        : 'header';
    
    // 只在 footer 或 both 模式下显示
    if ($button_position !== 'footer' && $button_position !== 'both') {
        return $btn;
    }
    
    // 生成浮动按钮样式的多语言切换按钮
    $language_button = zibll_get_language_switcher_float_html();
    
    // 将多语言按钮添加到现有的浮动按钮之前
    return $language_button . $btn;
}
add_filter('zib_float_right', 'zibll_add_language_switcher_to_float');

/**
 * 加载翻译脚本和样式
 */
function zibll_load_translation_assets() {
    $translation_options = get_option('zibll_plugin_option');
    
    if (empty($translation_options['translation_s'])) {
        return;
    }

    // 注册并加载CSS
    wp_enqueue_style(
        'zibll-translation-css',
        ZIBLL_PLUGIN_URL . '/modules/translation/translation.css',
        array(),
        '2.0.0'
    );
    
    // 注册并加载JS
    wp_enqueue_script(
        'zibll-translation-js',
        ZIBLL_PLUGIN_URL . '/modules/translation/translation.js',
        array('jquery'),
        '2.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'zibll_load_translation_assets');

/**
 * 输出插件翻译配置 - 简化版本，与子主题逻辑保持一致
 */
function zibll_override_theme_translation_config() {
    $translation_options = get_option('zibll_plugin_option');
    
    // 修改：使用 translation_s 检测
    if (empty($translation_options['translation_s'])) {
        return;
    }
    
    // 检查子主题翻译是否已启用，如果是则不输出插件配置
    if (function_exists('_mrhe') && _mrhe('translation_s')) {
        return;
    }

    // 获取嵌套的翻译选项
    $trans_opts = $translation_options['translation_options'];
    
    $default_language_code = !empty($trans_opts['translation_default_lang']) 
        ? $trans_opts['translation_default_lang'] 
        : 'chinese_simplified';

    // 处理忽略设置（使用嵌套配置）
    $ignore_settings = array();
    
    if (!empty($trans_opts['translation_ignore_tags'])) {
        $tags = array_filter(array_map('trim', explode(',', $trans_opts['translation_ignore_tags'])));
        $ignore_settings['tags'] = $tags;
    }
    
    if (!empty($trans_opts['translation_ignore_classes'])) {
        $classes = array_filter(array_map('trim', explode(',', $trans_opts['translation_ignore_classes'])));
        $ignore_settings['classes'] = $classes;
    }
    
    if (!empty($trans_opts['translation_ignore_ids'])) {
        $ids = array_filter(array_map('trim', explode(',', $trans_opts['translation_ignore_ids'])));
        $ignore_settings['ids'] = $ids;
    }
    
    if (!empty($trans_opts['translation_ignore_texts'])) {
        $texts = array_filter(array_map('trim', explode("\n", $trans_opts['translation_ignore_texts'])));
        $ignore_settings['texts'] = $texts;
    }
    
    if (!empty($trans_opts['translation_ignore_regexs'])) {
        $regexs = array_filter(array_map('trim', explode("\n", $trans_opts['translation_ignore_regexs'])));
        $ignore_settings['regexs'] = $regexs;
    }

    // 获取启用的语言列表
    $enabled_languages = !empty($trans_opts['translation_enabled_languages']) 
        ? $trans_opts['translation_enabled_languages'] 
        : array('chinese_simplified', 'chinese_traditional', 'english', 'japanese', 'korean');
    
    // 获取按钮位置
    $button_position = !empty($trans_opts['translation_button_position']) 
        ? $trans_opts['translation_button_position'] 
        : 'header';
    
    // 获取语言配置
    $language_config = zibll_get_language_config();
    
    // 构建语言列表
    $languages = array();
    foreach ($enabled_languages as $code) {
        if (isset($language_config[$code])) {
            $lang = $language_config[$code];
            $languages[] = array(
                'code' => $lang['code'],
                'name' => $lang['name'],
                'icon_class' => $lang['icon_class'],
                'icon' => $lang['icon']
            );
        }
    }

    $translation_config = array(
        'enabled' => true,
        'service' => $trans_opts['translation_service'],
        'default_language' => $default_language_code,
        'button_position' => $button_position,
        'languages' => $languages,
        'auto_detect' => $trans_opts['translation_auto_detect'],
        'translate_js' => $trans_opts['translate_js'],
        'cdn_url' => 'https://cdn.jsdelivr.net/gh/xnx3/translate/translate.js/translate.min.js',
        'ignore' => $ignore_settings
    );
    ?>
    <script type="text/javascript">
    // 使用独立命名空间，不覆盖子主题配置
    window._zibll_plugin = window._zibll_plugin || {};
    window._zibll_plugin.TRANSLATION = <?php echo wp_json_encode($translation_config); ?>;
    </script>
    <?php
}
// 使用正常优先级，不覆盖子主题配置
add_action('wp_footer', 'zibll_override_theme_translation_config', 10);

