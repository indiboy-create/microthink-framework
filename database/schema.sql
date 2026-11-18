-- microthink-framework SQLite schema
-- database file: database/app.sqlite
-- 表结构参考 EmpireCMS dbdoc80，按极简原则抽取核心字段

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS user_class (
    id         INTEGER PRIMARY KEY AUTOINCREMENT, -- 主键ID
    pid        INTEGER NOT NULL DEFAULT 0, -- 父级ID
    title      TEXT    NOT NULL DEFAULT '', -- 标题
    name       TEXT    NOT NULL UNIQUE, -- 标识
    sort       INTEGER NOT NULL DEFAULT 0, -- 排序值
    user_id    INTEGER NOT NULL DEFAULT 0, -- 用户ID
    site_id    INTEGER NOT NULL DEFAULT 0, -- 站点ID
    create_time INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')), -- 创建时间
    update_time INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')) -- 更新时间
);

CREATE TABLE IF NOT EXISTS user (
    id           INTEGER PRIMARY KEY AUTOINCREMENT, -- 主键ID
    cid     INTEGER NOT NULL DEFAULT 1, -- 用户组ID
    username     TEXT    NOT NULL UNIQUE, -- 用户名
    password     TEXT    NOT NULL DEFAULT '', -- 密码
    title      TEXT    NOT NULL DEFAULT '', -- 标题
    name         TEXT    NOT NULL DEFAULT '', -- 标识
    email        TEXT    NOT NULL UNIQUE, -- 邮箱
    disabled     INTEGER NOT NULL DEFAULT 0 CHECK (disabled IN (0, 1)), -- 是否禁用
    loginnum     INTEGER NOT NULL DEFAULT 0, -- 登录次数
    lasttime     INTEGER NOT NULL DEFAULT (strftime('%s', 'now')), -- 最后登录时间
    lastip       TEXT    NOT NULL DEFAULT '', -- 最后登录IP
    addtime      INTEGER NOT NULL DEFAULT (strftime('%s', 'now')), -- 添加时间
    addip        TEXT    NOT NULL DEFAULT '', -- 添加IP
    rnd          TEXT    NOT NULL DEFAULT '', -- 随机码
    salt         TEXT    NOT NULL DEFAULT '', -- 密码盐
    wname        TEXT    NOT NULL DEFAULT '', -- 真实姓名
    tel          TEXT    NOT NULL DEFAULT '', -- 联系电话
    mobile       TEXT    NOT NULL DEFAULT '', -- 手机号
    wxno         TEXT    NOT NULL DEFAULT '', -- 微信号
    qq           TEXT    NOT NULL DEFAULT '', -- QQ号
    user_id    INTEGER NOT NULL DEFAULT 0, -- 用户ID
    site_id    INTEGER NOT NULL DEFAULT 0, -- 站点ID
    create_time   INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')), -- 创建时间
    update_time   INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')), -- 更新时间
    FOREIGN KEY (cid) REFERENCES user_class (id) ON DELETE RESTRICT
);

CREATE INDEX IF NOT EXISTS idx_user_email
    ON user (email);

CREATE INDEX IF NOT EXISTS idx_user_cid
    ON user (cid);

CREATE TABLE IF NOT EXISTS category (
    id          INTEGER PRIMARY KEY AUTOINCREMENT, -- 主键ID
    pid   INTEGER, -- 父级ID
    title      TEXT    NOT NULL DEFAULT '', -- 标题
    name        TEXT    NOT NULL, -- 标识
    slug        TEXT    NOT NULL UNIQUE, -- URL标识
    alias       TEXT    NOT NULL DEFAULT '', -- 别名
    intro       TEXT    NOT NULL DEFAULT '', -- 简介
    keywords    TEXT    NOT NULL DEFAULT '', -- 关键词
    image       TEXT    NOT NULL DEFAULT '', -- 图片
    path        TEXT    NOT NULL DEFAULT '', -- 路径
    islast      INTEGER NOT NULL DEFAULT 1 CHECK (islast IN (0, 1)), -- 是否末级
    page_size   INTEGER NOT NULL DEFAULT 20, -- 每页条数
    sort        INTEGER NOT NULL DEFAULT 0, -- 排序值
    checked     INTEGER NOT NULL DEFAULT 1 CHECK (checked IN (0, 1)), -- 是否审核
    show_nav    INTEGER NOT NULL DEFAULT 1 CHECK (show_nav IN (0, 1)), -- 是否显示导航
    status      INTEGER NOT NULL DEFAULT 1 CHECK (status IN (0, 1)), -- 状态
    addtime     INTEGER NOT NULL DEFAULT (strftime('%s', 'now')), -- 添加时间
    user_id    INTEGER NOT NULL DEFAULT 0, -- 用户ID
    site_id    INTEGER NOT NULL DEFAULT 0, -- 站点ID
    create_time  INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')), -- 创建时间
    update_time  INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')), -- 更新时间
    FOREIGN KEY (pid) REFERENCES category (id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_category_parent
    ON category (pid);

CREATE INDEX IF NOT EXISTS idx_category_status_sort
    ON category (status, sort);

CREATE TABLE IF NOT EXISTS article (
    id          INTEGER PRIMARY KEY AUTOINCREMENT, -- 主键ID
    cid INTEGER NOT NULL DEFAULT 1, -- 分类ID
    title       TEXT    NOT NULL, -- 标题
    titleurl    TEXT    NOT NULL DEFAULT '', -- 标题链接
    titlepic    TEXT    NOT NULL DEFAULT '', -- 标题图片
    smalltext   TEXT    NOT NULL DEFAULT '', -- 摘要
    keywords    TEXT    NOT NULL DEFAULT '', -- 关键词
    onclick     INTEGER NOT NULL DEFAULT 0, -- 点击数
    plnum       INTEGER NOT NULL DEFAULT 0, -- 评论数
    firsttitle  INTEGER NOT NULL DEFAULT 0, -- 首页推荐
    isgood      INTEGER NOT NULL DEFAULT 0, -- 推荐
    ispic       INTEGER NOT NULL DEFAULT 0, -- 含图片
    istop       INTEGER NOT NULL DEFAULT 0, -- 置顶
    isqf        INTEGER NOT NULL DEFAULT 0, -- 全面推荐
    ismember    INTEGER NOT NULL DEFAULT 0, -- 会员可见
    isurl       INTEGER NOT NULL DEFAULT 0, -- 外部链接
    status      INTEGER NOT NULL DEFAULT 1 CHECK (status IN (0, 1)), -- 状态
    checked     INTEGER NOT NULL DEFAULT 0 CHECK (checked IN (0, 1)), -- 是否审核
    havehtml    INTEGER NOT NULL DEFAULT 0 CHECK (havehtml IN (0, 1)), -- 已生成静态页
    truetime    INTEGER NOT NULL DEFAULT (strftime('%s', 'now')), -- 录入时间
    lastdotime  INTEGER NOT NULL DEFAULT (strftime('%s', 'now')), -- 最后修改时间
    newstime    INTEGER NOT NULL DEFAULT (strftime('%s', 'now')), -- 发布时间
    user_id     INTEGER NOT NULL, -- 用户ID
    site_id    INTEGER NOT NULL DEFAULT 0, -- 站点ID
    create_time  INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')), -- 创建时间
    update_time  INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')), -- 更新时间
    FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE,
    FOREIGN KEY (cid) REFERENCES category (id) ON DELETE RESTRICT
);

CREATE INDEX IF NOT EXISTS idx_article_user_id
    ON article (user_id);

CREATE INDEX IF NOT EXISTS idx_article_cid
    ON article (cid);

CREATE INDEX IF NOT EXISTS idx_article_status_newstime
    ON article (status, newstime DESC);

CREATE TABLE IF NOT EXISTS article_data_1 (
    id          INTEGER PRIMARY KEY, -- 主键ID
    cid INTEGER NOT NULL, -- 分类ID
    keyid       TEXT    NOT NULL DEFAULT '', -- 关键字ID
    dokey       INTEGER NOT NULL DEFAULT 0 CHECK (dokey IN (0, 1)), -- 处理关键字
    newstempid  INTEGER NOT NULL DEFAULT 0, -- 内容模板ID
    closepl     INTEGER NOT NULL DEFAULT 0 CHECK (closepl IN (0, 1)), -- 关闭评论
    haveaddfen  INTEGER NOT NULL DEFAULT 0 CHECK (haveaddfen IN (0, 1)), -- 已加积分
    infotags    TEXT    NOT NULL DEFAULT '', -- 信息标签
    efzstb      TEXT    NOT NULL DEFAULT '', -- 副标题
    writer      TEXT    NOT NULL DEFAULT '', -- 作者
    befrom      TEXT    NOT NULL DEFAULT '', -- 来源
    newstext    TEXT    NOT NULL DEFAULT '', -- 正文内容
    user_id    INTEGER NOT NULL DEFAULT 0, -- 用户ID
    site_id    INTEGER NOT NULL DEFAULT 0, -- 站点ID
    create_time INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')), -- 创建时间
    update_time INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')), -- 更新时间
    FOREIGN KEY (id) REFERENCES article (id) ON DELETE CASCADE,
    FOREIGN KEY (cid) REFERENCES category (id) ON DELETE RESTRICT
);

CREATE INDEX IF NOT EXISTS idx_article_data_category
    ON article_data_1 (cid);

CREATE TABLE IF NOT EXISTS public (
    id               INTEGER PRIMARY KEY CHECK (id = 1), -- 主键ID
    site_name        TEXT    NOT NULL DEFAULT 'microthink-framework', -- 站点名称
    site_url         TEXT    NOT NULL DEFAULT '', -- 站点地址
    admin_email      TEXT    NOT NULL DEFAULT '', -- 管理员邮箱
    site_keywords    TEXT    NOT NULL DEFAULT '', -- 站点关键词
    site_intro       TEXT    NOT NULL DEFAULT '', -- 站点简介
    search_min_len   INTEGER NOT NULL DEFAULT 1, -- 搜索最小长度
    search_max_len   INTEGER NOT NULL DEFAULT 20, -- 搜索最大长度
    search_num       INTEGER NOT NULL DEFAULT 10, -- 搜索结果数
    smalltext_len    INTEGER NOT NULL DEFAULT 255, -- 摘要截取长度
    upload_filetype  TEXT    NOT NULL DEFAULT '', -- 允许上传类型
    upload_filesize  INTEGER NOT NULL DEFAULT 2048, -- 上传大小限制
    register_ok      INTEGER NOT NULL DEFAULT 1 CHECK (register_ok IN (0, 1)), -- 允许注册
    addnews_ok       INTEGER NOT NULL DEFAULT 1 CHECK (addnews_ok IN (0, 1)), -- 允许投稿
    user_id    INTEGER NOT NULL DEFAULT 0, -- 用户ID
    site_id    INTEGER NOT NULL DEFAULT 0, -- 站点ID
    create_time      INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')), -- 创建时间
    update_time      INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')) -- 更新时间
);

CREATE TABLE IF NOT EXISTS menu_class (
    id       INTEGER PRIMARY KEY AUTOINCREMENT, -- 主键ID
    pid        INTEGER NOT NULL DEFAULT 0, -- 父级ID
    title      TEXT    NOT NULL DEFAULT '', -- 标题
    name     TEXT    NOT NULL UNIQUE, -- 标识
    issys    INTEGER NOT NULL DEFAULT 0 CHECK (issys IN (0, 1)), -- 是否系统内置
    sort     INTEGER NOT NULL DEFAULT 0, -- 排序值
    type     INTEGER NOT NULL DEFAULT 3, -- 类型
    groupids TEXT    NOT NULL DEFAULT '', -- 用户组ID列表
    user_id    INTEGER NOT NULL DEFAULT 0, -- 用户ID
    site_id    INTEGER NOT NULL DEFAULT 0, -- 站点ID
    create_time INTEGER NOT NULL DEFAULT (strftime('%s', 'now')), -- 创建时间
    update_time INTEGER NOT NULL DEFAULT (strftime('%s', 'now')) -- 更新时间
);

CREATE TABLE IF NOT EXISTS menu (
    id       INTEGER PRIMARY KEY AUTOINCREMENT, -- 主键ID
    cid INTEGER NOT NULL, -- 菜单分组ID
    title      TEXT    NOT NULL DEFAULT '', -- 标题
    name     TEXT    NOT NULL, -- 标识
    url      TEXT    NOT NULL, -- 链接地址
    sort     INTEGER NOT NULL DEFAULT 0, -- 排序值
    addhash  INTEGER NOT NULL DEFAULT 0, -- 附加锚点
    user_id    INTEGER NOT NULL DEFAULT 0, -- 用户ID
    site_id    INTEGER NOT NULL DEFAULT 0, -- 站点ID
    create_time INTEGER NOT NULL DEFAULT (strftime('%s', 'now')), -- 创建时间
    update_time INTEGER NOT NULL DEFAULT (strftime('%s', 'now')), -- 更新时间
    FOREIGN KEY (cid) REFERENCES menu_class (id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_menu_class
    ON menu (cid, sort);

CREATE TABLE IF NOT EXISTS template_class (
    id         INTEGER PRIMARY KEY AUTOINCREMENT, -- 主键ID
    pid        INTEGER NOT NULL DEFAULT 0, -- 父级ID
    title      TEXT    NOT NULL DEFAULT '', -- 标题
    name       TEXT    NOT NULL UNIQUE, -- 标识
    sort       INTEGER NOT NULL DEFAULT 0, -- 排序值
    user_id    INTEGER NOT NULL DEFAULT 0, -- 用户ID
    site_id    INTEGER NOT NULL DEFAULT 0, -- 站点ID
    create_time INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')), -- 创建时间
    update_time INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')) -- 更新时间
);

CREATE TABLE IF NOT EXISTS template (
    id         INTEGER PRIMARY KEY AUTOINCREMENT, -- 主键ID
    cid   INTEGER NOT NULL DEFAULT 1, -- 模板分类ID
    title      TEXT    NOT NULL DEFAULT '', -- 标题
    name       TEXT    NOT NULL, -- 标识
    content    TEXT    NOT NULL DEFAULT '', -- 模板内容
    listvar    TEXT    NOT NULL DEFAULT '', -- 列表变量
    subnews    INTEGER NOT NULL DEFAULT 0, -- 摘要截取字数
    subtitle   INTEGER NOT NULL DEFAULT 0, -- 标题截取字数
    rownum     INTEGER NOT NULL DEFAULT 10, -- 显示条数
    modid      INTEGER NOT NULL DEFAULT 1, -- 模型ID
    showdate   TEXT    NOT NULL DEFAULT 'Y-m-d', -- 时间格式
    isdefault  INTEGER NOT NULL DEFAULT 0 CHECK (isdefault IN (0, 1)), -- 是否默认
    docode     INTEGER NOT NULL DEFAULT 0 CHECK (docode IN (0, 1)), -- 使用程序代码
    user_id    INTEGER NOT NULL DEFAULT 0, -- 用户ID
    site_id    INTEGER NOT NULL DEFAULT 0, -- 站点ID
    create_time INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')), -- 创建时间
    update_time INTEGER    NOT NULL DEFAULT (strftime('%s', 'now')), -- 更新时间
    FOREIGN KEY (cid) REFERENCES template_class (id) ON DELETE RESTRICT
);

CREATE INDEX IF NOT EXISTS idx_template_class
    ON template (cid, isdefault);

CREATE TABLE IF NOT EXISTS website (
    id            INTEGER PRIMARY KEY AUTOINCREMENT, -- 主键ID
    title      TEXT    NOT NULL DEFAULT '', -- 标题
    name          TEXT    NOT NULL, -- 标识
    url           TEXT    NOT NULL DEFAULT '', -- 链接地址
    path          TEXT    NOT NULL DEFAULT '', -- 路径
    temp_group_id INTEGER NOT NULL DEFAULT 0, -- 模板组ID
    page_mode     INTEGER NOT NULL DEFAULT 0 CHECK (page_mode IN (0, 1)), -- 页面模式
    is_close      INTEGER NOT NULL DEFAULT 0 CHECK (is_close IN (0, 1)), -- 是否关闭
    close_add     INTEGER NOT NULL DEFAULT 0 CHECK (close_add IN (0, 1)), -- 关闭投稿
    open_admin    INTEGER NOT NULL DEFAULT 0 CHECK (open_admin IN (0, 1)), -- 开放后台
    rehtml        INTEGER NOT NULL DEFAULT 0, -- 同步静态页
    user_id       INTEGER NOT NULL DEFAULT 0, -- 用户ID
    site_id       INTEGER NOT NULL DEFAULT 0, -- 站点ID
    create_time   INTEGER NOT NULL DEFAULT (strftime('%s', 'now')), -- 创建时间
    update_time   INTEGER NOT NULL DEFAULT (strftime('%s', 'now')) -- 更新时间
);

INSERT OR IGNORE INTO user_class (id, title, name, sort)
VALUES (1, '默认部门', 'default', 0);

INSERT OR IGNORE INTO user (id, username, password, title, name, email, cid)
VALUES (1, 'admin', '', '管理员', 'admin', 'admin@example.com', 1);

INSERT OR IGNORE INTO category (id, pid, title, name, slug, alias, intro, page_size, sort, checked, show_nav, status)
VALUES
    (1, NULL, '默认分类', 'default', 'default', 'default', '默认分类', 20, 0, 1, 1, 1),
    (2, NULL, '技术', 'tech', 'tech', 'tech', '技术文章', 20, 1, 1, 1, 1);

INSERT OR IGNORE INTO article (id, user_id, cid, title, titleurl, smalltext, keywords, status, checked)
VALUES (1, 1, 2, 'Hello microthink-framework', '/articles/1', 'The first article.', 'mini,article', 1, 1);

INSERT OR IGNORE INTO article_data_1 (id, cid, newstext, writer, befrom, infotags)
VALUES (1, 2, 'The first article.', 'admin', 'microthink', 'mini,article');

INSERT OR IGNORE INTO public (id, site_name, site_url, admin_email, site_keywords, site_intro)
VALUES (1, 'microthink-framework', '', 'admin@example.com', 'mini,framework', 'A minimal PHP framework');

INSERT OR IGNORE INTO menu_class (id, title, name, issys, sort, type, groupids)
VALUES
    (1, '常用操作', 'common', 0, 0, 1, ''),
    (2, '扩展菜单', 'ext', 0, 1, 3, '');

INSERT OR IGNORE INTO menu (id, cid, title, name, url, sort, addhash)
VALUES
    (1, 1, '首页', 'home', '/', 1, 0),
    (2, 1, '文章列表', 'articles', '/articles', 2, 0),
    (3, 1, '分类列表', 'categories', '/categories', 3, 0);

INSERT OR IGNORE INTO template_class (id, title, name, sort)
VALUES (1, '默认模板', 'default', 0);

INSERT OR IGNORE INTO template (id, cid, title, name, content, listvar, subnews, subtitle, rownum, modid, showdate, isdefault, docode)
VALUES (1, 1, '默认列表模板', 'default', '<ul>[t:loop sql="SELECT id, title FROM article ORDER BY id DESC LIMIT ?" bind="10"]<li>[!--title--]</li>[/t:loop]</ul>', '', 120, 80, 10, 1, 'Y-m-d', 1, 1);

INSERT OR IGNORE INTO website (id, title, name, url, path, temp_group_id, page_mode, is_close, close_add, open_admin, rehtml)
VALUES (1, '主站', 'main', '', '', 1, 0, 0, 0, 1, 0);
