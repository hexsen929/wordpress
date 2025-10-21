<?php
//wp_cache_flush(); 
add_action('csf_mrhe_options_saved', 'wp_cache_flush');

require 'functions-meta.php';

/**
 * 多语言切换按钮功能
 * 根据设置在不同位置显示多语言切换按钮
 */

// 导航栏右侧按钮
function add_language_switcher_to_header($radius_but, $user_id) {
    // 检查是否启用翻译功能
    if (!_mrhe('translation_s')) {
        return $radius_but;
    }
    
    // 获取按钮显示位置设置
    $button_position = _mrhe('translation_options')['translation_button_position'];
    
    // 只在 header 或 both 模式下显示
    if ($button_position !== 'header' && $button_position !== 'both') {
        return $radius_but;
    }
    
    // 生成多语言切换按钮HTML
    $language_button = get_language_switcher_html();
    
    // 将多语言按钮添加到现有的radius按钮之后
    return $language_button . $radius_but;
}
add_filter('zib_nav_radius_button', 'add_language_switcher_to_header', 10, 2);

// 右侧浮动按钮
function add_language_switcher_to_float($btn) {
    // 检查是否启用翻译功能
    if (!_mrhe('translation_s')) {
        return $btn;
    }
    
    // 获取按钮显示位置设置
    $button_position = _mrhe('translation_options')['translation_button_position'];
    
    // 只在 footer 或 both 模式下显示
    if ($button_position !== 'footer' && $button_position !== 'both') {
        return $btn;
    }
    
    // 生成浮动按钮样式的多语言切换按钮
    $language_button = get_language_switcher_float_html();
    
    // 将多语言按钮添加到浮动按钮前面
    return $language_button . $btn;
}
add_filter('zib_float_right', 'add_language_switcher_to_float');

/**
 * 获取语言配置数据
 */
function get_language_config() {
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
            'icon' => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M32 16C32 24.8365 24.8365 32 16 32C7.1635 32 0 24.8365 0 16C5.49563 10.5044 10.1565 5.8435 16 0C24.8365 0 32 7.1635 32 16Z" fill="#D80027"></path><path d="M16 16C16 7.1635 16 6.12175 16 0C7.1635 0 0 7.1635 0 16H16Z" fill="#0052B4"></path><path d="M13.9081 9.36358L11.9541 10.2828L12.9946 12.1753L10.8727 11.7693L10.6038 13.9128L9.12594 12.3363L7.64794 13.9128L7.37912 11.7693L5.25725 12.1751L6.29775 10.2827L4.34375 9.36358L6.29781 8.44451L5.25725 6.55202L7.37906 6.95795L7.648 4.81445L9.12594 6.39095L10.6039 4.81445L10.8727 6.95795L12.9946 6.55202L11.9541 8.44458L13.9081 9.36358Z" fill="#F0F0F0"></path><path d="M9.13457 12.3421C10.7794 12.3421 12.1129 11.0087 12.1129 9.36381C12.1129 7.71893 10.7794 6.3855 9.13457 6.3855C7.48969 6.3855 6.15625 7.71893 6.15625 9.36381C6.15625 11.0087 7.48969 12.3421 9.13457 12.3421Z" fill="#0052B4"></path><path d="M9.126 10.9506C8.25094 10.9506 7.53906 10.2387 7.53906 9.36361C7.53906 8.48855 8.251 7.77661 9.126 7.77661C10.0011 7.77661 10.713 8.48855 10.713 9.36361C10.7129 10.2387 10.0009 10.9506 9.126 10.9506Z" fill="#F0F0F0"></path></svg>'
        ),
        'english' => array(
            'name' => 'English',
            'code' => 'english',
            'icon_class' => 'c-blue',
            'icon' => '<svg t="1721624135832" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="36359" width="200" height="200"><path d="M0 512a512 512 0 1 0 1024 0A512 512 0 1 0 0 512z" fill="#F0F0F0" p-id="36360"></path><path d="M489.74 512H1024c0-46.212-6.16-90.98-17.638-133.566H489.74V512z m0-267.13h459.112a514.7 514.7 0 0 0-118.14-133.566H489.74V244.87zM512 1024c120.498 0 231.252-41.648 318.712-111.304H193.288C280.748 982.352 391.502 1024 512 1024zM75.148 779.13h873.704a508.948 508.948 0 0 0 57.51-133.566H17.638a508.948 508.948 0 0 0 57.51 133.566z" fill="#D80027" p-id="36361"></path><path d="M237.168 79.956h46.658l-43.4 31.53 16.578 51.018-43.398-31.53-43.398 31.53 14.32-44.074a514.814 514.814 0 0 0-99.304 110.674h14.95l-27.626 20.07A511.16 511.16 0 0 0 60.16 271.05l13.192 40.602L48.74 293.77a507.134 507.134 0 0 0-16.744 39.746l14.534 44.736h53.644l-43.4 31.53L73.352 460.8l-43.398-31.53-25.996 18.888A516.936 516.936 0 0 0 0 512h512V0C410.856 0 316.57 29.34 237.168 79.956zM257.004 460.8l-43.398-31.53-43.398 31.53 16.578-51.018-43.4-31.53h53.644l16.576-51.018 16.576 51.018h53.644l-43.4 31.53 16.578 51.018z m-16.578-200.166l16.578 51.018-43.398-31.53-43.398 31.53 16.578-51.018-43.4-31.53h53.644l16.576-51.018 16.576 51.018h53.644l-43.4 31.53zM440.656 460.8l-43.398-31.53-43.398 31.53 16.578-51.018-43.4-31.53h53.644l16.576-51.018 16.576 51.018h53.644l-43.4 31.53 16.578 51.018z m-16.578-200.166l16.578 51.018-43.398-31.53-43.398 31.53 16.578-51.018-43.4-31.53h53.644l16.576-51.018 16.576 51.018h53.644l-43.4 31.53z m0-149.148l16.578 51.018-43.398-31.53-43.398 31.30 16.578-51.018-43.4-31.53h53.644l16.576-51.018 16.576 51.018h53.644l-43.4 31.53z" fill="#0052B4" p-id="36362"></path></svg>'
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
            'name' => 'Việt Nam',
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
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" id="flag-icon-css-fr" viewBox="0 0 640 480"><g fill-rule="evenodd" stroke-width="1pt"><path fill="#fff" d="M0 0h640v480H0z"></path><path fill="#00267f" d="M0 0h213.3v480H0z"></path><path fill="#f31830" d="M426.7 0H640v480H426.7z"></path></g></svg>'
        ),
        'deutsch' => array(
            'name' => 'Deutsch',
            'code' => 'deutsch',
            'icon_class' => '',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" id="flag-icon-css-de" viewBox="0 0 640 480"><path fill="#ffce00" d="M0 320h640v160H0z"></path><path d="M0 0h640v160H0z"></path><path fill="#d00" d="M0 160h640v160H0z"></path></svg>'
        ),
        'portuguese' => array(
            'name' => 'Português',
            'code' => 'portuguese',
            'icon_class' => '',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" id="flag-icon-css-pt" viewBox="0 0 640 480"><path fill="#f00" d="M640 0H213.3v480H640z"></path><path fill="#060" d="M0 0h213.3v480H0z"></path><path d="M196.2 196.2a106.7 106.7 0 1 1 0 87.6 120 120 0 1 0 0-87.6" fill="#ff0" stroke="#000" stroke-width=".4"></path></svg>'
        ),
        'thai' => array(
            'name' => 'ไทย',
            'code' => 'thai',
            'icon_class' => 'c-red',
            'icon' => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 32C24.8366 32 32 24.8366 32 16C32 7.16344 24.8366 0 16 0C7.16344 0 0 7.16344 0 16C0 24.8366 7.16344 32 16 32Z" fill="#F0F0F0"></path><path d="M32 13.9131H0C0 12.6464 0.152063 11.4189 0.433125 10.2432H31.5669C31.8479 11.4189 32 12.6464 32 13.9131Z" fill="#A41E36"></path><path d="M31.5669 21.7565H0.433125C0.152063 20.5808 0 19.3534 0 18.087H32C32 19.3534 31.8479 20.5808 31.5669 21.7565Z" fill="#A41E36"></path><path d="M16 32C22.3304 32 27.7434 28.0784 30.2635 22.6087H1.73648C4.2566 28.0784 9.66956 32 16 32Z" fill="#A41E36"></path><path d="M1.73648 9.39133H30.2635C27.7434 3.92162 22.3304 0 16 0C9.66956 0 4.2566 3.92162 1.73648 9.39133Z" fill="#A41E36"></path><path d="M32 18.087C32 17.3971 31.9584 16.7166 31.8783 16.0472H0.121719C0.0416407 16.7166 0 17.3971 0 18.087H32Z" fill="#1A237B"></path><path d="M31.8783 15.9528H0.121719C0.0416407 15.2834 0 14.6029 0 13.9131H32C32 14.6029 31.9584 15.2834 31.8783 15.9528Z" fill="#1A237B"></path></svg>'
        ),
        'hindi' => array(
            'name' => 'हिन्दी',
            'code' => 'hindi',
            'icon_class' => 'c-orange',
            'icon' => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 32C24.8366 32 32 24.8366 32 16C32 7.16344 24.8366 0 16 0C7.16344 0 0 7.16344 0 16C0 24.8366 7.16344 32 16 32Z" fill="#F0F0F0"></path><path d="M32 10.6667H0C0 7.65536 0.862437 4.84992 2.36081 2.48174L16 10.6667L29.6392 2.48174C31.1376 4.84992 32 7.65536 32 10.6667Z" fill="#FF9811"></path><path d="M32 21.3333H0C0 24.3446 0.862437 27.1501 2.36081 29.5183L16 21.3333L29.6392 29.5183C31.1376 27.1501 32 24.3446 32 21.3333Z" fill="#6DA544"></path><path d="M16 19.2174C17.7976 19.2174 19.2522 17.7627 19.2522 15.9652C19.2522 14.1676 17.7976 12.713 16 12.713C14.2024 12.713 12.7478 14.1676 12.7478 15.9652C12.7478 17.7627 14.2024 19.2174 16 19.2174Z" fill="#0052B4"></path></svg>'
        ),
        'arabic' => array(
            'name' => 'العربية',
            'code' => 'arabic',
            'icon_class' => '',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" id="flag-icon-css-ae" viewBox="0 0 640 480"><path fill="#00732f" d="M0 0h640v160H0z"></path><path fill="#fff" d="M0 160h640v160H0z"></path><path d="M0 320h640v160H0z"></path><path fill="red" d="M0 0h220v480H0z"></path></svg>'
        )
    );
}

/**
 * 生成多语言切换按钮的HTML
 */
function get_language_switcher_html() {
    // 获取语言配置
    $language_config = get_language_config();
    
    // 获取翻译配置
    $translation_options = _mrhe('translation_options');
    
    // 获取启用的语言（安全访问）
    $enabled_languages = !empty($translation_options['translation_enabled_languages']) 
        ? $translation_options['translation_enabled_languages'] 
        : array();
    
    // 获取默认语言（安全访问）
    $default_language = !empty($translation_options['translation_default_lang']) 
        ? $translation_options['translation_default_lang'] 
        : 'english';
    
    // 生成主按钮
    $default_lang_data = isset($language_config[$default_language]) ? $language_config[$default_language] : $language_config['english'];
    
    // 语言标识映射
    $lang_labels = array(
        'chinese_simplified'  => 'CN',
        'chinese_traditional' => 'TW',
        'english'             => 'EN',
        'japanese'            => 'JP',
        'korean'              => 'KR',
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
    // 使用 Google Translate 图标作为主按钮图标
    $translate_icon = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 998.1 998.3" xml:space="preserve" width="24" height="24"><defs><linearGradient id="translate-grad-a" gradientUnits="userSpaceOnUse" x1="534.3" y1="433.2" x2="998.1" y2="433.2"><stop offset="0" stop-color="#fff" stop-opacity=".2"></stop><stop offset="1" stop-color="#fff" stop-opacity=".02"></stop></linearGradient><radialGradient id="translate-grad-b" cx="65.208" cy="19.366" r="1398.271" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#fff" stop-opacity=".1"></stop><stop offset="1" stop-color="#fff" stop-opacity="0"></stop></radialGradient></defs><path fill="#DBDBDB" d="M931.7 998.3c36.5 0 66.4-29.4 66.4-65.4V265.8c0-36-29.9-65.4-66.4-65.4H283.6l260.1 797.9h388z"></path><path fill="#DCDCDC" d="M931.7 230.4c9.7 0 18.9 3.8 25.8 10.6 6.8 6.7 10.6 15.5 10.6 24.8v667.1c0 9.3-3.7 18.1-10.6 24.8-6.9 6.8-16.1 10.6-25.8 10.6H565.5L324.9 230.4h606.8m0-30H283.6l260.1 797.9h388c36.5 0 66.4-29.4 66.4-65.4V265.8c0-36-29.9-65.4-66.4-65.4z"></path><polygon fill="#4352B8" points="482.3,809.8 543.7,998.3 714.4,809.8"></polygon><path fill="#607988" d="M936.1 476.1V437H747.6v-63.2h-61.2V437H566.1v39.1h239.4c-12.8 45.1-41.1 87.7-68.7 120.8-48.9-57.9-49.1-76.7-49.1-76.7h-50.8s2.1 28.2 70.7 108.6c-22.3 22.8-39.2 36.3-39.2 36.3l15.6 48.8s23.6-20.3 53.1-51.6c29.6 32.1 67.8 70.7 117.2 116.7l32.1-32.1c-52.9-48-91.7-86.1-120.2-116.7 38.2-45.2 77-102.1 85.2-154.2H936v.1z"></path><path fill="#4285F4" d="M66.4 0C29.9 0 0 29.9 0 66.5v677c0 36.5 29.9 66.4 66.4 66.4h648.1L454.4 0h-388z"></path><path fill="url(#translate-grad-a)" d="M534.3 200.4h397.4c36.5 0 66.4 29.4 66.4 65.4V666L534.3 200.4z"></path><path fill="#EEEEEE" d="M371.4 430.6c-2.5 30.3-28.4 75.2-91.1 75.2-54.3 0-98.3-44.9-98.3-100.2s44-100.2 98.3-100.2c30.9 0 51.5 13.4 63.3 24.3l41.2-39.6c-27.1-25-62.4-40.6-104.5-40.6-86.1 0-156 69.9-156 156s69.9 156 156 156c90.2 0 149.8-63.3 149.8-152.6 0-12.8-1.6-22.2-3.7-31.8h-146v53.4l91 .1z"></path><path fill="url(#translate-grad-b)" d="M931.7 200.4H518.8L454.4 0h-388C29.9 0 0 29.9 0 66.5v677c0 36.5 29.9 66.4 66.4 66.4h415.9l61.4 188.4h388c36.5 0 66.4-29.4 66.4-65.4V265.8c0-36-29.9-65.4-66.4-65.4z"></path></svg>';
    $html .= '<span class="icon" style="margin-left:-20px;">' . $translate_icon . '</span>';
    $html .= '<span class="icon" style="margin-left:20px;">' . $default_label . '</span>';
    $html .= '</a>';

    $html .= '<div class="hover-show-con dropdown-menu drop-newadd">';
    if (!empty($enabled_languages) && is_array($enabled_languages)) {
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
    }
    $html .= '</div>';
    $html .= '</span><span class="ml10"></span>';

    return $html;
}

/**
 * 生成右侧浮动按钮样式的多语言切换按钮HTML
 */
function get_language_switcher_float_html() {
    // 获取语言配置
    $language_config = get_language_config();
    
    // 获取翻译配置
    $translation_options = _mrhe('translation_options');
    
    // 获取启用的语言（安全访问）
    $enabled_languages = !empty($translation_options['translation_enabled_languages']) 
        ? $translation_options['translation_enabled_languages'] 
        : array();
    
    // 获取默认语言（安全访问）
    $default_language = !empty($translation_options['translation_default_lang']) 
        ? $translation_options['translation_default_lang'] 
        : 'english';
    
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
    if (!empty($enabled_languages) && is_array($enabled_languages)) {
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
    }
    
    // 按照父主题 newadd-btns 样式构建多按钮浮动菜单
    $html = '<span class="newadd-btns hover-show float-btn translation-float-btn"' . $tooltip . ' data-placement="left">';
    $html .= $translate_icon;
    $html .= '<div class="hover-show-con dropdown-menu drop-newadd translation-dropdown">';
    $html .= $hover_content;
    $html .= '</div>';
    $html .= '</span>';

    return $html;
}


// 防止修改分类/标签时更新文章的修改时间
function disable_update_modified_date($data, $postarr) {
    // 只在更新文章时生效（不是新建）
    if ($postarr['ID'] > 0) {
        // 保持原来的修改时间
        $current_post = get_post($postarr['ID']);
        if ($current_post) {
            $data['post_modified']     = $current_post->post_modified;
            $data['post_modified_gmt'] = $current_post->post_modified_gmt;
        }
    }
    return $data;
}
//add_filter('wp_insert_post_data', 'disable_update_modified_date', 10, 2);

/**
 * 自动生成短拼音 slug（文章、页面）
 */
function custom_auto_short_post_slug($slug, $post_ID, $post_status, $post_type) {
    if ($slug == '') {
        $post = get_post($post_ID);
        $title = $post->post_title;

        // 转拼音
        $slug = convert_to_pinyin($title);

        // 截取前 3 个词
        $parts = explode('-', $slug);
        $parts = array_slice($parts, 0, 3);
        $slug = implode('-', $parts);

        // 限制最大长度
        $slug = substr($slug, 0, 30);
    }
    return $slug;
}
add_filter('wp_unique_post_slug', 'custom_auto_short_post_slug', 10, 4);

/**
 * 自动生成短拼音 slug（分类、标签等 term）
 */
function custom_auto_short_term_slug($slug, $term, $taxonomy) {
    if ($slug == '') {
        $name = $term->name;

        // 转拼音
        $slug = convert_to_pinyin($name);

        // 截取前 3 个词
        $parts = explode('-', $slug);
        $parts = array_slice($parts, 0, 3);
        $slug = implode('-', $parts);

        // 限制最大长度
        $slug = substr($slug, 0, 30);
    }
    return $slug;
}
add_filter('pre_wp_unique_term_slug', 'custom_auto_short_term_slug', 10, 3);

/**
 * 中文转拼音（简单转换，可替换更强大的库）
 */
function convert_to_pinyin($text) {
    // iconv 会尝试把中文转成拼音
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

    // 替换非法字符为 -
    $text = strtolower(preg_replace("/[^A-Za-z0-9-]+/", '-', $text));

    // 去掉多余的 -
    $text = trim($text, '-');

    return $text;
}

/**
 * 获取Gravatar头像URL
 */
function mrhe_get_gravatar_url($email, $size = 96) {
    $gravatar_base = _mrhe('gravatar_url', 'default');
    //$gravatar_base = "loli";
    $email_hash = md5(strtolower(trim($email)));
    
    //默认头像
    //$default_avatar = urlencode(zib_default_avatar());
    
    switch ($gravatar_base) {
        case 'v2ex':
            $url = 'https://cravatar.com/avatar/' . $email_hash;
            break;
        case 'loli':
            $url = 'https://weavatar.com/avatar/' . $email_hash;
            break;
        case 'ssl':
            $url = 'https://secure.gravatar.com/avatar/' . $email_hash;
            break;
        default:
            $url = 'https://secure.gravatar.com/avatar/' . $email_hash;
    }
    
    //添加参数
    // $url = add_query_arg(array(
    //     's' => $size,
    //     // 'd' => $default_avatar,
    //     // 'r' => 'g'
    // ), $url);
    
    return $url;
}

// 在 WordPress 文章段落中随机位置显示广告
function prefix_insert_post_ads($content)
{
    $pattern = "/<p>.*?<\/p>/";
    $paragraph_count = preg_match_all($pattern, $content); //计算文章的段落数量

    if (_mrhe('adsense_on') && $paragraph_count >= 8 && is_single()) { // 如果文章的段落数量少于8段，则不会插入文章段落广告
        $paragraph_count -= 2;
        $insert_paragraph = rand(3, $paragraph_count);
        $ad_code = _mrhe('adsense_js');
        return prefix_insert_after_paragraph($ad_code, $insert_paragraph, $content);
    }
    return $content;
}
add_filter('the_content', 'prefix_insert_post_ads');
// 插入广告所需的功能代码
function prefix_insert_after_paragraph($insertion, $paragraph_id, $content)
{
    $closing_p = '</p>';
    $paragraphs = explode($closing_p, $content);

    foreach ($paragraphs as $index => $paragraph) {
        if (trim($paragraph)) {
            $paragraphs[$index] .= $closing_p;
        }
        if ($paragraph_id == $index + 1) {
            $paragraphs[$index] .= $insertion;
        }
    }
    return implode('', $paragraphs);
}

function mrhee_scripts()
{
    if (!is_admin()) {
        $url = get_stylesheet_directory_uri();
        $script = array(
            'mrhealljs' => $url . '/mrhecode/js/mrheall.min.js',
            'mrhecommentjs' => $url . '/mrhecode/js/mrhecommentjs.min.js',
            // 'mrhee_right_click' => $url . '/mrhecode/js/right_click/mrhee_right_click.js'
        );
        foreach ($script as $k => $v) {
            wp_register_script($k, $v, array(), '2.9', true);
        };

        wp_enqueue_script('mrhealljs');

        if (is_singular()) {
            wp_enqueue_script('mrhecommentjs');
        };

        // if( !current_user_can( 'manage_options' ) && _mrhe('mrhee_right_click') ){
        //     wp_enqueue_script('mrhee_right_click');
        // }

        wp_enqueue_style('mrheecss', $url . '/mrhecode/css/mrhe.css', array(), '2.9', 'all');
        
        // 按需加载翻译CSS
        if (_mrhe('translation_s')) {
            wp_enqueue_style('mrhe-translation-css', $url . '/mrhecode/css/translation.css', array(), '2.9', 'all');
        }
        
        // Get body classes
        $body_classes = get_body_class();
        // Check if the body has the specific class and enqueue additional scripts/styles if needed
        if (in_array('page-template-pagesnav_links2-php', $body_classes)) {
            wp_enqueue_script('navjs', $url . '/mrhecode/js/navjs.js', array('jquery'), '2.6.1', true);
            wp_enqueue_style('navcss', $url . '/mrhecode/css/navcss.css', array(), '2.6.1', 'all');
        }
    }
}
add_action('wp_enqueue_scripts', 'mrhee_scripts');

//额外模块加载
function mrhe_moloader($name = '', $apply = true)
{
    if (!function_exists($name)) {
        include get_stylesheet_directory() . '/mrhecode/modules/' . $name . '.php';
    }

    if ($apply && function_exists($name)) {
        $name();
    }
}

// 外链跳转函数
function mrhe_link_nofollow($url)
{
    if (strpos($url, '://') !== false && strpos($url, home_url()) === false && !preg_match('/(ed2k|thunder|Flashget|flashget|qqdl):\/\//i', $url)) {
        $url = str_replace($url, home_url() . "/?golink=" . base64_encode($url), $url);
    }
    return $url;
}

//邮箱防采集
function mrhe_security_remove_emails($content)
{
    // 更高效的正则表达式，只匹配合法邮箱格式
    $pattern = '/\b([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\b/i';
    
    // 使用缓存回调来提高性能
    static $email_cache = array();
    
    return preg_replace_callback($pattern, function($matches) use (&$email_cache) {
        $email = $matches[1];
        
        // 检查缓存中是否已经处理过这个邮箱
        if (!isset($email_cache[$email])) {
            $email_cache[$email] = antispambot($email);
        }
        
        return $email_cache[$email];
    }, $content);
}
add_filter('the_content', 'mrhe_security_remove_emails', 20);
add_filter('comment_text', 'mrhe_security_remove_emails', 20);

//将所有超链接改为相对模式
if (!is_admin() && _mrhe('rewrite_urls_set')) {
    ob_start("rewrite_urls");
}
function rewrite_urls($buffer)
{
    $buffer = preg_replace('/("|\')http(s|):\/\/([^"\']*?)' . $_SERVER["HTTP_HOST"] . '/i', '$1//$3' . $_SERVER["HTTP_HOST"], $buffer);
    return $buffer;
}

//评论者链接重定向
function mrhee_comment_author_link($url, $author, $comment_ID)
{
    if (!current_user_can('administrator')) {
        date_default_timezone_set('PRC');
        $limit_days = _mrhe('links_num'); // 天数，代表最后一次评论时间距离今天超过设置天数的话，则隐藏评论链接
        $comment = get_comment($comment_ID);
        if (!empty($comment->comment_author_email)) {
            $last_comment = get_comments(array(
                'author_email' => $comment->comment_author_email,
                'number' => '1',
                'orderby' => 'comment_date',
                'order' => 'DESC',
                'status' => 'approve',
                'type' => 'comment'
            ));

            if (!empty($last_comment) && _mrhe('links_num')) {
                $time_diff = time() - strtotime($last_comment[0]->comment_date);
                if ($time_diff > $limit_days * 24 * 3600) {
                    return $author;
                }
            }
        }
    }
    $pattern = '/href=["\']([^"\']+)/i';
    if (preg_match_all($pattern, $url, $matches)) {
        $url = $matches[1][0];
    }
    if (empty($url) || 'http://' == $url)
        return $author;
    else
        return "<a href='" . home_url() . "/?golink=" . base64_encode($url) . "' rel='external nofollow' target='_blank' class='url'>$author</a>";
}
if (_mrhe('links_open')) {
    add_filter('get_comment_author_link', 'mrhee_comment_author_link', 10, 3);
}

//WordPress 文章关键词自动内链
function tag_sort($a, $b)
{
    if ($a->name == $b->name) return 0;
    return (strlen($a->name) > strlen($b->name)) ? -1 : 1;
}

function tag_link($content)
{
    $match_num_from = 1;    //一个标签少于几次不链接
    $match_num_to = 1;    //一个标签最多链接几次
    $posttags = get_the_tags();
    
    if ($posttags) {
        usort($posttags, "tag_sort");
        
        // 排除不需要添加链接的区域
        $exclude_areas = array();
        
        // 临时保存pre标签内容
        preg_match_all('/<pre.*?>(.*?)<\/pre>/is', $content, $pres);
        foreach ($pres[0] as $i => $pre) {
            $placeholder = "<!--PRE_PLACEHOLDER_{$i}-->";
            $exclude_areas[$placeholder] = $pre;
            $content = str_replace($pre, $placeholder, $content);
        }
        
        // 临时保存img标签
        preg_match_all('/<img.*?>/is', $content, $imgs);
        foreach ($imgs[0] as $i => $img) {
            $placeholder = "<!--IMG_PLACEHOLDER_{$i}-->";
            $exclude_areas[$placeholder] = $img;
            $content = str_replace($img, $placeholder, $content);
        }
        
        // 临时保存已有的链接
        preg_match_all('/<a.*?>.*?<\/a>/is', $content, $links);
        foreach ($links[0] as $i => $link) {
            $placeholder = "<!--LINK_PLACEHOLDER_{$i}-->";
            $exclude_areas[$placeholder] = $link;
            $content = str_replace($link, $placeholder, $content);
        }

        // 处理每个标签
        foreach ($posttags as $tag) {
            $link = get_tag_link($tag->term_id);
            $keyword = $tag->name;
            
            if (empty($keyword)) continue;
            
            // 构建链接
            $url = sprintf(
                '<a href="%s" title="%s" target="_blank">%s</a>',
                esc_url($link),
                esc_attr(sprintf(__('更多关于 %s 的文章'), $keyword)),
                esc_html($keyword)
            );
            
            // 随机替换次数
            $limit = rand($match_num_from, $match_num_to);
            
            // 替换关键词为链接
            $pattern = '/(?<!\pL)(' . preg_quote($keyword, '/') . ')(?!\pL)/u';
            $content = preg_replace($pattern, $url, $content, $limit);
        }
        
        // 恢复被排除的内容
        foreach ($exclude_areas as $placeholder => $original) {
            $content = str_replace($placeholder, $original, $content);
        }
    }
    
    return $content;
}
add_filter('the_content', 'tag_link', 1);

//WordPress 文章中英文数字间自动添加空格（写入数据库）
add_filter('wp_insert_post_data', 'fanly_post_data_autospace', 99, 2);
function fanly_post_data_autospace($data, $postarr)
{
    // 先处理标题（标题里通常没 HTML 标签）
    $data['post_title'] = preg_replace('/([\x{4e00}-\x{9fa5}]+)([A-Za-z0-9_]+)/u', '${1} ${2}', $data['post_title']);
    $data['post_title'] = preg_replace('/([A-Za-z0-9_]+)([\x{4e00}-\x{9fa5}]+)/u', '${1} ${2}', $data['post_title']);

    // 处理正文：只改 HTML 标签外的文字
    $data['post_content'] = preg_replace_callback(
        '/>([^<]+)</u', // 只匹配 HTML 标签外的文本（在 > 和 < 之间）
        function ($matches) {
            $text = $matches[1];
            $text = preg_replace('/([\x{4e00}-\x{9fa5}]+)([A-Za-z0-9_]+)/u', '${1} ${2}', $text);
            $text = preg_replace('/([A-Za-z0-9_]+)([\x{4e00}-\x{9fa5}]+)/u', '${1} ${2}', $text);
            return '>' . $text . '<';
        },
        $data['post_content']
    );

    return $data;
}


// /** 
//  * WordPress 后台管理员免密一键切换其他账号登录 
//  * https://www.dujin.org/fenxiang/wp/10144.html 
//  */
// function wpdx_user_switch_action($actions, $user)
// {
//     $capability = (is_multisite()) ? 'manage_site' : 'manage_options';
//     if (current_user_can($capability)) {
//         $actions['login_as'] = '<a title="以此身份登录" href="' . wp_nonce_url("users.php?action=login_as&users=$user->ID", 'bulk-users') . '">以此身份登录</a>';
//     }
//     return $actions;
// }
// add_filter('user_row_actions', 'wpdx_user_switch_action', 10, 2);

// function wpdx_handle_user_switch_action($sendback, $action, $user_ids)
// {
//     if ($action == 'login_as') {
//         wp_set_auth_cookie($user_ids, true);
//         wp_set_current_user($user_ids);
//     }
//     return admin_url();
// }
// add_filter('handle_bulk_actions-users', 'wpdx_handle_user_switch_action', 10, 3);

//获取浏览器信息
function getBrowser($agent)
{
    switch (true) {
        case preg_match('/MSIE\/([^\s]+)/i', $agent, $matches):
            $outputer = 'Internet Explore ' . $matches[1];
            $icon = 'icon-ie';
            break;
        case preg_match('/Firefox\/([^\s]+)/i', $agent, $matches):
            $outputer = 'FireFox ' . $matches[1];
            $icon = 'icon-firefox';
            break;
        case preg_match('/360\/([^\s]+)/i', $agent, $matches):
            $outputer = '360极速浏览器 ' . $matches[1];
            $icon = 'icon-360';
            break;
        case preg_match('/Edg\/([^\s]+)/i', $agent, $matches) || preg_match('/Edge\/([^\s]+)/i', $agent, $matches):
            $outputer = 'MicroSoft Edge ' . $matches[1];
            $icon = 'icon-edge';
            break;
        case preg_match('/UC\/([^\s]+)/i', $agent, $matches) || preg_match('/UCBrowser\/([^\s]+)/i', $agent, $matches):
            $outputer = 'UC浏览器 ' . $matches[1];
            $icon = 'icon-uc';
            break;
        case preg_match('/QQBrowser\/([^\s]+)/i', $agent, $matches) || preg_match('/QQ\/([^\s]+)/i', $agent, $matches):
            $outputer = 'QQ浏览器 ' . $matches[1];
            $icon = 'icon-qq';
            break;
        case preg_match('/Opera\/([^\s]+)/i', $agent, $matches):
            $outputer = 'Opera ' . $matches[1];
            $icon = 'icon-opera';
            break;
        case preg_match('/wp-iphone\/([^\s]+)/i', $agent, $matches):
            $outputer = 'wordpress ' . $matches[1];
            $icon = 'icon-wordpress';
            break;
        case preg_match('/MicroMessenger\/([^\s]+)/i', $agent, $matches):
            $outputer = '微信 ' . $matches[1];
            $icon = 'icon-weixin';
            break;
        case preg_match('/baiduboxapp\/([^\s]+)/i', $agent, $matches):
            $outputer = '百度 ' . $matches[1];
            $icon = 'icon-baidu';
            break;
        case preg_match('/Chrome\/([^\s]+)/i', $agent, $matches):
            $outputer = 'Google Chrome ' . $matches[1];
            $icon = 'icon-chrome';
            break;
        case preg_match('/Safari\/([^\s]+)/i', $agent, $matches):
            $outputer = 'Safari ' . $matches[1];
            $icon = 'icon-safari';
            break;
        case preg_match('/Quark\/([^\s]+)/i', $agent, $matches):
            $outputer = 'Quark ' . $matches[1];
            $icon = 'icon-quark';
            break;
        default:
            $outputer = '未知浏览器';
            $icon = 'icon-unknown';
            break;
    }
    return array(
        $outputer, //没有识别正确可以输出 $agent 进行调试。
        $icon
    );
}

// 获取操作系统信息
// function modify_user_agent_on_comment( $user_agent ) {
//     //if ( ( isset( $_POST['os_type'] ) ) && ( $_POST['os_type'] != '') ) {
//     if ( isset($_COOKIE['win11']) ) {
//         $user_agent = str_replace( 'Windows NT 10.0', 'Windows NT 11.0', $user_agent );
//     }
//     return $user_agent;
// }
// add_filter( 'pre_comment_user_agent', 'modify_user_agent_on_comment' );

//判断win11
function add_accept_ch_header()
{
    header("Accept-CH: Sec-CH-UA-Platform-Version");
}
add_action('send_headers', 'add_accept_ch_header');
add_filter('pre_comment_user_agent', 'modify_user_agent_on_comment');
function modify_user_agent_on_comment($user_agent)
{
    $http_sec_ch_ua_platform = str_replace('\"', '', $_SERVER['HTTP_SEC_CH_UA_PLATFORM']);
    $http_sec_ch_ua_platform_version = str_replace('\"', '', $_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION']);
    if (isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM']) && $http_sec_ch_ua_platform === 'Windows' && $http_sec_ch_ua_platform_version >= 13) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] . ' (Windows_version:' . $http_sec_ch_ua_platform_version . ')';
    }
    return $user_agent;
}
function getOs($agent)
{
    switch (true) {
        case preg_match("/Windows_version:(\d+)\.\d+/", $agent, $matches):
            $os = 'Windows 11';
            $icon = 'icon-win11';
            break;
        case strpos($agent, 'NT 11.0'):
            $os = 'Windows 11';
            $icon = 'icon-win11';
            break;
        case strpos($agent, 'NT 5.2'):
            $os = 'Windows XP';
            $icon = 'icon-winxp';
            break;
        case strpos($agent, 'NT 6.0'):
            $os = 'Windows Vista';
            $icon = 'icon-win1';
            break;
        case strpos($agent, 'NT 6.1'):
            $os = 'Windows 7';
            $icon = 'icon-win1';
            break;
        case strpos($agent, 'NT 6.2'):
            $os = 'Windows 8';
            $icon = 'icon-win2';
            break;
        case strpos($agent, 'NT 10.0'):
            $os = 'Windows 10';
            $icon = 'icon-win2';
            break;
        case strpos($agent, 'Android 9'):
            $os = 'Android Pie';
            $icon = 'icon-android';
            break;
        case strpos($agent, 'Android 8'):
            $os = 'Android Oreo';
            $icon = 'icon-android';
            break;
        case strpos($agent, 'Android'):
            preg_match("/(?<=Android )[\d\.]{1,}/", $agent, $version);
            $os = 'Android ' . $version[0];
            $icon = 'icon-android';
            break;
            //case preg_match('#iPhone.*.OS ([\d_]+)#i', $agent, $matches):
        case strpos($agent, 'iPhone'):
            preg_match("/(?<=CPU iPhone OS )[\d\_]{1,}/", $agent, $version);
            $os = 'iPhone ' . str_replace('_', '.', $version[0]);
            $icon = 'icon-apple';
            break;
        case strpos($agent, 'iPad'):
            preg_match("/(?<=CPU OS )[\d\_]{1,}/", $agent, $version);
            $os = 'iPad ' . str_replace('_', '.', $version[0]);
            $icon = 'icon-iPad';
            break;
        case strpos($agent, 'Macintosh'):
            preg_match("/(?<=Mac OS X )[\d\_]{1,}/", $agent, $version);
            $os = 'MacOS ' . str_replace('_', '.', $version[0]);
            $icon = 'icon-mac';
            break;
        case strpos($agent, 'Linux'):
            $os = 'Linux';
            $icon = 'icon-linux';
            break;
        default:
            $os = '未知操作系统';
            $icon = 'icon-unknown';
            break;
    }
    return array(
        $os,
        $icon
    );
}

//采用cloudflare中转ip时使用，用于获取js利用第三发ip获取工具的ip替换到评论区显示
add_action('wp_ajax_update_comment_ip', 'update_comment_ip_callback');
add_action('wp_ajax_nopriv_update_comment_ip', 'update_comment_ip_callback');
function update_comment_ip_callback()
{
    if (isset($_POST['ip'])) {
        $comment_author_ip = $_POST['ip'];
        // 存储IP地址到Session，以便在pre_comment_user_ip过滤器中使用
        @session_start();
        $_SESSION['comment_author_ip'] = $comment_author_ip;
    }
    wp_send_json($_SESSION['comment_author_ip']);
}
add_filter('pre_comment_user_ip', 'update_comment_ip', 10, 1);
function update_comment_ip($comment_author_ip)
{
    // 获取存储在Session中的IP地址
    @session_start();
    if (isset($_SESSION['comment_author_ip'])) {
        $comment_author_ip = $_SESSION['comment_author_ip'];
        // 清除Session中的IP地址，以便下次评论不会使用相同的IP地址
        //unset( $_SESSION['comment_author_ip'] );
    }
    return $comment_author_ip;
}

// /**
//  * Cloudflare IP处理模块
//  * 用于获取真实IP替换Cloudflare代理IP
//  */
// function update_comment_ip_callback() {
//     // 如果未启用自定义IP API，直接返回
//     if (!_mrhe('client_ip_api_s')) {
//         wp_send_json_error('未启用自定义IP API');
//         return;
//     }

//     if (!isset($_POST['ip'])) {
//         wp_send_json_error('未提供IP地址');
//     }

//     $ip = sanitize_text_field($_POST['ip']);

//     // 验证IP格式
//     if (!filter_var($ip, FILTER_VALIDATE_IP)) {
//         wp_send_json_error('无效的IP格式');
//     }

//     // 直接返回成功
//     wp_send_json($ip);
// }

// // 只在启用自定义IP API时注册AJAX处理函数
// if (_mrhe('client_ip_api_s')) {
//     add_action('wp_ajax_update_comment_ip', 'update_comment_ip_callback');
//     add_action('wp_ajax_nopriv_update_comment_ip', 'update_comment_ip_callback');
// }

// /**
//  * 过滤评论IP
//  */
// function update_comment_ip($comment_author_ip) {
//     // 如果未启用自定义IP API，直接返回原始IP
//     if (!_mrhe('client_ip_api_s')) {
//         return $comment_author_ip;
//     }

//     // 如果有POST提交的IP，且格式正确，则使用该IP
//     if (isset($_POST['ip']) && filter_var($_POST['ip'], FILTER_VALIDATE_IP)) {
//         return sanitize_text_field($_POST['ip']);
//     }
//     return $comment_author_ip;
// }
// add_filter('pre_comment_user_ip', 'update_comment_ip', 10, 1);

/**
 * @description: 在评论区显示地区
 * @param {*} $info
 * @param {*} $comment
 * @param {*} $depth
 * @return {*}
 */
//地区数据采用 https://github.com/itbdw/ip-database
//require './vendor/autoload.php';
use itbdw\Ip\IpLocation;

function wxs_footer_info_add_province($info, $comment, $depth)
{

    $agent = $comment->comment_agent;
    $user_id = $comment->user_id;
    $user_ip = $comment->comment_author_IP;
    $os = getOs($agent);
    $browser = getBrowser($agent);
    $d_user_ip = '';
    if (current_user_can('manage_options')) {
        $d_user_ip = $user_ip;
    }
    if ($user_ip) {
        if ($user_id == 1 && _mrhe('admin_ip_address')) {
            $convertip = _mrhe('admin_ip_address');
        } else {
            $json = json_encode(IpLocation::getLocation($user_ip), JSON_UNESCAPED_UNICODE);
            $obj = json_decode($json);
            $addrsValue = $obj->area;
            filter_var($obj->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? $is_ipv6 = 'IPV6' : $is_ipv6 = 'IPV4';
            $convertip = $is_ipv6 . '：' . $addrsValue;
        }
        $province = $convertip ? '<span class="useragent tra"><i class="ua-icon ' . $os[1] . ' hint--top" data-hint="' . $os[0] . '"></i><i class="ua-icon ' . $browser[1] . ' hint--top" data-hint="' . $browser[0] . '"></i><span class="fa fa-map-marker"></span> ' . $convertip . '-' . $d_user_ip . '</span>' : '';
        $info = $info . $province;
    }
    return $info;
}
_mrhe('admin_ip_address_swich') ? add_filter('comment_footer_info', 'wxs_footer_info_add_province', 99, 3) : '';

/**
 * @author 教书先生
 * @link https://blog.oioweb.cn
 * @quote_link 使用方法请查看： https://hexsen.com/php-curl.html
 * @date 2020 年 11 月 12 日 18:00:30
 * @msg PHPCurl 封装的方法 万能Curl
 */
function teacher_curl($url, $paras = array())
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if (@$paras['Header']) {
        $Header = $paras['Header'];
    } else {
        $Header[] = "Accept:*/*";
        $Header[] = "Accept-Encoding:gzip,deflate,sdch";
        $Header[] = "Accept-Language:zh-CN,zh;q=0.8";
        $Header[] = "Connection:close";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $Header);
    if (@$paras['ctime']) { // 连接超时
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $paras['ctime']);
    } else {
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    }
    if (@$paras['rtime']) { // 读取超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $paras['rtime']);
    }
    if (@$paras['post']) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $paras['post']);
    }
    if (@$paras['delete']) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, @$paras['delete']);
    }
    if (@$paras['header']) {
        curl_setopt($ch, CURLOPT_HEADER, true);
    }
    if (@$paras['cookie']) {
        curl_setopt($ch, CURLOPT_COOKIE, $paras['cookie']);
    }
    if (@$paras['refer']) {
        if ($paras['refer'] == 1) {
            curl_setopt($ch, CURLOPT_REFERER, 'http://m.qzone.com/infocenter?g_f=');
        } else {
            curl_setopt($ch, CURLOPT_REFERER, $paras['refer']);
        }
    }
    if (@$paras['ua']) {
        curl_setopt($ch, CURLOPT_USERAGENT, $paras['ua']);
    } else {
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36");
    }
    if (@$paras['nobody']) {
        curl_setopt($ch, CURLOPT_NOBODY, 1);
    }
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if (@$paras['GetCookie']) {
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $result = curl_exec($ch);
        preg_match_all("/Set-Cookie: (.*?);/m", $result, $matches);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $headerSize); //状态码
        $body = substr($result, $headerSize);
        $ret = [
            "Cookie" => $matches, "body" => $body, "header" => $header, 'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE)
        ];
        curl_close($ch);
        return $ret;
    }
    $ret = curl_exec($ch);
    if (@$paras['loadurl']) {
        $Headers = curl_getinfo($ch);
        $ret = $Headers['redirect_url'];
    }
    curl_close($ch);
    return $ret;
}

// 生成 token
function generate_token($email, $password)
{
    $url = _mrhe('api_from_function')['api_from_remote_url'] . "/api/v1/tokens";
    $data = [
        'post' => [
            'email' => $email,
            'password' => $password
        ],
        'Header' => [
            'Accept: application/json'
        ]
    ];
    $response = teacher_curl($url, $data);
    $response_data = json_decode($response, true);
    if ($response_data["status"]) {
        return $response_data["data"]["token"];
    } else {
        return false;
    }
}

function upload_file_with_curl($file_path, $remote_url, $token, $strategy_id)
{
    $data = [
        'Header' => [
            "Accept: application/json",
            "Content-Type: multipart/form-data",
            "Authorization: Bearer " . $token,
        ],
        'post' => [
            'file' => new CURLFILE($file_path),
            'strategy_id' => $strategy_id
        ],
    ];
    $response = teacher_curl($remote_url, $data);
    return $response;
}

function upload_and_handle_response($file_path, $remote_url, $token, $strategy_id, $attachment_id, $count)
{
    $response = upload_file_with_curl($file_path, $remote_url, $token, $strategy_id);
    $response_data = json_decode($response, true);
    if ($response_data["status"]) {
        $key = $response_data["data"]["key"];
        add_post_meta($attachment_id, 'wp_attached_api_key_' . $count, $key, true);
        $pathname = $response_data["data"];
    } else {
        wp_die('上传失败，处理错误');
    }
    return $pathname; //日后修改调用
}

function upload_url_to_api($metadata, $attachment_id, $action)
{
    $upload_url = get_option('upload_url_path');
    if (empty($upload_url) || $upload_url !== _mrhe('api_from_function')['api_from_visit_url']) {
        update_option('upload_url_path', _mrhe('api_from_function')['api_from_visit_url']);
    }
    $count = 0;
    $file_path = get_attached_file($attachment_id); // /var/www/html/wp/wp-content/uploads/image.jpg
    $size_width_height = getimagesize($file_path); //获取本地文件属性
    $metadata['width'] = $size_width_height[0];
    $metadata['height'] = $size_width_height[1];

    $remote_url = _mrhe('api_from_function')['api_from_remote_url'] . '/api/v1/upload';

    $uploader_id = get_post_field('post_author', $attachment_id);
    if ($uploader_id == 1) {
        $token = generate_token(_mrhe('api_from_function')['api_from_Lsky_username'], _mrhe('api_from_function')['api_from_Lsky_password']);
    } else {
        $token = generate_token(_mrhe('api_from_function')['api_from_Lsky_other_username'], _mrhe('api_from_function')['api_from_Lsky_other_password']);
    }
    $strategy_id = _mrhe('api_from_function')['api_from_strategy_id'];

    $pathname = upload_and_handle_response($file_path, $remote_url, $token, $strategy_id, $attachment_id, $count); //上传主图片到API

    $path = dirname($file_path); //获取文件路径
    if (_mrhe('api_from_function')['delete_path_img']) {
        wp_delete_file($file_path); //删除本地文件
    }
    update_post_meta($attachment_id, '_wp_attached_file', $pathname['pathname']);
    $metadata['file'] = $pathname['pathname'];

    foreach ($metadata['sizes'] as $size => $sizeimg) {
        $file_path_size = $path . '/' . $sizeimg['file']; //获取略缩图文件路径+文件名.jpg
        if (file_exists($file_path_size)) {
            $pathname = upload_and_handle_response($file_path_size, $remote_url, $token, $strategy_id, $attachment_id, $count + 1); //上传略缩图到API
            if (_mrhe('api_from_function')['delete_path_img']) {
                wp_delete_file($file_path_size); //删除本地文件
            }
            $metadata['sizes'][$size]['file'] = $pathname['name'];
            $count++;
        }
    }

    return $metadata;
}
_mrhe('open_upload_url_to_api') ? add_filter('wp_generate_attachment_metadata', 'upload_url_to_api', 9, 3) . add_action('delete_attachment', 'api_url_delete_attachment') : false;

function delete_api_images($key, $attachment_id, $token)
{
    $data = [
        'Header' => [
            "Accept: application/json",
            "Authorization: Bearer " . $token,
        ],
        'delete' => 'DELETE',
    ];
    $delete_key_url_images = _mrhe('api_from_function')['api_from_remote_url'] . '/api/v1/images/' . $key;
    $response = teacher_curl($delete_key_url_images, $data);
    $response_data = json_decode($response, true);
    if ($response_data['status']) {
        return $response_data['message'];
    } else {
        return $response_data['message'];
    }
}

function api_url_delete_attachment($attachment_id)
{
    $uploader_id = get_post_field('post_author', $attachment_id);
    if ($uploader_id == 1) {
        $token = generate_token(_mrhe('api_from_function')['api_from_Lsky_username'], _mrhe('api_from_function')['api_from_Lsky_password']);
    } else {
        $token = generate_token(_mrhe('api_from_function')['api_from_Lsky_other_username'], _mrhe('api_from_function')['api_from_Lsky_other_password']);
    }

    $i = 0;
    do {
        $key = 'wp_attached_api_key_' . $i;
        $value = get_post_meta($attachment_id, $key, true);
        if ($value === '') {
            break;
        }
        delete_api_images($value, $attachment_id, $token);
        $i++;
    } while (true);

    // 	//同时删除本地文件
    // 	$file_path = get_attached_file($attachment_id);
    // 	if (file_exists($file_path)) {
    // 		wp_delete_file( $file_path );
    // 		$path = dirname( $file_path );
    // 		$metadata = wp_get_attachment_metadata($attachment_id);
    // 		if (isset($metadata['sizes'])) {
    // 			foreach ($metadata['sizes'] as $size => $sizeimg) {
    // 				$file_path_size = $path . '/' . $sizeimg['file'];
    // 				if(file_exists($file_path_size)){
    // 					wp_delete_file( $file_path_size );
    // 				}
    // 			}
    // 		}
    // 	}
}

//彻底禁止WordPress缩略图
add_filter('add_image_size', function () {
    return 1;
});

//禁用自动生成的图片尺寸
function shapeSpace_disable_image_sizes($sizes)
{
    unset($sizes['thumbnail']);    // disable thumbnail size
    unset($sizes['medium']);       // disable medium size
    unset($sizes['large']);        // disable large size
    unset($sizes['medium_large']); // disable medium-large size
    unset($sizes['1536x1536']);    // disable 2x medium-large size
    unset($sizes['2048x2048']);    // disable 2x large size    
    return $sizes;
}
add_action('intermediate_image_sizes_advanced', 'shapeSpace_disable_image_sizes');
// 禁用缩放尺寸
add_filter('big_image_size_threshold', '__return_false');
// 禁用其他图片尺寸
function shapeSpace_disable_other_image_sizes()
{
    remove_image_size('post-thumbnail'); // disable images added via set_post_thumbnail_size() 
    remove_image_size('another-size');   // disable any other added image sizes
}
add_action('init', 'shapeSpace_disable_other_image_sizes');

/**
 * WordPress 百度收录检测和推送功能
 * 可以添加到主题的 functions.php 文件中，或者制作成插件
 */
class WordPress_Baidu_SEO {
    
    /**
     * 检查百度是否收录指定URL
     * 
     * @param string $url 要检查的URL
     * @return array 返回检查结果
     */
    public static function baidu_record($url) {
        $index = false;
        $url = preg_replace('/^https?:\/\//', '', $url);
        
        // 构建百度搜索URL
        $search_url = 'http://www.baidu.com/s?' . http_build_query([
            'wd' => $url,
            'rn' => 1,
            'tn' => 'json',
            'ie' => 'utf-8',
            'cl' => 3,
            'f' => 9
        ]);
        
        // 获取WordPress选项中的Cookie和User-Agent
        $cookie = _mrhe('baidu_record_cookie');
        $user_agent = _mrhe('baidu_record_user_agent');
        
        // 使用wp_remote_get替代curl
        $args = array(
            'timeout' => 30,
            'headers' => array(
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language' => 'zh-CN,zh;q=0.9',
                'Cache-Control' => 'max-age=0',
                'Connection' => 'keep-alive',
                'Cookie' => $cookie,
                'Host' => 'www.baidu.com',
                'User-Agent' => $user_agent,
            )
        );
        
        $response = wp_remote_get($search_url, $args);
        
        if (is_wp_error($response)) {
            return ['index' => null, 'response' => $response->get_error_message()];
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (is_array($data)) {
            if (!empty($data['feed']['entry'][0]['url'])) {
                $baidu_url = preg_replace('/^https?:\/\//', '', $data['feed']['entry'][0]['url']);
                if ($baidu_url == $url) {
                    $index = true;
                }
            }
        } else {
            $index = null;
        }
        
        return ['index' => $index, 'response' => $data];
    }
    
    /**
     * 检查文章是否被百度收录
     * 
     * @param int $post_id 文章ID
     * @return array 返回检查结果
     */
    public static function check_post_baidu_record($post_id) {
        // 验证文章ID
        if (!is_numeric($post_id) || $post_id <= 0) {
            return ['code' => 0, 'data' => '非法请求！已屏蔽！'];
        }
        
        $post_url = get_permalink($post_id);
        if (!$post_url) {
            return ['code' => 0, 'data' => '文章不存在！'];
        }
        
        $baidu_record = self::baidu_record($post_url);
        
        if (is_bool($baidu_record['index'])) {
            if ($baidu_record['index']) {
                return ['data' => "已收录", 'response' => $baidu_record['response']];
            } else {
                return ['data' => '未收录', 'response' => $baidu_record['response']];
            }
        } else {
            return ['data' => "检测失败", 'index' => $baidu_record['index'], 'response' => $baidu_record['response']];
        }
    }

    /**
     * 主动推送URL到百度
     * 
     * @param int $post_id 文章ID
     * @return array 推送结果
     */
    public static function baidu_push($post_id) {
        // 验证文章ID
        if (!is_numeric($post_id) || $post_id <= 0) {
            return ['code' => 0, 'data' => '非法请求！已屏蔽！'];
        }
        
        // // 检查是否已经推送过
        // $baidu_push = get_post_meta($post_id, 'baidu_push', true);
        // if ($baidu_push) {
        //     return ['already' => true, 'data' => '该文章已推送过'];
        // }
        
        $token = _pz('xzh_post_token');
        if (empty($token)) {
            return ['code' => 0, 'data' => '百度推送Token未配置'];
        }
        
        $domain = home_url();
        $domain = preg_replace('/^https?:\/\//', '', $domain);
        $url = get_permalink($post_id);
        
        if (!$url) {
            return ['code' => 0, 'data' => '文章不存在'];
        }
        
        // 构建API URL
        $api = "http://data.zz.baidu.com/urls?site={$domain}&token={$token}";
        
        // 使用wp_remote_post替代curl
        $args = array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'text/plain'
            ),
            'body' => $url
        );
        
        $response = wp_remote_post($api, $args);
        
        if (is_wp_error($response)) {
            return ['code' => 0, 'data' => '推送失败：' . $response->get_error_message()];
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        // // 如果推送成功，记录到数据库
        // if (empty($result['error'])) {
        //     update_post_meta($post_id, 'baidu_push', '1');
        //     update_post_meta($post_id, 'baidu_push_time', current_time('mysql'));
        // }
        
        // 处理错误信息的中文化
        if (!empty($result['message'])) {
            $messages = [
                'site error' => '站点未在站长平台验证',
                'empty content' => 'post内容为空',
                'only 2000 urls are allowed once' => '每次最多只能提交2000条链接',
                'over quota' => '已超过每日配额',
                'token is not valid' => 'token错误',
                'not found' => '接口地址填写错误',
                'internal error, please try later' => '服务器偶然异常，通常重试就会成功'
            ];
            
            foreach ($messages as $key => $value) {
                if ($result['message'] == $key) {
                    $result['message'] = $value;
                }
            }
        }
        
        return ['domain' => $domain, 'url' => $url, 'data' => $result];
    }
}



/**
 * AJAX处理函数 - 检查百度收录
 */
function baidu_record_callback() {
    $post_id = intval($_POST['push']);
    
    if (!$post_id) {
        zib_send_json_error('无效的文章ID');
        return;
    }
    
    $result = WordPress_Baidu_SEO::check_post_baidu_record($post_id);
    
    if (isset($result['code']) && $result['code'] === 0) {
        zib_send_json_error($result['data']);
    } else {
        zib_send_json_success($result);
    }
}
add_action('wp_ajax_mrhe_baidu_record', 'baidu_record_callback');
add_action('wp_ajax_nopriv_mrhe_baidu_record', 'baidu_record_callback');

/**
 * AJAX处理函数 - 推送到百度
 */
function _pushRecord_callback() {
    // 检查配置（与注册条件保持一致）
    if ((!_pz('xzh_post_on') && !_pz('xzh_post_daily_push')) || !_pz('xzh_post_token')) {
        zib_send_json_error('百度推送未配置或未启用');
        return;
    }

    $post_id = intval($_POST['push']);
    
    if (!$post_id) {
        zib_send_json_error('无效的文章ID');
        return;
    }
    
    $result = WordPress_Baidu_SEO::baidu_push($post_id);
    
    if (isset($result['code']) && $result['code'] === 0) {
        zib_send_json_error($result['data']);
    } else {
        zib_send_json_success($result);
    }
}
// 始终注册 AJAX handler，在函数内部检查配置
add_action('wp_ajax_mrhe_baidu_push', '_pushRecord_callback');
add_action('wp_ajax_nopriv_mrhe_baidu_push', '_pushRecord_callback');

// 静态缓存使用Ajax处理文章浏览量
function increase_post_views() {
    // 检查是否有post_id传递过来，并且确保它是一个数字
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if ($post_id > 0) {
        $count_key = 'views';
        $count = get_post_meta($post_id, $count_key, true);
        if ($count == '') {
            $count = 1;
            add_post_meta($post_id, $count_key, $count);
        } else {
            $count++;
            update_post_meta($post_id, $count_key, $count);
        }
    }
    // 如果是Ajax请求，我们需要停止执行并返回结果
    if (defined('DOING_AJAX') && DOING_AJAX) { 
        echo $count;
        wp_die(); // 这将停止执行并返回结果给Ajax调用
    }
}

// 注册一个空的脚本，用于将数据传递到JavaScript
function enqueue_and_localize_scripts() {
    wp_register_script('my_custom_script', '');
    wp_enqueue_script('my_custom_script');

    // 使用wp_localize_script来传递Ajax URL和post ID
    wp_localize_script('my_custom_script', 'myScriptData', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'postID' => get_the_ID()
    ));
}

// 检查用户是否未登录并且没有评论email cookie
if (!is_user_logged_in() && !isset($_COOKIE['comment_author_email_' . COOKIEHASH]) && _mrhe('ajax_number_s')) {
    add_action('wp_ajax_nopriv_increase_views', 'increase_post_views');
    add_action('wp_enqueue_scripts', 'enqueue_and_localize_scripts');
    add_action('wp_footer', 'add_footer_scripts');
}

// 将JavaScript代码添加到页面底部
function add_footer_scripts() {
    if (is_single()) {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            if(_mrhe.BAIDU_PUSH){
            var postId = myScriptData.postID;
            $.ajax({
                type: "POST",
                url: myScriptData.ajaxurl,
                data: {
                    action: "increase_views",
                    post_id: postId
                },
                success: function(response) {
                    console.log('AJAX将浏览量+: ' + response);
                    $('div.post-metas > item.meta-view, div.meta-right > item.item-view').html(function() {
                      var svgElement = $(this).find('svg.icon');
                      var formattedNumber;
                      if (response >= 10000) {
                        formattedNumber = (response / 10000).toFixed(1) + 'W+';
                      } else {
                        formattedNumber = response.toString();
                      }
                      return svgElement.prop('outerHTML') + formattedNumber;
                    });
                }
            });
            }
        });
        </script>
        <?php
    }
}

//何先生页面加载参数
add_action('wp_footer', 'mrhe_win_var');
function mrhe_win_var()
{
    // 初始化全局变量
    $global_vars = array(
        'BAIDU_PUSH' => ((_pz('xzh_post_on') || _pz('xzh_post_daily_push')) && _pz('xzh_post_token') && _mrhe('baidu_record')) ? 'true' : 'false',
        'CACHE_VIEWS' => _mrhe('ajax_number_s') ? 'true' : 'false',
        'OFFICIALSITE' => _mrhe('official_domains_s')
    );

    // 添加翻译功能相关的全局变量
    if (_mrhe('translation_s')) {
        $translation_options = _mrhe('translation_options');
        $language_config = get_language_config();
        $default_language_key = $translation_options['translation_default_lang'];
        $default_language_code = isset($language_config[$default_language_key]) ? $language_config[$default_language_key]['code'] : 'chinese_simplified';
        
        // 处理忽略设置
        $ignore_settings = array();
        
        // 处理忽略的HTML标签
        if (!empty($translation_options['translation_ignore_tags'])) {
            $tags = array_map('trim', explode(',', $translation_options['translation_ignore_tags']));
            $ignore_settings['tags'] = array_filter($tags);
        }
        
        // 处理忽略的CSS类名
        if (!empty($translation_options['translation_ignore_classes'])) {
            $classes = array_map('trim', explode(',', $translation_options['translation_ignore_classes']));
            $ignore_settings['classes'] = array_filter($classes);
        }
        
        // 处理忽略的元素ID
        if (!empty($translation_options['translation_ignore_ids'])) {
            $ids = array_map('trim', explode(',', $translation_options['translation_ignore_ids']));
            $ignore_settings['ids'] = array_filter($ids);
        }
        
        // 处理忽略的文本内容
        if (!empty($translation_options['translation_ignore_texts'])) {
            $texts = array_filter(array_map('trim', explode("\n", $translation_options['translation_ignore_texts'])));
            $ignore_settings['texts'] = $texts;
        }
        
        // 处理正则表达式忽略
        if (!empty($translation_options['translation_ignore_regexs'])) {
            $regexs = array_filter(array_map('trim', explode("\n", $translation_options['translation_ignore_regexs'])));
            $ignore_settings['regexs'] = $regexs;
        }
        
        $global_vars['TRANSLATION'] = array(
            'enabled' => true,
            'service' => $translation_options['translation_service'],
            'default_language' => $default_language_code,
            'auto_detect' => $translation_options['translation_auto_detect'],
            'translate_js' => $translation_options['translate_js'],
            'cdn_url' => 'https://cdn.jsdelivr.net/gh/xnx3/translate/translate.js/translate.min.js',
            'ignore' => $ignore_settings
        );
    } else {
        $global_vars['TRANSLATION'] = array(
            'enabled' => false
        );
    }

    // 只有在启用自定义IP API时才添加IP_APIS配置
    if (_mrhe('client_ip_api_s')) {
        $ip_apis = array();
        $apis = _mrhe('client_ip_apis');
        if (!empty($apis) && is_array($apis)) {
            foreach ($apis as $api) {
                if (!empty($api['api_status']) && !empty($api['api_url'])) {
                    $api_config = array(
                        'url' => $api['api_url']
                    );
                    
                    // 处理JSONP格式
                    if (!empty($api['is_jsonp'])) {
                        $api_config['isJsonp'] = true;
                        $api_config['jsonpCallback'] = !empty($api['jsonp_callback']) ? $api['jsonp_callback'] : 'IPCallBack';
                    }
                    
                    // 处理IP获取路径
                    $api_config['getIP'] = 'data => ' . (!empty($api['ip_key']) ? 'zib_get_array_value_by_path(data, "' . $api['ip_key'] . '")' : 'data.ip');
                    
                    $ip_apis[] = $api_config;
                }
            }
        }
        if (!empty($ip_apis)) {
            $global_vars['IP_APIS'] = $ip_apis;
        }
    }
?>
    <script type="text/javascript">
        window._mrhe = <?php echo json_encode($global_vars); ?>;
    </script>
<?php
}

// //私密评论--何先生逻辑优化版
// function mrhe_private_message_hook($comment_content, $comment)
// {
//     $comment_ID = $comment->comment_ID;
//     $parent_ID = $comment->comment_parent ? $comment->comment_parent : '';
//     $parent_email = get_comment_author_email($parent_ID);
//     $is_private = get_comment_meta($comment_ID, '_private', true);
//     $email = $comment->comment_author_email;
//     $current_commenter = wp_get_current_commenter();
//     $current_user = wp_get_current_user();
//     $html = '<span style="color:#558E53"><i class="fa fa-lock fa-fw"></i>该评论为私密评论</span>';
//     if ($is_private) {
//         if (!is_user_logged_in() && $current_commenter['comment_author_email'] == '') {
//             return $comment_content = $html;
//         } else
// 		if ($current_commenter['comment_author_email'] == '' && $current_user->user_email == $parent_email || current_user_can('delete_user') || $current_user->user_email == $email || $current_commenter['comment_author_email'] == $email || $parent_email == $current_commenter['comment_author_email'] && $current_commenter['comment_author_email'] !== '') {
//             return $comment_content = '#私密# ' . $comment_content;
//         }
//         return $comment_content = $html;
//     }
//     return $comment_content;
// }
// add_filter('get_comment_text', 'mrhe_private_message_hook', 10, 2);

//私密评论--何先生逻辑优化版
// function mrhe_private_message_hook($comment_content, $comment)
// {
//     if (!$comment || !is_object($comment)) {
//         return $comment_content;
//     }

//     $comment_ID = $comment->comment_ID;
//     $parent_ID = $comment->comment_parent ? $comment->comment_parent : '';
//     //$parent_email = get_comment_author_email($parent_ID);
    
//     // 安全获取父评论邮箱
//     $parent_email = '';
//     if ($parent_ID) {
//         $parent_comment = get_comment($parent_ID);
//         if ($parent_comment && isset($parent_comment->comment_author_email)) {
//             $parent_email = $parent_comment->comment_author_email;
//         }
//     }

//     $is_private = get_comment_meta($comment_ID, '_private', true);
    
//     // 确保评论邮箱存在
//     if (!isset($comment->comment_author_email)) {
//         return $comment_content;
//     }
//     $email = $comment->comment_author_email;
    
//     $current_commenter = wp_get_current_commenter();
//     $current_user = wp_get_current_user();
//     $html = '<span style="color:#558E53"><i class="fa fa-lock fa-fw"></i>该评论为私密评论</span>';

//     if ($is_private) {
//         // 未登录且没有评论者邮箱信息时显示私密提示
//         if (!is_user_logged_in() && empty($current_commenter['comment_author_email'])) {
//             return $html;
//         }

//         // 获取评论所在文章的作者ID
//         $post = get_post($comment->comment_post_ID);
//         $post_author_id = $post ? $post->post_author : 0;

//         // 以下情况可以查看私密评论内容:
//         // 1. 管理员
//         // 2. 评论作者本人
//         // 3. 父评论作者
//         // 4. 当前评论者与评论或父评论邮箱匹配
//         // 5. 文章作者
//         if (current_user_can('delete_user') || 
//             $current_user->user_email === $email ||
//             $current_user->user_email === $parent_email ||
//             $current_commenter['comment_author_email'] === $email ||
//             ($parent_email && $current_commenter['comment_author_email'] === $parent_email) ||
//             ($post_author_id && $post_author_id === $current_user->ID)
//         ) {
//             return '#私密# ' . $comment_content;
//         }

//         return $html;
//     }

//     return $comment_content;
// }
// add_filter('get_comment_text', 'mrhe_private_message_hook', 10, 2);

// function mrhe_mark_private_message($comment_id)
// {
//     if ($_POST['is-private']) {
//         update_comment_meta($comment_id, '_private', 'true');
//     }
// }
// add_action('comment_post', 'mrhe_mark_private_message');
// //将某条评论设为私密评论
// add_action('wp_ajax_nopriv_mrhe_private', 'mrhe_private');
// add_action('wp_ajax_mrhe_private', 'mrhe_private');
// function mrhe_private()
// {
//     $comment_id = $_POST["p_id"];
//     $action = $_POST["p_action"];
//     if ($action == 'set_private') {
//         update_comment_meta($comment_id, '_private', 'true');
//     }
//     if ($action == 'del_private') {
//         delete_comment_meta($comment_id, '_private', 'true');
//     }
//     echo 'ok';
//     die;
// }
// //挂载到评论底部
// function mrhe_footer_info_add_private($info, $comment)
// {
//     if (current_user_can('manage_options')) {
//         $comment_ID = $comment->comment_ID;
//         $i_private = get_comment_meta($comment_ID, '_private', true);
//         $flag = '';
//         if (empty($i_private)) {
//             $flag .= ' - <a href="javascript:;" data-actionp="set_private" data-idp="' . get_comment_id() . '" id="sp" class="sm">(<span class="has_set_private">设为私密</span>)</a>';
//             $info = $info . $flag;
//         } else {
//             $flag .= ' - <a href="javascript:;" data-actionp="del_private" data-idp="' . get_comment_id() . '" id="sp" class="sm">(<span class="has_set_private">删除私密</span>)</a>';
//             $info = $info . $flag;
//         }
//     }
//     return $info;
// }
// add_filter('comment_footer_info', 'mrhe_footer_info_add_private', 99, 2);

// ===== 私密评论显示逻辑（融合版） =====
function fusion_private_message_hook($comment_content, $comment) {
    if (!$comment || !is_object($comment)) {
        return $comment_content;
    }

    $comment_ID   = $comment->comment_ID;
    $is_private   = get_comment_meta($comment_ID, '_private', true);
    $email        = isset($comment->comment_author_email) ? $comment->comment_author_email : '';
    $parent_email = '';

    // 获取父评论邮箱
    if ($comment->comment_parent) {
        $parent_comment = get_comment($comment->comment_parent);
        if ($parent_comment && isset($parent_comment->comment_author_email)) {
            $parent_email = $parent_comment->comment_author_email;
        }
    }

    // 当前用户 & 当前访客
    $current_user      = wp_get_current_user();
    $current_commenter = wp_get_current_commenter(); // cookie 里的邮箱（未登录也可能有）

    $html = '<div class="hidden-box" reply-show="true" reload-hash="#hidden-box-comment">'
          . '<a class="hidden-text"><i class="fa fa-lock fa-fw"></i>此评论为私密，仅评论双方和文章作者可见.</a>'
          . '</div>';

    if ($is_private) {
        $is_admin          = current_user_can('manage_options');
        $is_comment_author = ($current_user && $current_user->ID && $current_user->ID == $comment->user_id);
        $is_post_author    = ($current_user && $current_user->ID && $current_user->ID == get_post_field('post_author', $comment->comment_post_ID));

        // 访客邮箱匹配（支持未登录用户查看自己的评论）
        $is_guest_match = false;
        if (!is_user_logged_in() && !empty($current_commenter['comment_author_email'])) {
            $is_guest_match = (
                $current_commenter['comment_author_email'] === $email ||
                ($parent_email && $current_commenter['comment_author_email'] === $parent_email)
            );
        }

        // 仅允许：管理员 / 评论作者 / 文章作者 / 父评论作者 / 邮箱匹配的访客
        if ($is_admin || $is_comment_author || $is_post_author ||
            ($current_user && $current_user->user_email && ($current_user->user_email === $email || $current_user->user_email === $parent_email)) ||
            $is_guest_match
        ) {
            return '<div class="hidden-box show" id="hidden-box-comment">'
                 . '<div class="hidden-text">#私密# 以下内容仅对你可见</div>'
                 . $comment_content
                 . '</div>';
        }

        return $html;
    }

    return $comment_content;
}
add_filter('get_comment_text', 'fusion_private_message_hook', 10, 2);


// ===== 评论操作：添加“设为私密/取消私密”按钮 =====
function fusion_comments_action_add_private($lists, $comment) {
    $current_user = wp_get_current_user();
    $user_id      = get_current_user_id();

    // 仅允许管理员或评论作者看到操作按钮
    if (is_user_logged_in() && (is_super_admin($user_id) || $current_user->ID == $comment->user_id)) {
        $comment_ID   = $comment->comment_ID;
        $is_private   = get_comment_meta($comment_ID, '_private', true);
        $private_text = empty($is_private) ? '设为私密' : '取消私密';
        $action       = empty($is_private) ? 'set_private' : 'del_private';

        $private_but  = '<a class="comment-private-link wp-ajax-submit" '
                      . 'form-action="' . esc_attr($action) . '" '
                      . 'form-data="' . esc_attr(json_encode(['id' => $comment_ID])) . '" '
                      . 'href="javascript:;"><i class="fa fa-user-secret mr10" aria-hidden="true"></i>'
                      . $private_text . '</a>';

        $lists = '<li>' . $private_but . '</li>' . $lists;
    }

    return $lists;
}
add_filter('comments_action_lists', 'fusion_comments_action_add_private', 99, 2);

function mrhe_mark_private_message($comment_id)
{
    if ($_POST['is-private']) {
        update_comment_meta($comment_id, '_private', 'true');
    }
}
add_action('comment_post', 'mrhe_mark_private_message');

// ===== 处理更新评论私密状态的 AJAX 请求 =====
function fusion_private_comment_action() {
    $response = ['reload' => true];

    if (empty($_POST['id'])) {
        zib_send_json_error(['msg' => '无效的评论ID'] + $response);
    }

    $comment_id   = intval($_POST['id']);
    $action       = sanitize_text_field($_POST['action']);
    $comment      = get_comment($comment_id);
    $current_user = wp_get_current_user();

    if (!$comment || !$current_user || !($current_user->ID == $comment->user_id || current_user_can('manage_options'))) {
        zib_send_json_error(['msg' => '权限不足或无效的评论ID'] + $response);
    }

    if ($action === 'set_private') {
        update_comment_meta($comment_id, '_private', 'true');
        zib_send_json_success(['msg' => '评论已设为私密'] + $response);
    } elseif ($action === 'del_private') {
        delete_comment_meta($comment_id, '_private');
        zib_send_json_success(['msg' => '评论已公开'] + $response);
    } else {
        zib_send_json_error(['msg' => '无效的操作类型'] + $response);
    }
}
add_action('wp_ajax_set_private', 'fusion_private_comment_action');
add_action('wp_ajax_del_private', 'fusion_private_comment_action');

function add_preconnect_link() {
    // 预连接到所需的域
    echo '<link rel="preconnect" href="https://images.hexsen.com">';
    echo '<link rel="preconnect" href="https://cravatar.cn">';
}
add_action('wp_head', 'add_preconnect_link', 1);

//添加og属性增强seo
add_action('wp_head', 'sk_og_meta', 1);
function sk_og_meta()
{
    $wp_type = 'website';
    $og_image = '';

    if (is_home()) {
        $og_image = _pz('logo_src');
    } elseif (is_singular()) {
        $wp_type = 'article';
        $og_image = zib_post_thumbnail('', '', $show_url = true);
    }

    $title = zib_title($echo = false);
    $url = zib_get_current_url();
    $post = get_post();
    if (empty($post->ID)) {
        return;
    }
    $author_id    = isset($post->post_author) ? $post->post_author : 0;
    $user = get_userdata($author_id);
    $display_name = $user->display_name ?: get_the_author();

    $meta_tags = '
    <meta property="og:locale" content="zh_CN" />
    <meta property="og:type" content="' . $wp_type . '" />
    <meta property="og:title" content="' . $title . '" />
    ';

    if (is_home() || is_singular()) {
        $meta_tags .= '
        <meta property="og:description" content="' . zib_description($echo = false) . '" />
        ';
    }

    $meta_tags .= '
    <meta property="og:keywords" content="' . zib_keywords($echo = false) . '" />
    <meta property="og:url" content="' . $url . '" />
    <meta property="og:site_name" content="' . get_bloginfo('name') . '" />
    ';

    if (!empty($og_image)) {
        $meta_tags .= '
        <meta property="og:image" content="' . $og_image . '" />
        ';
    }

    if (is_singular()) {
        if (is_single()) {
            $meta_tags .= '
            <meta property="og:author" content="' . $display_name . '" />
            ';
        }
        $meta_tags .= '
        <meta property="article:published_time" content="' . get_the_time('c') . '" />
        <meta property="article:modified_time" content="' . get_the_modified_time('c') . '" />
        ';
    }

    echo $meta_tags;
}

/* 屏蔽纯英文评论和纯日文 */
function refused_english_comments($incoming_comment) {
    $pattern = '/[一-龥]/u';
    // 禁止全英文评论
     if(!preg_match($pattern, $incoming_comment['comment_content'])) {
     echo (json_encode(array('error' => 1, 'msg' => '您的评论中必须包含汉字！')));
            exit();
    }
    $pattern = '/[あ-んア-ン]/u';
    // 禁止日文评论
     if(preg_match($pattern, $incoming_comment['comment_content'])) {
     echo (json_encode(array('error' => 1, 'msg' => '评论禁止包含日文！')));
            exit();
     }
    return( $incoming_comment );
    }
add_filter('preprocess_comment', 'refused_english_comments');

// // 禁用REST API
// function disable_rest_api_for_non_admin_and_unallowed_plugins()
// {
//     if (!is_user_logged_in()) {
//         return new WP_Error('rest_disabled', '对不起，无权限访问REST API。', array('status' => 401));
//     }
// }
// add_filter('rest_authentication_errors', 'disable_rest_api_for_non_admin_and_unallowed_plugins');


/**
 * 文件存储系统设置
 */
function custom_storage_settings() {
    define('STORAGE_API_URL', _mrhe('storage_api_function')['storage_api_url']);

    if(current_user_can('administrator')){
        define('STORAGE_API_TOKEN', _mrhe('storage_api_function')['storage_api_token']);
    }else{
        define('STORAGE_API_TOKEN', _mrhe('storage_api_function')['storage_api_token_user']);
    }
}

_mrhe('open_storage_api') ? add_action('init', 'custom_storage_settings') : false;

/**
 * 修改文件URL
 */
function custom_attachment_url($url, $attachment_id) {
    $meta = wp_get_attachment_metadata($attachment_id);
    if (isset($meta['file']) && strpos($meta['file'], '/uploads/') === 0) {
        return STORAGE_API_URL . $meta['file'];
    }
    return $url;
}
_mrhe('open_storage_api') ? add_filter('wp_get_attachment_url', 'custom_attachment_url', 10, 2) : false;

/**
 * 修改上传目录设置
 */
function custom_upload_dir($uploads) {
    $uploads['url'] = STORAGE_API_URL . '/uploads';
    $uploads['baseurl'] = STORAGE_API_URL . '/uploads';
    return $uploads;
}
_mrhe('open_storage_api') ? add_filter('upload_dir', 'custom_upload_dir') : false;

/**
 * CURL请求封装
 */
function custom_curl($url, $params = array()) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    if (@$params['Header']) {
        $Header = $params['Header'];
    } else {
        $Header = ["Accept: application/json"];
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $Header);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if (@$params['post']) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params['post']);
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

/**
 * 上传文件到存储系统
 */
function upload_to_storage($file_path) {
    // 获取文件的 MIME 类型
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);
    
    $url = STORAGE_API_URL . '/api/upload.php';

    // // 获取原始文件名
    // $path_parts = pathinfo($file_path);
    // $original_name = $path_parts['basename'];
    
    $data = [
        'Header' => [
            "Accept: application/json",
            "Authorization: Bearer " . STORAGE_API_TOKEN
        ],
        'post' => [
            'file' => new CURLFile($file_path, $mime_type, basename($file_path))
            // 'file' => new CURLFile($file_path, $mime_type, $original_name)
        ]
    ];
    
    $response = custom_curl($url, $data);
    return json_decode($response, true);
}

/**
 * 处理文件上传
 */
function handle_upload_to_storage($metadata, $attachment_id) {
    $file_path = get_attached_file($attachment_id);
    
    // 保存文件大小到 metadata（WordPress 标准方式）
    if (file_exists($file_path)) {
        $file_size = filesize($file_path);
        $metadata['filesize'] = $file_size;
    }
    
    // 获取图片信息
    if (strpos(wp_check_filetype($file_path)['type'], 'image/') === 0) {
        if ($image_size = getimagesize($file_path)) {
            $metadata['width'] = $image_size[0];
            $metadata['height'] = $image_size[1];
        }
    }

    // 上传主文件
    $response = upload_to_storage($file_path);
    if (!$response['success']) {
        return $metadata;
    }
    
    // 保存文件信息
    add_post_meta($attachment_id, 'remote_file_name', $response['filename'], true);
    add_post_meta($attachment_id, 'remote_delete_key', $response['delete_key'], true);
    $metadata['file'] = $response['download_url'];
    update_post_meta($attachment_id, '_wp_attached_file', $response['download_url']);
    
    // 处理缩略图
    if (isset($metadata['sizes'])) {
        $base_dir = dirname($file_path);
        foreach ($metadata['sizes'] as $size => $size_info) {
            $size_file_path = $base_dir . '/' . $size_info['file'];
            if (file_exists($size_file_path)) {
                $size_response = upload_to_storage($size_file_path);
                if ($size_response['success']) {
                    add_post_meta($attachment_id, 'remote_file_name_' . $size, $size_response['filename'], true);
                    add_post_meta($attachment_id, 'remote_delete_key_' . $size, $size_response['delete_key'], true);
                    $metadata['sizes'][$size]['file'] = $size_response['filename'];
                    wp_delete_file($size_file_path);
                }
            }
        }
    }
    
    wp_delete_file($file_path);

    return $metadata;
}
_mrhe('open_storage_api') ? add_filter('wp_generate_attachment_metadata', 'handle_upload_to_storage', 10, 2) : false;

/**
 * 删除远程文件
 */
function delete_remote_file($attachment_id) {
    // 删除主文件
    $filename = get_post_meta($attachment_id, 'remote_file_name', true);
    $delete_key = get_post_meta($attachment_id, 'remote_delete_key', true);
    
    if ($filename && $delete_key) {
        $data = [
            'Header' => [
                "Accept: application/json",
                "Authorization: Bearer " . STORAGE_API_TOKEN
            ],
            'post' => [
                'action' => 'delete',
                'filename' => $filename,
                'delete_key' => $delete_key
            ]
        ];
        custom_curl(STORAGE_API_URL . '/api/file_manage.php', $data);
    }
    
    // 删除缩略图
    $meta = wp_get_attachment_metadata($attachment_id);
    if (isset($meta['sizes'])) {
        foreach ($meta['sizes'] as $size => $size_info) {
            $size_filename = get_post_meta($attachment_id, 'remote_file_name_' . $size, true);
            $size_delete_key = get_post_meta($attachment_id, 'remote_delete_key_' . $size, true);
            
            if ($size_filename && $size_delete_key) {
                $data = [
                    'Header' => [
                        "Accept: application/json",
                        "Authorization: Bearer " . STORAGE_API_TOKEN
                    ],
                    'post' => [
                        'action' => 'delete',
                        'filename' => $size_filename,
                        'delete_key' => $size_delete_key
                    ]
                ];
                custom_curl(STORAGE_API_URL . '/api/file_manage.php', $data);
            }
        }
    }
}
_mrhe('open_storage_api') ? add_action('delete_attachment', 'delete_remote_file') : false;



// //测试分片上传
// /**
//  * 文件存储系统设置
//  */
// function custom_storage_settings() {
//     define('STORAGE_API_URL', _mrhe('storage_api_function')['storage_api_url']);

//     if(current_user_can('administrator')){
//         define('STORAGE_API_TOKEN', _mrhe('storage_api_function')['storage_api_token']);
//     }else{
//         define('STORAGE_API_TOKEN', _mrhe('storage_api_function')['storage_api_token_user']);
//     }
// }

// _mrhe('open_storage_api') ? add_action('init', 'custom_storage_settings') : false;

// /**
//  * 修改文件URL
//  */
// function custom_attachment_url($url, $attachment_id) {
//     $meta = wp_get_attachment_metadata($attachment_id);
//     if (isset($meta['file']) && strpos($meta['file'], '/uploads/') === 0) {
//         return STORAGE_API_URL . $meta['file'];
//     }
//     return $url;
// }
// _mrhe('open_storage_api') ? add_filter('wp_get_attachment_url', 'custom_attachment_url', 10, 2) : false;

// /**
//  * 修改上传目录设置
//  */
// function custom_upload_dir($uploads) {
//     $uploads['url'] = STORAGE_API_URL . '/uploads';
//     $uploads['baseurl'] = STORAGE_API_URL . '/uploads';
//     return $uploads;
// }
// _mrhe('open_storage_api') ? add_filter('upload_dir', 'custom_upload_dir') : false;

// /**
//  * CURL请求封装
//  */
// function custom_curl($url, $params = array()) {
//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
//     if (@$params['Header']) {
//         $Header = $params['Header'];
//     } else {
//         $Header = ["Accept: application/json"];
//     }
    
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $Header);
//     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
//     curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
//     if (@$params['post']) {
//         curl_setopt($ch, CURLOPT_POST, 1);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, $params['post']);
//     }
    
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     $response = curl_exec($ch);
//     curl_close($ch);
//     return $response;
// }

// /**
//  * 上传文件到存储系统
//  */
// function upload_to_storage($file_path) {
//     // 获取文件的 MIME 类型
//     $finfo = finfo_open(FILEINFO_MIME_TYPE);
//     $mime_type = finfo_file($finfo, $file_path);
//     finfo_close($finfo);
    
//     $file_size = filesize($file_path);
//     $chunk_size = 2 * 1024 * 1024; // 2MB per chunk
    
//     // 如果文件小于分片大小，直接上传
//     if ($file_size <= $chunk_size) {
//         return upload_single_file($file_path, $mime_type);
//     }
    
//     // 分片上传
//     $file_id = uniqid() . '_' . time();
//     $total_chunks = ceil($file_size / $chunk_size);
//     $fp = fopen($file_path, 'rb');
    
//     for ($chunk_number = 0; $chunk_number < $total_chunks; $chunk_number++) {
//         // 创建临时分片文件
//         $chunk_path = sys_get_temp_dir() . '/' . $file_id . '_chunk_' . $chunk_number;
//         $chunk_handle = fopen($chunk_path, 'wb');
        
//         // 读取分片数据
//         $chunk_data = fread($fp, $chunk_size);
//         fwrite($chunk_handle, $chunk_data);
//         fclose($chunk_handle);
        
//         // 上传分片
//         $response = upload_chunk($chunk_path, [
//             'fileId' => $file_id,
//             'fileName' => basename($file_path),
//             'chunkNumber' => $chunk_number,
//             'totalChunks' => $total_chunks
//         ]);
        
//         // 删除临时分片文件
//         unlink($chunk_path);
        
//         if (!$response['success']) {
//             fclose($fp);
//             return $response;
//         }
        
//         // 如果是最后一个分片且上传完成
//         if (isset($response['isComplete']) && $response['isComplete']) {
//             fclose($fp);
//             return $response;
//         }
//     }
    
//     fclose($fp);
//     return ['success' => false, 'error' => '上传失败'];
// }

// /**
//  * 上传单个完整文件
//  */
// function upload_single_file($file_path, $mime_type) {
//     $url = STORAGE_API_URL . '/api/upload.php';
    
//     $data = [
//         'Header' => [
//             "Accept: application/json",
//             "Authorization: Bearer " . STORAGE_API_TOKEN
//         ],
//         'post' => [
//             'file' => new CURLFile($file_path, $mime_type, basename($file_path))
//         ]
//     ];
    
//     $response = custom_curl($url, $data);
//     return json_decode($response, true);
// }

// /**
//  * 上传单个分片
//  */
// function upload_chunk($chunk_path, $params) {
//     $url = STORAGE_API_URL . '/api/chunk_upload.php';
    
//     $data = [
//         'Header' => [
//             "Accept: application/json",
//             "Authorization: Bearer " . STORAGE_API_TOKEN
//         ],
//         'post' => [
//             'chunk' => new CURLFile($chunk_path, 'application/octet-stream', 'chunk'),
//             'fileId' => $params['fileId'],
//             'fileName' => $params['fileName'],
//             'chunkNumber' => $params['chunkNumber'],
//             'totalChunks' => $params['totalChunks']
//         ]
//     ];
    
//     $response = custom_curl($url, $data);
//     return json_decode($response, true);
// }

// /**
//  * 处理文件上传
//  */
// function handle_upload_to_storage($metadata, $attachment_id) {
//     $file_path = get_attached_file($attachment_id);
    
//     // 获取图片信息
//     if (strpos(wp_check_filetype($file_path)['type'], 'image/') === 0) {
//         if ($image_size = getimagesize($file_path)) {
//             $metadata['width'] = $image_size[0];
//             $metadata['height'] = $image_size[1];
//         }
//     }

//     // 上传主文件
//     $response = upload_to_storage($file_path);
//     if (!$response['success']) {
//         return $metadata;
//     }
    
//     // 保存文件信息
//     add_post_meta($attachment_id, 'remote_file_name', $response['filename'], true);
//     add_post_meta($attachment_id, 'remote_delete_key', $response['delete_key'], true);
//     $metadata['file'] = $response['download_url'];
//     update_post_meta($attachment_id, '_wp_attached_file', $response['download_url']);
    
//     // 处理缩略图
//     if (isset($metadata['sizes'])) {
//         $base_dir = dirname($file_path);
//         foreach ($metadata['sizes'] as $size => $size_info) {
//             $size_file_path = $base_dir . '/' . $size_info['file'];
//             if (file_exists($size_file_path)) {
//                 $size_response = upload_to_storage($size_file_path);
//                 if ($size_response['success']) {
//                     add_post_meta($attachment_id, 'remote_file_name_' . $size, $size_response['filename'], true);
//                     add_post_meta($attachment_id, 'remote_delete_key_' . $size, $size_response['delete_key'], true);
//                     $metadata['sizes'][$size]['file'] = $size_response['filename'];
//                     wp_delete_file($size_file_path);
//                 }
//             }
//         }
//     }
    
//     wp_delete_file($file_path);

//     return $metadata;
// }
// _mrhe('open_storage_api') ? add_filter('wp_generate_attachment_metadata', 'handle_upload_to_storage', 10, 2) : false;

// /**
//  * 删除远程文件
//  */
// function delete_remote_file($attachment_id) {
//     // 删除主文件
//     $filename = get_post_meta($attachment_id, 'remote_file_name', true);
//     $delete_key = get_post_meta($attachment_id, 'remote_delete_key', true);
    
//     if ($filename && $delete_key) {
//         $data = [
//             'Header' => [
//                 "Accept: application/json",
//                 "Authorization: Bearer " . STORAGE_API_TOKEN
//             ],
//             'post' => [
//                 'action' => 'delete',
//                 'filename' => $filename,
//                 'delete_key' => $delete_key
//             ]
//         ];
//         custom_curl(STORAGE_API_URL . '/api/file_manage.php', $data);
//     }
    
//     // 删除缩略图
//     $meta = wp_get_attachment_metadata($attachment_id);
//     if (isset($meta['sizes'])) {
//         foreach ($meta['sizes'] as $size => $size_info) {
//             $size_filename = get_post_meta($attachment_id, 'remote_file_name_' . $size, true);
//             $size_delete_key = get_post_meta($attachment_id, 'remote_delete_key_' . $size, true);
            
//             if ($size_filename && $size_delete_key) {
//                 $data = [
//                     'Header' => [
//                         "Accept: application/json",
//                         "Authorization: Bearer " . STORAGE_API_TOKEN
//                     ],
//                     'post' => [
//                         'action' => 'delete',
//                         'filename' => $size_filename,
//                         'delete_key' => $size_delete_key
//                     ]
//                 ];
//                 custom_curl(STORAGE_API_URL . '/api/file_manage.php', $data);
//             }
//         }
//     }
// }
// _mrhe('open_storage_api') ? add_action('delete_attachment', 'delete_remote_file') : false; 

// 将友情链接挂载到wp_footer钩子的最前面
function zib_footer_friendlinks() {
    ?>
    <div class="footer-links d-none d-lg-block">
        <div class="container">
            <strong>友情链接：</strong>
            <ul class="friendlinks-ul">
            <?php 
                $flinks = zib_get_option('flinks_s');
                if ( isset($flinks['flinks_cat']) ) {
                    $bookmarks = get_bookmarks(array(
                        'orderby'  => 'rand',
                        'category' => $flinks['flinks_cat']
                    ));
                    foreach ($bookmarks as $item) {
                        $nofollow = (!empty($item->link_rel)) 
                            ? ' rel="nofollow noopener noreferrer"' 
                            : ' rel="noopener noreferrer"' ;
                        echo '<li><a target="'.$item->link_target.'" href="'.$item->link_url.'" title="'.$item->link_name.'"'.$nofollow.'>'.$item->link_name.'</a></li>';
                    }
                } else {
                    echo '请后台设置友情链接！';
                }
            ?>
            </ul>
        </div>
    </div>
    <?php
}

// 使用优先级1确保在wp_footer最前面执行
if ( zib_get_option('flinks_footer_s') && is_home() ) { 
    add_action('wp_footer', 'zib_footer_friendlinks', 1);
}
