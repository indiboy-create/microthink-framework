<?php

declare(strict_types=1);

namespace app\controller\install;

use mini\Db;
use mini\Request;
use mini\Response;
use RuntimeException;
use Throwable;

final class Install
{
    private string $root;

    public function __construct()
    {
        $this->root = dirname(__DIR__, 3);
    }

    public function index(Request $request): string|Response
    {
        if (!$this->isAllowed($request)) {
            return $this->forbidden();
        }

        if ($this->isInstalled()) {
            return view('install/index', [
                'checks' => $this->checks(),
                'drivers' => $this->drivers(),
                'canInstall' => false,
                'installed' => true,
                'token' => '',
            ]);
        }

        $checks = $this->checks();

        return view('install/index', [
            'checks' => $checks,
            'drivers' => $this->drivers(),
            'canInstall' => !in_array(false, array_column($checks, 'ok'), true),
            'installed' => false,
            'token' => $this->token(),
        ]);
    }

    public function run(Request $request): array|Response
    {
        if (!$this->isAllowed($request)) {
            return Response::json(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        if ($this->isInstalled()) {
            return ['ok' => false, 'error' => '系统已安装'];
        }

        try {
            $tokenValid = $this->validToken($request);
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        if (!$tokenValid) {
            return Response::json(['ok' => false, 'error' => '安装令牌无效，请刷新安装页面'], 403);
        }

        $driver = strtolower((string) $request->input('driver', 'sqlite'));

        if (!in_array($driver, ['sqlite', 'mysql'], true)) {
            return ['ok' => false, 'error' => '不支持的数据库驱动'];
        }

        $extension = $driver === 'mysql' ? 'pdo_mysql' : 'pdo_sqlite';

        if (!extension_loaded($extension)) {
            return ['ok' => false, 'error' => '当前 PHP 未启用 ' . $extension];
        }

        try {
            $dbConfig = $this->buildConfig($driver, $request);
            $db = new Db($dbConfig);

            $this->import($db, $driver);
            $this->writeConfig($dbConfig);
            $this->lock();
            $this->removeToken();

            return ['ok' => true, 'redirect' => '/install/complete'];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function complete(): string
    {
        return view('install/complete');
    }

    private function isAllowed(Request $request): bool
    {
        $allowed = (array) config('install.allowed_ips', ['127.0.0.1', '::1']);

        return in_array($request->ip(), $allowed, true);
    }

    private function forbidden(): Response
    {
        return Response::html(
            '<!doctype html><html lang="zh-CN"><head><meta charset="utf-8"><title>403</title></head>'
            . '<body><h1>403 Forbidden</h1><p>安装向导仅允许本机访问。</p></body></html>',
            403
        );
    }

    private function isInstalled(): bool
    {
        return is_file($this->root . '/database/install.lock');
    }

    private function tokenFile(): string
    {
        return $this->root . '/database/install.token';
    }

    private function token(): string
    {
        $file = $this->tokenFile();

        if (is_file($file)) {
            $token = trim((string) file_get_contents($file));

            if ($token !== '') {
                return $token;
            }
        }

        $token = bin2hex(random_bytes(32));
        $dir = dirname($file);

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('无法创建 database 目录');
        }

        if (file_put_contents($file, $token, LOCK_EX) === false) {
            throw new RuntimeException('无法写入安装令牌');
        }

        @chmod($file, 0600);

        return $token;
    }

    private function validToken(Request $request): bool
    {
        $provided = (string) ($request->input('_token') ?? $request->header('X-Install-Token', ''));
        $expected = $this->token();

        return $provided !== '' && hash_equals($expected, $provided);
    }

    private function removeToken(): void
    {
        $file = $this->tokenFile();

        if (is_file($file)) {
            @unlink($file);
        }
    }

    private function checks(): array
    {
        $configWritable = is_writable($this->root . '/config/app.php');
        $databaseDir = $this->root . '/database';
        $databaseWritable = is_dir($databaseDir) ? is_writable($databaseDir) : is_writable($this->root);
        $driverReady = extension_loaded('pdo_sqlite') || extension_loaded('pdo_mysql');

        return [
            [
                'name' => 'PHP 版本',
                'ok' => PHP_VERSION_ID >= 80300,
                'hint' => PHP_VERSION,
            ],
            [
                'name' => 'PDO 扩展',
                'ok' => extension_loaded('pdo'),
                'hint' => extension_loaded('pdo') ? '已启用' : '未启用',
            ],
            [
                'name' => 'JSON 扩展',
                'ok' => extension_loaded('json'),
                'hint' => extension_loaded('json') ? '已启用' : '未启用',
            ],
            [
                'name' => '数据库驱动',
                'ok' => $driverReady,
                'hint' => 'pdo_sqlite 或 pdo_mysql 至少启用一个',
            ],
            [
                'name' => 'config/app.php 可写',
                'ok' => $configWritable,
                'hint' => $configWritable ? '可写' : '不可写',
            ],
            [
                'name' => 'database 目录可写',
                'ok' => $databaseWritable,
                'hint' => $databaseWritable ? '可写' : '不可写',
            ],
            [
                'name' => 'SQL 文件',
                'ok' => is_file($databaseDir . '/schema.sql')
                    && is_file($databaseDir . '/schema.mysql.sql'),
                'hint' => 'schema.sql / schema.mysql.sql',
            ],
        ];
    }

    private function drivers(): array
    {
        return [
            'sqlite' => [
                'name' => 'SQLite',
                'enabled' => extension_loaded('pdo_sqlite'),
            ],
            'mysql' => [
                'name' => 'MySQL',
                'enabled' => extension_loaded('pdo_mysql'),
            ],
        ];
    }

    private function buildConfig(string $driver, Request $request): array
    {
        if ($driver === 'sqlite') {
            $databaseDir = $this->root . '/database';

            if (!is_dir($databaseDir) && !mkdir($databaseDir, 0777, true) && !is_dir($databaseDir)) {
                throw new RuntimeException('无法创建 database 目录');
            }

            return [
                'driver' => 'sqlite',
                'database' => $databaseDir . DIRECTORY_SEPARATOR . 'app.sqlite',
            ];
        }

        $database = (string) $request->input('database', '');

        if ($database === '') {
            throw new RuntimeException('数据库名不能为空');
        }

        return [
            'driver' => 'mysql',
            'host' => (string) $request->input('host', '127.0.0.1'),
            'port' => (int) $request->input('port', 3306),
            'database' => $database,
            'username' => (string) $request->input('username', 'root'),
            'password' => (string) $request->input('password', ''),
            'charset' => (string) $request->input('charset', 'utf8mb4'),
        ];
    }

    private function import(Db $db, string $driver): void
    {
        $file = $this->root . '/database/'
            . ($driver === 'mysql' ? 'schema.mysql.sql' : 'schema.sql');

        $schema = file_get_contents($file);

        if ($schema === false) {
            throw new RuntimeException('无法读取 SQL 文件：' . $file);
        }

        foreach (explode(';', $schema) as $statement) {
            $statement = preg_replace('/^\s*--.*$/m', '', $statement);
            $statement = trim((string) $statement);

            if ($statement === '') {
                continue;
            }

            $db->query($statement);
        }
    }

    private function writeConfig(array $dbConfig): void
    {
        $path = $this->root . '/config/app.php';
        $source = file_get_contents($path);

        if ($source === false) {
            throw new RuntimeException('无法读取 config/app.php');
        }

        $lines = explode(PHP_EOL, $source);
        $output = [];
        $inDb = false;
        $depth = 0;
        $replaced = false;

        foreach ($lines as $line) {
            if (!$inDb && preg_match("/^\s*'db'\s*=>\s*\[/", $line) === 1) {
                $inDb = true;
                $depth = 1;
                $replaced = true;
                $output[] = "    'db' => [";

                foreach ($this->dbLines($dbConfig) as $dbLine) {
                    $output[] = $dbLine;
                }

                continue;
            }

            if ($inDb) {
                $depth += substr_count($line, '[') - substr_count($line, ']');

                if ($depth <= 0) {
                    $inDb = false;
                    $output[] = '    ],';
                }

                continue;
            }

            if (preg_match("/^\s*'debug'\s*=>\s*(?:true|false),/", $line) === 1) {
                $output[] = (string) preg_replace(
                    "/=>\s*(?:true|false),/",
                    '=> false,',
                    $line
                );

                continue;
            }

            $output[] = $line;
        }

        if (!$replaced) {
            throw new RuntimeException('无法定位 config/app.php 中的 db 配置');
        }

        if (file_put_contents($path, implode(PHP_EOL, $output) . PHP_EOL) === false) {
            throw new RuntimeException('无法写入 config/app.php');
        }
    }

    private function dbLines(array $dbConfig): array
    {
        $lines = [];

        foreach ($dbConfig as $key => $value) {
            $lines[] = '        ' . var_export((string) $key, true) . ' => ' . var_export($value, true) . ',';
        }

        return $lines;
    }

    private function lock(): void
    {
        $databaseDir = $this->root . '/database';

        if (!is_dir($databaseDir) && !mkdir($databaseDir, 0777, true) && !is_dir($databaseDir)) {
            throw new RuntimeException('无法创建 database 目录');
        }

        file_put_contents($databaseDir . DIRECTORY_SEPARATOR . 'install.lock', date('Y-m-d H:i:s'));
    }
}
