<?php
//修改子比主题go.php跳转文件所在目录为当前主题
function zib_gophp_template_changes()
{
    $golink = get_query_var('golink');
    if ($golink) {
        global $wp_query;
        $wp_query->is_home = false;
        $wp_query->is_page = true; //将该模板改为页面属性，而非首页
        $template          = get_stylesheet_directory() . '/go.php';
        @session_start();
        $_SESSION['GOLINK'] = $golink;
        load_template($template);
        exit;
    }
}
add_action('template_redirect', 'zib_gophp_template_changes', 4);

// 链接提交的模态框
function zib_submit_links_modal_changes($args = array())
{
    $defaults = array(
        'class'      => '',
        'title'      => '申请入驻',
        'dec'        => '',
        'show_title' => true,
        'sign'       => true,
        'cats'       => [],
    );

    $args = wp_parse_args((array) $args, $defaults);

    $title = $args['title'];
    if ($title) {
        $title = '<div class="mb20"><button class="close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button><b class="modal-title flex ac"><span class="toggle-radius mr10 b-theme"><i class="fa fa-pencil-square-o"></i></span>' . $title . '</b></div>';
    }

    $input = '';

    if ($args['dec']) {
        $input .= '<div class="muted-box em09">' . $args['dec'] . '</div>';
    }

    if ($args['sign'] && !get_current_user_id()) {
        $input .= '<div class="muted-box text-center">';
        $input .= '<div class="mb20 muted-3-color">请先登录</div>';
        $input .= '<p>';
        $input .= '<a href="javascript:;" class="signin-loader but c-blue padding-lg"><i class="fa fa-fw fa-sign-in mr10" aria-hidden="true"></i>登录</a>';
        $input .= !zib_is_close_signup() ? '<a href="javascript:;" class="signup-loader ml10 but c-yellow padding-lg"><i data-class="icon mr10" data-viewbox="0 0 1024 1024" data-svg="signup" aria-hidden="true"></i>注册</a>' : '';
        $input .= '</p>';
        $input .= '<div class="social_loginbar">';
        $input .= zib_social_login(false);
        $input .= '</div>';
        $input .= '</div>';
    } else {
        $cats_query_args = array(
            'taxonomy'   => array('link_category'),
            'hide_empty' => false,
        );

        if ($args['cats']) {
            $cats_query_args['include'] = $args['cats'];
            $cats_query_args['orderby'] = 'include';
        }
        $cats_query = new WP_Term_Query($cats_query_args);

        $cats_options = '';
        if (!is_wp_error($cats_query) && !empty($cats_query->terms)) {
            foreach ($cats_query->terms as $item) {
                $cats_options .= '<option value="' . $item->term_id . '">' . $item->name . '</option>';
            }
        }
        $cats_options = $cats_options ? '<div class="col-sm-12 mb10">
            <div class="em09 muted-2-color mb6">网站类别</div>
            <div class="form-select"><select name="link_category" class="form-control">' . $cats_options . '</select></div>
        </div>' : '';

        $input .= '<form class="form-horizontal mt10 form-upload">';

        $input .= '<div class="row gutters-5">
                        <div class="col-sm-6 mb10">
                            <div class="em09 muted-2-color mb6">网站名称（必填）</div>
                            <input type="text" class="form-control" id="link_name" name="link_name" placeholder="请输入网站名称">
                        </div>
                        <div class="col-sm-6 mb10">
                            <div class="em09 muted-2-color mb6">网站地址（必填）</div>
                            <input type="text" class="form-control" id="link_url" name="link_url" placeholder="https://...">
                        </div>

                    <div class="col-sm-12 mb10">
                        <div class="em09 muted-2-color mb6">网站简介</div>
                        <input type="text" class="form-control" id="link_description" name="link_description" placeholder="一句话介绍网站">
                    </div>
                     ' . $cats_options . '
                    <div class="col-sm-12 mb10">
                        <div class="em09 muted-2-color mb6">LOGO图像链接（选填）</div>
                        <input type="text" class="form-control" id="link_logo" name="link_logo" placeholder="请输入LOGO图片链接">
                    </div>
                </div>';

        //人机验证
        if (_pz('verification_links_s')) {
            $verification_input = zib_get_machine_verification_input('frontend_links_submit');
            if ($verification_input) {
                $input .= '<div class="col-sm-9" style="max-width: 300px;">' . $verification_input . '</div>';
            }
        }

        $input .= '<div class="text-right edit-footer">
                        <button class="but c-blue padding-lg wp-ajax-submit"><i class="fa fa-check" aria-hidden="true"></i>提交链接</button>
                    </div>';
        $input .= wp_nonce_field('frontend_links_submit', '_wpnonce', false, false); //安全效验
        $input .= '<input type="hidden" name="action" value="frontend_links_submit">';
        $input .= '</form>';
    }

    $card = $input;

    $html = '<div class="modal fade" id="submit-links-modal" tabindex="-1" role="dialog" aria-hidden="false">    <div class="modal-dialog" role="document">    <div class="modal-content" style=""><div class="modal-body">' . $title . $card . '</div></div>    </div>    </div>';

    return $html;
}

/**前端AJAX链接提交 -- 修改\action\function.php */
function zib_ajax_frontend_links_submit_changes()
{
    //人机验证
    if (_pz('verification_links_s')) {
        zib_ajax_man_machine_verification();
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    if (isset($_COOKIE['zib_links_submit_time'])) {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '操作过于频繁，请稍候再试')));
        exit();
    }
    if (empty($_POST['link_name']) || mb_strlen($_POST['link_name']) > 20 ) {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '连接名称必须填写，且长度不得超过30字')));
        exit();
    }
    if (empty($_POST['link_url']) || strlen($_POST['link_url']) > 60 ) {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '链接地址不正确！请重新填写,注意需要添加http://或者https://')));
        exit();
    }

    if(!zib_is_url($_POST['link_url'])){
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '网站地址格式错误')));
        exit();
    }

    // 处理LOGO图像链接
    $logo_image_url = !empty($_POST['link_logo']) ? esc_url($_POST['link_logo']) : '';

    /**准备数据 */
    $linkdata = array(
        'link_name'        => esc_attr('【待审核】--- '.$_POST['link_name']),
        'link_url'         => esc_url($_POST['link_url']),
        'link_description' => !empty($_POST['link_description']) ? esc_attr($_POST['link_description']) : '',
        'link_image'       => $logo_image_url,
        'link_target'      => '_blank',
        'link_visible'     => is_super_admin() ? 'Y' : 'N',//是否直接显示添加的链接到分类
        'link_category'    => !empty($_POST['link_category']) ? $_POST['link_category'] : array(),
    );

    //何先生新增代码 本业修改大量代码
    function my_file_get_contents($url, $timeout = 30) {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
    		//$httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
            curl_close($ch);
        } else if (ini_get('allow_url_fopen') == 1 || strtolower(ini_get('allow_url_fopen')) == 'on') {
            $file_contents = @file_get_contents($url);
        } else {
            $file_contents = '';
        }
        return $file_contents;
    }
    	
    function isExistsContentUrl($url, $mydomain = "") {
        if (!isset($url) || empty($url)) {
            zib_send_json_error("填写的URL为空");
            exit();
        }
        if (!isset($mydomain) || empty($mydomain)) {
            $mydomain = $_SERVER['SERVER_NAME'];
        }
        $resultContent = my_file_get_contents($url);
        if (trim($resultContent) == '') {
            zib_send_json_error("未获得URL相关数据，请重试！");
            exit();
        }
        if (strripos($resultContent, $mydomain)) {
            return true;
        } else {
            zib_send_json_error("系统检测到您还未添加本站链接！请添加首页后重试！");
            exit();
        }
    }

    $patten = home_url();
    $ret = isExistsContentUrl($_POST['link_url'], $patten);
    
    if($ret){
        $linkdata = wp_unslash(sanitize_bookmark($linkdata, 'db'));
    
        //禁止重复提交
        global $wpdb;
        $search = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM $wpdb->links WHERE link_url = %s", $linkdata['link_url']));
    
        if ($search) {
            zib_send_json_error('您的链接已提交，请勿重复提交');
        }
    
        /**添加链接 */
        $links_id = wp_insert_link($linkdata);
        if (is_wp_error($links_id)) {
            echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $links_id->get_error_message())));
            exit();
        }
    
        //设置浏览器缓存限制提交的间隔时间
        $expire = time() + 30;
        setcookie('zib_links_submit_time', time(), $expire, '/', '', false);
    
        $send_msg = $linkdata['link_visible'] === 'Y' ? '提交成功' : '提交成功，等待管理员处理';
    	echo (json_encode(array('msg' => $send_msg, 'reload' => true)));
        /**添加执行挂钩 */
        do_action('zib_ajax_frontend_links_submit_changes_success', $linkdata);
        exit();
    }
}
add_action('wp_ajax_frontend_links_submit', 'zib_ajax_frontend_links_submit_changes',9);
add_action('wp_ajax_nopriv_frontend_links_submit', 'zib_ajax_frontend_links_submit_changes',9);

/*
 * ========================================
 * 会员免评论查看功能
 * ========================================
 * 功能说明：为一级和二级会员用户提供免评论查看"评论可见"内容的特权
 * 支持单独控制一级和二级会员的权限
 */
function zib_vip_can_view_without_comment($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }
    // 检查用户是否为会员
    if (function_exists('zib_get_user_vip_level')) {
        $vip_level = zib_get_user_vip_level($user_id);
        if ($vip_level == 1) {
            // 一级会员，检查一级会员开关
            return _mrhe('vip1_skip_comment_view', false);
        } elseif ($vip_level == 2) {
            // 二级会员，检查二级会员开关
            return _mrhe('vip2_skip_comment_view', false);
        }
    }
    return false;
}
function vip_skip_comment_view_filter($show, $type)
{
    // 只处理 reply 类型的隐藏内容
    if ($type === 'reply' && function_exists('zib_vip_can_view_without_comment')) {
        return zib_vip_can_view_without_comment();
    }
    return $show;
}
add_filter('hidecontent_is_show', 'vip_skip_comment_view_filter', 10, 2);
/**
 * 修改旧版 reply 短代码的显示逻辑
 * 通过重新定义 reply_to_read 函数来实现会员免评论查看
 */
function vip_reply_to_read($atts, $content = null)
{
    $a = '#commentform';
    extract(shortcode_atts(array("notice" => '<a class="hidden-text" href="javascript:(scrollTopTo(\'' . $a . '\',-50));"><i class="fa fa-exclamation-circle"></i>  此处内容已隐藏，请评论后刷新页面查看.</a>'), $atts));
    $_hide = '<div class="hidden-box">' . $notice . '</div>';
    $_show = '<div class="hidden-box show"><div class="hidden-text">本文隐藏内容</div>' . do_shortcode($content) . '</div>';
    if (is_super_admin()) {
        //管理员登陆直接显示内容
        return '<div class="hidden-box show"><div class="hidden-text">本文隐藏内容 - 管理员可见</div>' . do_shortcode($content) . '</div>';
    } elseif (function_exists('zib_vip_can_view_without_comment') && zib_vip_can_view_without_comment()) {
        //会员用户免评论查看
        $vip_level = function_exists('zib_get_user_vip_level') ? zib_get_user_vip_level() : 0;
        $vip_name = _pz('pay_user_vip_' . $vip_level . '_name', '会员');
        return '<div class="hidden-box show"><div class="hidden-text">本文隐藏内容 - ' . $vip_name . '专享</div>' . do_shortcode($content) . '</div>';
    } else {
        if (function_exists('zib_user_is_commented') && zib_user_is_commented()) {
            return $_show;
        } else {
            return $_hide;
        }
    }
}
remove_shortcode('reply');
add_shortcode('reply', 'vip_reply_to_read');


// /**
//  * WordPress 序列化数据批量替换脚本
//  * 将 https://api.hexsen.com/API/googleimg.php 中的 API 改为 api
//  */

// // 数据库配置
// $db_host = 'localhost';
// $db_name = 'hexsen_com';
// $db_user = 'hexsen_com';
// $db_pass = 'IxiRXLfZI1Co97Nx';
// $table_name = 'wp_usermeta';

// // 执行模式：false=测试模式（不修改），true=执行更新
// $execute_update = false;

// try {
//     $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
//     echo "=== WordPress URL 批量替换工具 ===\n";
//     echo "表: $table_name\n";
//     echo "字段: meta_key = 'zib_other_data'\n";
//     echo "替换: /API/googleimg.php → /api/googleimg.php\n";
//     echo "模式: " . ($execute_update ? "【执行更新】" : "【测试模式】") . "\n";
//     echo "=====================================\n\n";
    
//     // 第一步：查找所有包含目标URL的记录
//     $stmt = $pdo->prepare("
//         SELECT umeta_id, user_id, meta_value 
//         FROM $table_name 
//         WHERE meta_key = 'zib_other_data'
//         AND meta_value LIKE '%api.hexsen.com%googleimg.php%'
//     ");
//     $stmt->execute();
//     $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
//     echo "第一步：搜索记录\n";
//     echo "找到 " . count($results) . " 条包含 googleimg.php 的记录\n\n";
    
//     if (count($results) == 0) {
//         echo "未找到需要处理的记录。\n";
//         exit(0);
//     }
    
//     // 第二步：分析和处理每条记录
//     $need_update = 0;
//     $already_correct = 0;
//     $updated = 0;
//     $failed = 0;
    
//     echo "第二步：逐条检查\n";
//     echo str_repeat("-", 60) . "\n";
    
//     foreach ($results as $row) {
//         $umeta_id = $row['umeta_id'];
//         $user_id = $row['user_id'];
//         $old_value = $row['meta_value'];
        
//         // 检查是否包含大写的 /API/
//         $has_uppercase = strpos($old_value, '/API/googleimg.php') !== false;
        
//         if (!$has_uppercase) {
//             echo "✓ umeta_id: $umeta_id (user_id: $user_id) - 已是小写，跳过\n";
//             $already_correct++;
//             continue;
//         }
        
//         echo "\n○ umeta_id: $umeta_id (user_id: $user_id)\n";
        
//         // 统计出现次数
//         $count = substr_count($old_value, '/API/googleimg.php');
//         echo "  发现 $count 处大写 /API/ 需要替换\n";
        
//         // 执行替换
//         $new_value = str_replace(
//             'https://api.hexsen.com/API/googleimg.php',
//             'https://api.hexsen.com/api/googleimg.php',
//             $old_value
//         );
        
//         // 验证序列化数据完整性
//         $test_old = @unserialize($old_value);
//         $test_new = @unserialize($new_value);
        
//         if ($test_old === false) {
//             echo "  ✗ 错误：原数据不是有效的序列化格式\n";
//             $failed++;
//             continue;
//         }
        
//         if ($test_new === false) {
//             echo "  ✗ 错误：替换后数据序列化验证失败\n";
//             $failed++;
//             continue;
//         }
        
//         echo "  ✓ 序列化验证通过\n";
//         $need_update++;
        
//         // 如果是执行模式，进行更新
//         if ($execute_update) {
//             try {
//                 $update_stmt = $pdo->prepare("
//                     UPDATE $table_name 
//                     SET meta_value = :new_value 
//                     WHERE umeta_id = :umeta_id
//                 ");
//                 $result = $update_stmt->execute([
//                     'new_value' => $new_value,
//                     'umeta_id' => $umeta_id
//                 ]);
                
//                 if ($result) {
//                     echo "  ★ 已更新\n";
//                     $updated++;
//                 } else {
//                     echo "  ✗ 更新失败\n";
//                     $failed++;
//                 }
//             } catch (PDOException $e) {
//                 echo "  ✗ 更新异常: " . $e->getMessage() . "\n";
//                 $failed++;
//             }
//         } else {
//             echo "  → 测试模式，未执行更新\n";
//         }
//     }
    
//     // 统计结果
//     echo "\n" . str_repeat("=", 60) . "\n";
//     echo "处理完成\n";
//     echo str_repeat("=", 60) . "\n";
//     echo "总记录数: " . count($results) . "\n";
//     echo "已是正确格式: $already_correct\n";
//     echo "需要更新: $need_update\n";
    
//     if ($execute_update) {
//         echo "成功更新: $updated\n";
//         echo "失败: $failed\n";
//     } else {
//         echo "\n⚠ 当前为测试模式，未实际修改数据库\n";
//         echo "如需执行更新，请将脚本中的 \$execute_update 改为 true\n";
//     }
    
// } catch (PDOException $e) {
//     echo "数据库连接错误: " . $e->getMessage() . "\n";
//     exit(1);
// }