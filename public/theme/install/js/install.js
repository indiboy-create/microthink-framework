layui.use(['form', 'layer'], function () {
    var form = layui.form;
    var layer = layui.layer;
    var $ = layui.$;

    function toggleDriver(value) {
        if (value === 'mysql') {
            $('#sqlite-box').hide();
            $('#mysql-box').show();
        } else {
            $('#mysql-box').hide();
            $('#sqlite-box').show();
        }
        form.render(null, 'install-form');
    }

    toggleDriver($('select[name="driver"]').val());

    form.on('select(driver)', function (data) {
        toggleDriver(data.value);
    });

    form.on('submit(install)', function (data) {
        var loading = layer.load(1, {shade: [0.3, '#fff']});

        $.post('/install', data.field, function (res) {
            layer.close(loading);

            if (res.ok) {
                layer.msg('安装成功', {icon: 1, time: 1000}, function () {
                    location.href = res.redirect;
                });
            } else {
                layer.alert(res.error || '安装失败', {icon: 2});
            }
        }, 'json').fail(function () {
            layer.close(loading);
            layer.alert('请求失败，请稍后重试', {icon: 2});
        });

        return false;
    });
});
