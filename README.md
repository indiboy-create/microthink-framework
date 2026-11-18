# microthink-framework

一个参考 ThinkPHP8 设计思想的极简 PHP 框架，支持 SQLite 与 MySQL。

核心目标是：**只保留请求到响应的最小闭环，并精简到无法再精简。**

当前版本不包含 `Model` 封装，数据库访问统一使用 `Db::query()` 直接写 SQL。

## 特性

- PHP 8.3+
- 单一入口：`public/index.php`
- 显式数组路由
- `Request` / `Response` 对象
- 极简 `Container`，支持构造函数注入和方法参数注入
- 原生 PHP 模板视图
- 闭包中间件
- SQLite / MySQL 双驱动，默认使用 SQLite
- `Db` 提供 `query()` 与基础事务方法
- 基于 `league/glide` 的按需图片缩略图系统
- 帝国 CMS 风格模板占位符 `[!--field--]`
- 无 Model、无查询构造器、无 Facade、无模板引擎，仅缩略图依赖 `league/glide`

## 目录结构

```text
microthink-framework/
├─ .github/
│  └─ workflows/
│     └─ tests.yml
├─ composer.json
├─ composer.lock
├─ tests/
├─ public/
│  ├─ index.php
│  ├─ .htaccess
│  ├─ uploads/            缩略图源图目录
│  └─ theme/
│     ├─ editor/
│     ├─ install/
│     │  ├─ css/install.css
│     │  └─ js/install.js
│     └─ ui/layui/
├─ config/
│  └─ app.php
├─ route/
│  └─ web.php
├─ app/
│  ├─ common.php
│  ├─ controller/
│  │  ├─ article/
│  │  │  ├─ Article.php
│  │  │  └─ Category.php
│  │  ├─ install/
│  │  │  └─ Install.php
│  │  ├─ user/
│  │  │  └─ User.php
│  │  ├─ Index.php
│  │  └─ Thumbnail.php
│  └─ view/
│     ├─ index.php
│     └─ install/
│        ├─ index.php
│        └─ complete.php
├─ database/
│  ├─ schema.sql
│  ├─ schema.mysql.sql
│  └─ init.php
└─ src/
   ├─ App.php
   ├─ Container.php
   ├─ Request.php
   ├─ Response.php
   ├─ Router.php
   ├─ View.php
   └─ Db.php
```

## 安装

需要 PHP 8.3+，并启用以下扩展：

- `pdo`
- `json`
- `mbstring`
- `fileinfo`
- `pdo_sqlite`：使用 SQLite 时启用
- `pdo_mysql`：使用 MySQL 时启用
- `gd` 或 `imagick`：使用缩略图功能时启用

### 通过 Composer 创建项目

使用 `composer create-project` 直接创建项目：

```bash
composer create-project youvision/microthink-framework mt
```

该命令会创建 `mt` 目录、安装依赖，并自动创建 `public/uploads` 缩略图目录。创建完成后进入目录并启动服务：

```bash
cd mt
php -S 127.0.0.1:8000 -t public public/index.php
```

### 在已有目录安装

如果代码已经就位，只需安装依赖：

```bash
composer install
```

如果只需要重新生成自动加载：

```bash
composer dump-autoload
```

## 测试

开发依赖包含 PHPUnit，运行：

```bash
composer test
```

GitHub Actions 会在 push 和 pull request 时自动执行同一命令。

## Web 安装向导

项目提供 Web 安装向导：

```text
/install
```

功能：

- 检查 PHP 版本、PDO、JSON、数据库驱动、目录权限和 SQL 文件
- 选择 SQLite 或 MySQL
- 根据驱动导入 `schema.sql` 或 `schema.mysql.sql`
- 自动写入 `config/app.php`
- 创建 `database/install.lock` 防止重复安装
- 默认仅允许 `127.0.0.1` / `::1` 访问安装向导，并校验随机安装令牌
- 安装过程中只替换 `db` 配置段并强制关闭 `debug`，保留其余配置

界面使用 `public/theme/ui/layui` 中的 layui，安装页样式和脚本位于：

```text
public/theme/install/css/install.css
public/theme/install/js/install.js
```

启动后访问：

```text
http://127.0.0.1:8000/install
```

安装完成后如需重新安装，请先删除 `database/install.lock`。

如需在受信任的内网环境中远程安装，可在 `config/app.php` 的 `install.allowed_ips` 中增加来源 IP。安装令牌会自动写入 `database/install.token` 并在安装完成后删除。

应用启动时会检查 `database/install.lock`：未安装时，`GET` / `HEAD` 请求会跳转到 `/install`，其他请求返回 `503`。可通过 `install.require_lock => false` 关闭该检查。

### 写接口认证

`POST /articles` 默认关闭。设置环境变量 `MICROTHINK_WRITE_TOKEN` 后，请求需携带：

```text
X-Write-Token: <你设置的令牌>
```

服务端使用该令牌校验写权限，并忽略客户端传入的 `user_id`，改为使用 `security.default_user_id`。

## 数据库

默认使用 SQLite：

```text
database/app.sqlite
```

切换到 MySQL 时，在 `config/app.php` 中修改 `db`：

```php
'db' => [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'microthink',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
],
```

MySQL 数据库需要提前创建。

表结构文件：

| 驱动 | 文件 |
|---|---|
| SQLite | `database/schema.sql` |
| MySQL | `database/schema.mysql.sql` |

当前包含 11 张表，按极简原则抽取核心字段：

| 表 | 说明 |
|---|---|
| `user_class` | 用户组 |
| `user` | 用户表 |
| `category` | 栏目表 |
| `article` | 文章主表，含审核状态与静态页标记 |
| `article_data_1` | 文章副表，存放正文等扩展内容 |
| `public` | 站点公共配置，单行记录（`id = 1`） |
| `menu_class` | 后台菜单分组 |
| `menu` | 后台菜单项 |
| `template_class` | 列表模板分类 |
| `template` | 列表模板，存放模板内容、截取字数、每页条数等 |
| `website` | 访问端配置，存放名称、地址、目录、页面模式与开关配置 |

主表 / 副表设计：主表放列表字段与检索字段，副表放正文和低频扩展字段，避免大字段拖慢列表查询。

关系：

```text
user.cid              → user_class.id
category.pid         → category.id
article.user_id            → user.id
article.cid        → category.id
article_data_1.id          → article.id
article_data_1.cid → category.id
menu.cid              → menu_class.id
template.cid          → template_class.id
```

根据当前驱动初始化数据库：

```bash
php database/init.php
```

该脚本会根据 `db.driver` 自动选择驱动和表结构脚本，并写入最小示例数据。
SQLite 下会生成 `database/app.sqlite`，同时会创建 `database/install.lock`，因此初始化完成后应用即可正常访问。

## 运行

使用 PHP 内置服务器：

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

然后访问：

```text
http://127.0.0.1:8000/
```

## Nginx 配置

Nginx 不使用 `public/.htaccess`。部署时需要将网站根目录指向 `public/`，并让不存在的文件全部交给 `index.php` 处理。

示例配置：

```nginx
server {
    listen 80;
    server_name example.com;

    root /var/www/microthink-framework/public;
    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ /\.(?!well-known) {
        deny all;
    }

    location ~ \.php$ {
        try_files $fastcgi_script_name =404;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

注意：

- 将 `root` 改为项目实际部署路径
- 将 `fastcgi_pass` 改为本机 PHP-FPM 地址，例如 `127.0.0.1:9000` 或实际 socket
- 修改后先执行 `nginx -t` 检查配置，再重载 Nginx

## 路由

路由定义在 `route/web.php`：

```php
return [
    'GET /' => [Index::class, 'index'],
    'GET /users/{id}' => [User::class, 'read'],
    'GET /articles' => [Article::class, 'index'],
    'POST /articles' => [Article::class, 'create'],
];
```

当前示例路由：

| 方法 | 路径 | 说明 |
|---|---|---|
| `GET` | `/` | 框架信息 JSON |
| `GET` | `/page` | 原生 PHP 模板页面 |
| `GET` | `/users/{id}` | 用户详情 |
| `GET` | `/articles` | 文章列表 |
| `GET` | `/articles/{id}` | 文章详情 |
| `POST` | `/articles` | 新建文章 |
| `GET` | `/categories` | 分类列表 |
| `GET` | `/thumb/{path*}` | 按需生成图片缩略图 |

## 缩略图

基于 `league/glide` 的按需缩略图系统。源图目录为 `public/uploads`，生成的缓存写入 `public/uploads/.cache`。

URL 格式：

```text
/thumb/{图片相对路径}?参数
```

示例：

```text
/thumb/photo.jpg?w=300&h=200&fit=crop
/thumb/uploads/2024/photo.jpg?w=300&fm=webp
/thumb/photo.jpg?p=small
```

常用参数：`w`（宽）、`h`（高）、`fit`（裁剪模式）、`q`（质量）、`fm`（格式）、`p`（预设）。预设与默认参数在 `config/app.php` 的 `thumb` 配置中定义。

Apache 下由 `public/.htaccess` 将 `/thumb/` 请求重写到入口文件，并禁止直接访问 `.cache` 缓存目录。Nginx 部署时需确保 `/thumb/` 请求同样交给 `index.php` 处理（现有的 `try_files` 配置已覆盖）。

> 注意：缩略图生成依赖 GD 或 Imagick 扩展，请确保已在 `php.ini` 中启用。

## 控制器

控制器通过容器注入 `Request`、`View`；数据库使用 `$think = db()` 获取局部别名。

示例：

```php
final class Article
{
    public function read(int $id): array
    {
        $think = db();

        $rows = (array) $think->query(
            'SELECT * FROM article WHERE id = ?',
            [$id]
        );

        return ['article' => $rows[0] ?? null];
    }
}
```

路由参数 `{id}` 会按方法参数名注入，并根据类型自动转换。

## 数据库操作

`Db` 提供查询与基础事务方法：

```php
query(string $sql, array $bindings = []): array|int|string
beginTransaction(): bool
commit(): bool
rollBack(): bool
```

返回值按结果集判断：有结果集返回 `array`；`INSERT` 返回自增 ID；其余写操作返回影响行数。
| 其他 | `int`，影响行数 |

先获取数据库别名：

```php
$think = db();
```

查询：

```php
$articles = $think->query(
    'SELECT * FROM article WHERE status = ? ORDER BY id DESC LIMIT ?',
    [1, 20]
);
```

新增：

新增主表：

```php
$id = $think->query(
    'INSERT INTO article (user_id, cid, title, smalltext) VALUES (?, ?, ?, ?)',
    [1, 2, 'Hello', 'Summary']
);
```

正文写入副表：

```php
$think->query(
    'INSERT INTO article_data_1 (id, cid, newstext) VALUES (?, ?, ?)',
    [$id, 2, 'Content']
);
```

更新：

```php
$affected = $think->query(
    'UPDATE article SET title = ?, update_time = ? WHERE id = ?',
    ['New title', time(), 1]
);
```

所有参数都通过 PDO 绑定，不支持拼接用户输入。

各表包含 `create_time` 和 `update_time` 两个字段，存储 Unix 时间戳整数（`time()`），写入时由调用方显式赋值。

## 响应约定

控制器返回值会自动归一化：

| 返回值 | 响应 |
|---|---|
| `array` | JSON |
| `string` | HTML |
| `Response` | 原样发送 |
| `null` | 空响应 JSON |
| `int` / `bool` | JSON |

也可以直接返回响应对象：

```php
return Response::json(['ok' => true]);
```

## 配置

配置文件只有 `config/app.php`：

```php
return [
    'debug' => false,

    'db' => [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/../database/app.sqlite',

        // 'driver' => 'mysql',
        // 'host' => '127.0.0.1',
        // 'port' => 3306,
        // 'database' => 'microthink',
        // 'username' => 'root',
        // 'password' => '',
        // 'charset' => 'utf8mb4',
    ],

    'view' => [
        'path' => __DIR__ . '/../app/view',
    ],

    'thumb' => [
        'source' => __DIR__ . '/../public/uploads',
        'cache' => __DIR__ . '/../public/uploads/.cache',
        'max_image_size' => 2000,
        'max_output_size' => 2000,
        'defaults' => [
            'q' => 80,
        ],
        'presets' => [
            'small' => ['w' => 120, 'h' => 120, 'fit' => 'crop'],
            'medium' => ['w' => 320, 'h' => 320, 'fit' => 'crop'],
            'large' => ['w' => 800, 'h' => 800, 'fit' => 'contain'],
        ],
    ],

    'install' => [
        'require_lock' => true,
        'allowed_ips' => ['127.0.0.1', '::1'],
    ],

    'security' => [
        'write_token' => getenv('MICROTHINK_WRITE_TOKEN') ?: '',
        'default_user_id' => 1,
    ],

    'middleware' => [],
];
```

## 助手函数

`app/common.php` 由 Composer 自动加载，提供少量全局助手函数：

| 函数 | 说明 |
|---|---|
| `app()` | 获取当前 `App` 实例 |
| `config('db.driver')` | 读取配置，支持点语法 |
| `request()` | 获取当前 `Request` |
| `db()` | 获取当前 `Db` 实例 |
| `view('index', $vars)` | 渲染原生 PHP 模板 |
| `e($value)` | HTML 转义 |
| `loop($sql, $bindings, $template)` | 灵动标签：查询并循环渲染 |

模板中还可以使用全局 `$think`，它是当前 `Db` 实例的别名：

```php
<?php foreach ($think->query('SELECT id, title FROM article ORDER BY id DESC') as $row): ?>
    <li><?= e($row['title']) ?></li>
<?php endforeach; ?>
```

在控制器类方法中仍推荐使用 `db()` 或构造函数注入 `Db`，因为 PHP 全局变量不会自动进入函数/方法作用域。

示例：

```php
$driver = config('db.driver', 'sqlite');

$articles = db()->query('SELECT * FROM article ORDER BY id DESC');

return view('index', ['articles' => $articles]);
```

模板中输出变量：

```php
<?= e($article['title']) ?>
```

## 灵动标签

模板中可以使用 `[t:loop]...[/t:loop]` 直接写 SQL 并循环输出。

语法：

```text
[t:loop sql="SELECT ..." bind="1,10"]
模板内容
[/t:loop]
```

属性：

| 属性 | 说明 |
|---|---|
| `sql` | 要执行的 SQL，支持 `?` 占位符 |
| `bind` / `bindings` | 参数列表，逗号分隔，数字自动转为整数 |

模板占位符：

| 写法 | 说明 |
|---|---|
| `[!--field--]` | 输出字段并自动 HTML 转义 |
| `[!--!field--]` | 原样输出字段，仅用于可信 HTML |
| `[!--i--]` | 当前序号，从 1 开始 |
| `[!--index--]` | 当前下标，从 0 开始 |
| `[!--total--]` | 总记录数 |

字段占位符采用帝国 CMS 风格的 `[!--字段名--]` 语法，字段名与 SQL 查询结果的列名对应。

示例：

```text
<ul>
[t:loop sql="SELECT id, title FROM article WHERE status = ? ORDER BY id DESC LIMIT ?" bind="1,10"]
<li>[!--i--]. <a href="/articles/[!--id--]">[!--title--]</a></li>
[/t:loop]
</ul>
```

原样输出正文：

```text
[t:loop sql="SELECT d.newstext AS content FROM article_data_1 d WHERE d.id = ?" bind="1"]
<div>[!--!content--]</div>
[/t:loop]
```

标签最终会编译为 `loop()` 调用，底层仍只调用 `Db::query()`，不引入 ORM。

### 嵌套使用

`[t:loop]` 不支持标签自身嵌套，`bind` 也只接受逗号分隔的标量。需要动态绑定时，请直接使用原生 PHP 调用 `db()->query()`。

示例：按分类列出文章。

```text
<?php foreach (db()->query(
    'SELECT id, name FROM category WHERE status = ? ORDER BY id ASC',
    [1]
) as $category): ?>
    <h2><?= e($category['name']) ?></h2>

    <ul>
    <?php foreach (db()->query(
        'SELECT id, title FROM article WHERE cid = ? AND status = ? ORDER BY id DESC',
        [(int) $category['id'], 1]
    ) as $article): ?>
        <li><a href="/articles/<?= (int) $article['id'] ?>"><?= e($article['title']) ?></a></li>
    <?php endforeach; ?>
    </ul>
<?php endforeach; ?>
```

说明：

- 动态绑定请使用原生 PHP + `db()->query()`
- `[t:loop]` 的 `bind` 仅支持逗号分隔的标量，数字会自动转为整数
- 当前版本不支持 `[t:loop]` 标签自身嵌套

## 中间件

中间件是闭包数组，不做中间件类和命名别名：

```php
'middleware' => [
    function ($request, $next) {
        // before

        $response = $next($request);

        // after

        return $response;
    },
],
```

执行顺序：

```text
middleware1 before
middleware2 before
controller
middleware2 after
middleware1 after
```

## 核心类

| 类 | 职责 |
|---|---|
| `App` | 启动、配置、容器、中间件、路由、异常处理 |
| `Container` | 创建对象、依赖注入、方法调用 |
| `Request` | 请求信息读取 |
| `Response` | 响应构造与发送 |
| `Router` | 显式路由匹配 |
| `View` | 原生 PHP 模板渲染 |
| `Db` | SQLite / MySQL 执行器，仅提供 `query()` |

## 设计取舍

保留：

- 单一入口
- 显式数组路由
- 控制器分层
- `Request` / `Response`
- 极简容器
- 原生 PHP 视图
- 闭包中间件
- SQLite / MySQL 双驱动

取消：

- `Model` 封装
- 查询构造器
- ORM 关联
- Facade
- 多应用
- 多连接 / 多库同时访问
- 模板引擎
- 日志、缓存、队列、事件
- Session、Cookie、文件上传
- CLI、注解路由

## 一句话说明

`microthink-framework` 是一个保留 ThinkPHP8 分层和运行流程，但把数据层压缩到只剩 `Db::query()` 的极简 PHP 框架，默认 SQLite，并支持 MySQL。
