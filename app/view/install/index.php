<?php
if (!isset($installed)) {
    $installed = false;
}
if (!isset($checks) || !is_array($checks)) {
    $checks = [];
}
if (!isset($canInstall)) {
    $canInstall = false;
}
if (!isset($drivers) || !is_array($drivers)) {
    $drivers = [];
}
if (!isset($token) || !is_string($token)) {
    $token = '';
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>系统安装 - microthink-framework</title>
    <link rel="stylesheet" href="/theme/ui/layui/css/layui.css">
    <link rel="stylesheet" href="/theme/install/css/install.css">
</head>
<body>
<div class="install-wrap">
    <div class="layui-card">
        <div class="layui-card-header">microthink-framework 安装向导</div>
        <div class="layui-card-body install-card-body">
            <?php if ($installed): ?>
                <div class="layui-elem-quote">
                    系统已安装。如需重新安装，请先删除 <code>database/install.lock</code>。
                </div>
                <div class="install-actions">
                    <a href="/" class="layui-btn">进入首页</a>
                </div>
            <?php else: ?>
                <fieldset class="layui-elem-field layui-field-title">
                    <legend>环境检测</legend>
                </fieldset>

                <table class="layui-table">
                    <thead>
                    <tr>
                        <th>项目</th>
                        <th>状态</th>
                        <th>说明</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($checks as $check): ?>
                        <tr>
                            <td><?= e($check['name']) ?></td>
                            <td>
                                <?php if ($check['ok']): ?>
                                    <span class="layui-badge layui-bg-green">通过</span>
                                <?php else: ?>
                                    <span class="layui-badge">失败</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($check['hint']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <fieldset class="layui-elem-field layui-field-title">
                    <legend>数据库配置</legend>
                </fieldset>

                <?php if (!$canInstall): ?>
                    <div class="layui-elem-quote layui-quote-nm">
                        请先满足上述环境要求，再继续安装。
                    </div>
                <?php endif; ?>

                <form class="layui-form" action="/install" method="post" lay-filter="install-form">
                    <?php if (!empty($token)): ?>
                        <input type="hidden" name="_token" value="<?= e($token) ?>">
                    <?php endif; ?>
                    <div class="layui-form-item">
                        <label class="layui-form-label">数据库</label>
                        <div class="layui-input-block">
                            <select name="driver" lay-filter="driver">
                                <?php foreach ($drivers as $value => $driver): ?>
                                    <option value="<?= e($value) ?>" <?= $driver['enabled'] ? '' : 'disabled' ?>>
                                        <?= e($driver['name']) ?><?= $driver['enabled'] ? '' : '（未启用）' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div id="sqlite-box">
                        <div class="layui-form-item">
                            <label class="layui-form-label">数据库文件</label>
                            <div class="layui-input-block install-static">
                                database/app.sqlite
                            </div>
                        </div>
                    </div>

                    <div id="mysql-box" hidden>
                        <div class="layui-form-item">
                            <label class="layui-form-label">主机</label>
                            <div class="layui-input-block">
                                <input type="text" name="host" value="127.0.0.1" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">端口</label>
                            <div class="layui-input-block">
                                <input type="number" name="port" value="3306" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">库名</label>
                            <div class="layui-input-block">
                                <input type="text" name="database" class="layui-input" placeholder="microthink">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">用户名</label>
                            <div class="layui-input-block">
                                <input type="text" name="username" value="root" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">密码</label>
                            <div class="layui-input-block">
                                <input type="password" name="password" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">字符集</label>
                            <div class="layui-input-block">
                                <input type="text" name="charset" value="utf8mb4" class="layui-input">
                            </div>
                        </div>
                    </div>

                    <div class="install-actions">
                        <button class="layui-btn" lay-submit lay-filter="install" <?= $canInstall ? '' : 'disabled' ?>>
                            开始安装
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="/theme/ui/layui/layui.js"></script>
<script src="/theme/install/js/install.js"></script>
</body>
</html>
