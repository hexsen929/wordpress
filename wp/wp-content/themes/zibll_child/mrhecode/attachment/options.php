<?php
/**
 * 前端附件管理 - 后台设置
 */

if (!defined('ABSPATH')) {
    exit;
}

// 获取 CSF 前缀（与主设置保持一致）
$prefix = 'mrhe_options';

CSF::createSection($prefix, array(
    'title'       => '前端附件管理',
    'icon'        => 'fa fa-file-image-o',
    'description' => '前端附件管理设置，包括用户中心和作者相册',
    'fields'      => array(
        array(
            'type'    => 'submessage',
            'style'   => 'info',
            'content' => '<h4><i class="fa fa-info-circle"></i> 功能说明</h4>
                         <p>• 用户可在用户中心查看和管理自己上传的所有附件（图片、视频、音频、文档等）</p>
                         <p>• 支持图片预览、视频播放（DPlayer）、音频播放</p>
                         <p>• 支持删除附件功能，用户只能删除自己上传的文件</p>
                         <p>• 支持 AJAX 分页加载，提升用户体验</p>
                         <p>• 作者主页支持个人相册功能，展示上传的图片和视频</p>
                         <p>• 所有操作都有权限验证和 Nonce 安全检查</p>',
        ),
        array(
            'id'      => 'attachment_manager_s',
            'type'    => 'switcher',
            'title'   => '启用前端附件管理功能',
            'desc'    => '⚠️ 如果已启用 zib-uploads 插件，请关闭此选项避免冲突。开启后用户可在用户中心和作者主页查看和管理附件。',
            'default' => false,
        ),
        array(
            'id'         => 'attachment_list_number',
            'type'       => 'spinner',
            'title'      => '单页加载数量',
            'desc'       => '设置附件列表每页显示的附件数量',
            'default'    => 16,
            'min'        => 4,
            'max'        => 100,
            'step'       => 4,
            'unit'       => '张',
            'dependency' => array('attachment_manager_s', '==', 'true'),
        ),
        array(
            'id'         => 'paging_ajax_s',
            'type'       => 'radio',
            'title'      => '列表翻页模式',
            'inline'     => true,
            'desc'       => '您可以在上面选项，以调整单页加载数量',
            'default'    => '1',
            'options'    => array(
                '1' => 'AJAX追加列表翻页',
                '0' => '数字翻页按钮',
            ),
            'dependency' => array('attachment_manager_s', '==', 'true'),
        ),
        array(
            'id'         => 'attachment_delete_enabled',
            'type'       => 'switcher',
            'title'      => '允许用户删除附件',
            'desc'       => '开启后用户可以删除自己上传的附件',
            'default'    => true,
            'dependency' => array('attachment_manager_s', '==', 'true'),
        ),
        array(
            'id'         => 'author_album_enable',
            'type'       => 'switcher',
            'title'      => '启用作者个人相册',
            'subtitle'   => '控制作者主页"个人相册"标签是否显示',
            'desc'       => '开启后在作者主页显示个人相册标签',
            'default'    => true,
            'dependency' => array('attachment_manager_s', '==', 'true'),
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
                array('attachment_manager_s', '==', 'true'),
                array('author_album_enable', '==', 'true'),
            ),
        ),
        array(
            'id'         => 'attachment_preview_video',
            'type'       => 'switcher',
            'title'      => '启用视频预览',
            'desc'       => '开启后使用 DPlayer 播放器预览视频文件',
            'default'    => true,
            'dependency' => array('attachment_manager_s', '==', 'true'),
        ),
        array(
            'id'         => 'attachment_preview_audio',
            'type'       => 'switcher',
            'title'      => '启用音频预览',
            'desc'       => '开启后使用 HTML5 音频播放器预览音频文件',
            'default'    => true,
            'dependency' => array('attachment_manager_s', '==', 'true'),
        ),
    )
));

