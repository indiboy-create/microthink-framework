<?php

declare(strict_types=1);

$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, '请先运行 composer install' . PHP_EOL);
    exit(1);
}

require $autoload;

$root = dirname(__DIR__);
$config = require $root . '/config/app.php';
$driver = strtolower((string) ($config['db']['driver'] ?? 'sqlite'));

$extension = $driver === 'mysql' ? 'pdo_mysql' : 'pdo_sqlite';

if (!extension_loaded($extension)) {
    fwrite(STDERR, '当前 PHP 未启用 ' . $extension . '，请在 php.ini 中启用后重试。' . PHP_EOL);
    exit(1);
}

$schemaFile = __DIR__ . DIRECTORY_SEPARATOR
    . ($driver === 'mysql' ? 'schema.mysql.sql' : 'schema.sql');

$schema = file_get_contents($schemaFile);

if ($schema === false) {
    fwrite(STDERR, '无法读取数据库脚本：' . $schemaFile . PHP_EOL);
    exit(1);
}

try {
    $db = new mini\Db($config['db']);

    foreach (explode(';', $schema) as $statement) {
        $statement = preg_replace('/^\s*--.*$/m', '', $statement);
        $statement = trim((string) $statement);

        if ($statement === '') {
            continue;
        }

        $db->query($statement);
    }

    $lockFile = $root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'install.lock';

    if (file_put_contents($lockFile, date('Y-m-d H:i:s')) === false) {
        throw new \RuntimeException('无法写入 install.lock');
    }

    $target = $driver === 'mysql'
        ? (string) ($config['db']['host'] ?? '127.0.0.1') . '/' . (string) ($config['db']['database'] ?? '')
        : (string) ($config['db']['database'] ?? '');

    echo ucfirst($driver) . ' database initialized: ' . $target . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, ucfirst($driver) . ' 数据库初始化失败：' . $e->getMessage() . PHP_EOL);
    exit(1);
}
