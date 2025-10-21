/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2020-11-27 22:18:32
 * @LastEditTime: 2025-10-06 21:45:25
 */
$(window).load(function () {
    function get_mrhe_api(data, c, _this, type) {
        notyf('正在处理请稍等...', 'load', '', 'user_ajax');
        var _tt = _this.html();
        _this.attr('disabled', true).html('请稍候'),
        (c = c || '处理完成');
        $.ajax({
            type: 'POST',
            url: _win.ajax_url,
            data: data,
            dataType: 'json',
            success: function (n) {
                var ys = n.ys ? n.ys : n.error ? 'danger' : '';
                notyf(n.msg || c, ys, '', 'user_ajax');
                var _txet_1 = '恭喜您，授权添加成功';
                var _txet_2 = '请刷新页面查看详细信息';
                var _button = '';
                if (type == 'oldaut') {
                    _txet_1 = '查询到以下信息';
                    _txet_2 = '请确认信息是否匹配，确认无误后请点击下方按钮以导入到您的数据中';
                    _button = '<div class="box-body text-center"><a href="javascript:;" class="but jb-blue radius btn-block padding-lg import-ordery" style="max-width:260px;">确认导入</a></div>';
                    addzibaut = n;
                }
                if (type == 'reload' && !n.error) {
                    return location.reload();
                }
                if (type == 'add' && !n.error) {
                    return location.reload();
                }
                if (n.authorization_code) {
                    var _url = '';
                    for (var j = 0, len = n.authorization_url.length; j < len; j++) {
                        _url += '<span class="badg mr6 mb6 c-blue">' + n.authorization_url[j] + '</span>';
                    }
                    var _html = '<div class="text-center mt10 mb10 c-red"><i class="fa fa-shield mr10"></i>' + _txet_1 + '</div>\
                <div class="mb10"><div class="author-set-left">授权码</div><div class="author-set-right"><b class="badg mr6 mb6 c-red">' + n.authorization_code + '</b></div></div>\
                <div class="mb10"><div class="author-set-left">授权域名</div><div class="author-set-right">' + _url + '</div></div>\
                <div class="text-center mt10 mb10 muted-2-color">' + _txet_2 + '</div>' + _button;
                    _this.parents('form').html(_html);
                } else {
                    _this.attr('disabled', false).html(_tt).addClass('jb-red');
                }
                return false;
            },
        });
    }
    var $document = $(document);
    $document.on('click', '.addzibaut', function () {
        var _this = $(this);
        var form = _this.parents('form');
        var inputs = form.serializeObject();
        get_mrhe_api(inputs, '恭喜您，授权添加成功', _this, 'add');
    });
    /**导入老用户授权信息 */
    var addzibaut = {}; //存储老用户授权信息
    $document.on('click', '.query-ordery', function () {
        var _this = $(this);
        var form = _this.parents('form');
        var inputs = form.serializeObject();
        get_mrhe_api(inputs, '查询到授权信息', _this, 'oldaut');
    });
    $document.on('click', '.import-ordery', function () {
        var _this = $(this);
        var inputs = addzibaut;
        inputs.action = 'import_autordery';
        get_mrhe_api(inputs, '导入成功！正在刷新页面', _this, 'reload');
    });
    $document.on('click', '.eye-aut', function () {
        var _this = $(this);
        var code = _this.attr('text');
        var slash_code = _this.attr('slash-text');
        var _class = 'slash';
        var $code = _this.siblings('b');
        var $icon = _this.find('i');
        if (_this.hasClass(_class)) {
            // 当前是显示完整状态（有slash class），点击后隐藏
            _this.removeClass(_class);
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
            $code.text(slash_code);
        } else {
            // 当前是隐藏状态（无slash class），点击后显示完整
            _this.addClass(_class);
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
            $code.text(code);
        }
    });
    /**刷新授权信息 */
    $document.on('click', '.refresh-autordery', function () {
        var _this = $(this);
        var form = _this.parents('form');
        var inputs = form.serializeObject();
        // action 已经在表单中设置，不需要修改
        get_mrhe_api(inputs, '信息已更新！正在刷新页面', _this, 'reload');
    });
    $document.on('input', '.user-api-aut-admin input[name="aut_url"]', function () {
        var form = $(this).parents('form');
        form.find('input[name="new_aut_url"]').focus();
    });
    $document.on('zib_ajax.success', '.replace-someone-aut-btn', function (n, data) {
        if (!data.error) {
            var $autosearch = $(this).parents('form').find('.auto-search');
            $autosearch.find('.search-input').val('');
            $autosearch.find('.search-remind').html('输入域名/订单号/授权码以搜索授权域名');
            $autosearch.find('.search-centent').html('<div class="text-center muted-2-color">暂无数据</div>');
        }
    });

    /**产品选择器 - 切换显示不同产品的授权信息 */
    $document.on('change', '#mrhe-product-selector', function() {
        var product_id = $(this).val();
        var current_url = window.location.href.split('?')[0].split('#')[0];
        var new_url = current_url + '?product_id=' + product_id + '#user-tab-product';
        window.location.href = new_url;
    });
});