-- microthink-framework MySQL schema
-- database: microthink
-- 表结构参考 EmpireCMS dbdoc80，按极简原则抽取核心字段

CREATE TABLE IF NOT EXISTS `user_class` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, -- 主键ID
    `pid` INT UNSIGNED NOT NULL DEFAULT 0, -- 父级ID
    `title` VARCHAR(255) NOT NULL DEFAULT '', -- 标题
    `name` VARCHAR(100) NOT NULL, -- 标识
    `sort` INT NOT NULL DEFAULT 0, -- 排序值
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 用户ID
    `site_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 站点ID
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 创建时间
    `update_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 更新时间
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_class_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, -- 主键ID
    `cid` INT UNSIGNED NOT NULL DEFAULT 1, -- 用户组ID
    `username` VARCHAR(100) NOT NULL, -- 用户名
    `password` VARCHAR(255) NOT NULL DEFAULT '', -- 密码
    `title` VARCHAR(255) NOT NULL DEFAULT '', -- 标题
    `name` VARCHAR(100) NOT NULL DEFAULT '', -- 标识
    `email` VARCHAR(255) NOT NULL, -- 邮箱
    `disabled` TINYINT NOT NULL DEFAULT 0 CHECK (`disabled` IN (0, 1)), -- 是否禁用
    `loginnum` INT UNSIGNED NOT NULL DEFAULT 0, -- 登录次数
    `lasttime` INT UNSIGNED NOT NULL DEFAULT 0, -- 最后登录时间
    `lastip` VARCHAR(50) NOT NULL DEFAULT '', -- 最后登录IP
    `addtime` INT UNSIGNED NOT NULL DEFAULT 0, -- 添加时间
    `addip` VARCHAR(50) NOT NULL DEFAULT '', -- 添加IP
    `rnd` VARCHAR(50) NOT NULL DEFAULT '', -- 随机码
    `salt` VARCHAR(50) NOT NULL DEFAULT '', -- 密码盐
    `wname` VARCHAR(100) NOT NULL DEFAULT '', -- 真实姓名
    `tel` VARCHAR(50) NOT NULL DEFAULT '', -- 联系电话
    `mobile` VARCHAR(50) NOT NULL DEFAULT '', -- 手机号
    `wxno` VARCHAR(50) NOT NULL DEFAULT '', -- 微信号
    `qq` VARCHAR(50) NOT NULL DEFAULT '', -- QQ号
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 用户ID
    `site_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 站点ID
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 创建时间
    `update_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 更新时间
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_username` (`username`),
    UNIQUE KEY `uk_user_email` (`email`),
    KEY `idx_user_cid` (`cid`),
    CONSTRAINT `fk_user_cid` FOREIGN KEY (`cid`) REFERENCES `user_class` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `category` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, -- 主键ID
    `pid` INT UNSIGNED DEFAULT NULL, -- 父级ID
    `title` VARCHAR(255) NOT NULL DEFAULT '', -- 标题
    `name` VARCHAR(100) NOT NULL, -- 标识
    `slug` VARCHAR(100) NOT NULL, -- URL标识
    `alias` VARCHAR(100) NOT NULL DEFAULT '', -- 别名
    `intro` VARCHAR(500) NOT NULL DEFAULT '', -- 简介
    `keywords` VARCHAR(255) NOT NULL DEFAULT '', -- 关键词
    `image` VARCHAR(255) NOT NULL DEFAULT '', -- 图片
    `path` VARCHAR(255) NOT NULL DEFAULT '', -- 路径
    `islast` TINYINT NOT NULL DEFAULT 1 CHECK (`islast` IN (0, 1)), -- 是否末级
    `page_size` INT NOT NULL DEFAULT 20, -- 每页条数
    `sort` INT NOT NULL DEFAULT 0, -- 排序值
    `checked` TINYINT NOT NULL DEFAULT 1 CHECK (`checked` IN (0, 1)), -- 是否审核
    `show_nav` TINYINT NOT NULL DEFAULT 1 CHECK (`show_nav` IN (0, 1)), -- 是否显示导航
    `status` TINYINT NOT NULL DEFAULT 1 CHECK (`status` IN (0, 1)), -- 状态
    `addtime` INT UNSIGNED NOT NULL DEFAULT 0, -- 添加时间
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 用户ID
    `site_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 站点ID
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 创建时间
    `update_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 更新时间
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_category_slug` (`slug`),
    KEY `idx_category_parent` (`pid`),
    KEY `idx_category_status_sort` (`status`, `sort`),
    CONSTRAINT `fk_category_parent` FOREIGN KEY (`pid`) REFERENCES `category` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `article` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, -- 主键ID
    `cid` INT UNSIGNED NOT NULL DEFAULT 1, -- 分类ID
    `title` VARCHAR(255) NOT NULL, -- 标题
    `titleurl` VARCHAR(255) NOT NULL DEFAULT '', -- 标题链接
    `titlepic` VARCHAR(255) NOT NULL DEFAULT '', -- 标题图片
    `smalltext` VARCHAR(500) NOT NULL DEFAULT '', -- 摘要
    `keywords` VARCHAR(255) NOT NULL DEFAULT '', -- 关键词
    `onclick` INT UNSIGNED NOT NULL DEFAULT 0, -- 点击数
    `plnum` INT UNSIGNED NOT NULL DEFAULT 0, -- 评论数
    `firsttitle` TINYINT NOT NULL DEFAULT 0, -- 首页推荐
    `isgood` TINYINT NOT NULL DEFAULT 0, -- 推荐
    `ispic` TINYINT NOT NULL DEFAULT 0, -- 含图片
    `istop` TINYINT NOT NULL DEFAULT 0, -- 置顶
    `isqf` TINYINT NOT NULL DEFAULT 0, -- 全面推荐
    `ismember` TINYINT NOT NULL DEFAULT 0, -- 会员可见
    `isurl` TINYINT NOT NULL DEFAULT 0, -- 外部链接
    `status` TINYINT NOT NULL DEFAULT 1 CHECK (`status` IN (0, 1)), -- 状态
    `checked` TINYINT NOT NULL DEFAULT 0 CHECK (`checked` IN (0, 1)), -- 是否审核
    `havehtml` TINYINT NOT NULL DEFAULT 0 CHECK (`havehtml` IN (0, 1)), -- 已生成静态页
    `truetime` INT UNSIGNED NOT NULL DEFAULT 0, -- 录入时间
    `lastdotime` INT UNSIGNED NOT NULL DEFAULT 0, -- 最后修改时间
    `newstime` INT UNSIGNED NOT NULL DEFAULT 0, -- 发布时间
    `user_id` INT UNSIGNED NOT NULL, -- 用户ID
    `site_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 站点ID
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 创建时间
    `update_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 更新时间
    PRIMARY KEY (`id`),
    KEY `idx_article_user_id` (`user_id`),
    KEY `idx_article_cid` (`cid`),
    KEY `idx_article_status_newstime` (`status`, `newstime`),
    CONSTRAINT `fk_article_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_article_category` FOREIGN KEY (`cid`) REFERENCES `category` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `article_data_1` (
    `id` INT UNSIGNED NOT NULL, -- 主键ID
    `cid` INT UNSIGNED NOT NULL, -- 分类ID
    `keyid` VARCHAR(255) NOT NULL DEFAULT '', -- 关键字ID
    `dokey` TINYINT NOT NULL DEFAULT 0 CHECK (`dokey` IN (0, 1)), -- 处理关键字
    `newstempid` INT UNSIGNED NOT NULL DEFAULT 0, -- 内容模板ID
    `closepl` TINYINT NOT NULL DEFAULT 0 CHECK (`closepl` IN (0, 1)), -- 关闭评论
    `haveaddfen` TINYINT NOT NULL DEFAULT 0 CHECK (`haveaddfen` IN (0, 1)), -- 已加积分
    `infotags` VARCHAR(255) NOT NULL DEFAULT '', -- 信息标签
    `efzstb` VARCHAR(50) NOT NULL DEFAULT '', -- 副标题
    `writer` VARCHAR(50) NOT NULL DEFAULT '', -- 作者
    `befrom` VARCHAR(100) NOT NULL DEFAULT '', -- 来源
    `newstext` TEXT, -- 正文内容
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 用户ID
    `site_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 站点ID
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 创建时间
    `update_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 更新时间
    PRIMARY KEY (`id`),
    KEY `idx_article_data_category` (`cid`),
    CONSTRAINT `fk_article_data_article` FOREIGN KEY (`id`) REFERENCES `article` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_article_data_category` FOREIGN KEY (`cid`) REFERENCES `category` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `public` (
    `id` TINYINT UNSIGNED NOT NULL CHECK (`id` = 1), -- 主键ID
    `site_name` VARCHAR(100) NOT NULL DEFAULT 'microthink-framework', -- 站点名称
    `site_url` VARCHAR(255) NOT NULL DEFAULT '', -- 站点地址
    `admin_email` VARCHAR(255) NOT NULL DEFAULT '', -- 管理员邮箱
    `site_keywords` VARCHAR(255) NOT NULL DEFAULT '', -- 站点关键词
    `site_intro` VARCHAR(500) NOT NULL DEFAULT '', -- 站点简介
    `search_min_len` INT NOT NULL DEFAULT 1, -- 搜索最小长度
    `search_max_len` INT NOT NULL DEFAULT 20, -- 搜索最大长度
    `search_num` INT NOT NULL DEFAULT 10, -- 搜索结果数
    `smalltext_len` INT NOT NULL DEFAULT 255, -- 摘要截取长度
    `upload_filetype` VARCHAR(255) NOT NULL DEFAULT '', -- 允许上传类型
    `upload_filesize` INT UNSIGNED NOT NULL DEFAULT 2048, -- 上传大小限制
    `register_ok` TINYINT NOT NULL DEFAULT 1 CHECK (`register_ok` IN (0, 1)), -- 允许注册
    `addnews_ok` TINYINT NOT NULL DEFAULT 1 CHECK (`addnews_ok` IN (0, 1)), -- 允许投稿
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 用户ID
    `site_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 站点ID
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 创建时间
    `update_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 更新时间
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `menu_class` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, -- 主键ID
    `pid` INT UNSIGNED NOT NULL DEFAULT 0, -- 父级ID
    `title` VARCHAR(255) NOT NULL DEFAULT '', -- 标题
    `name` VARCHAR(100) NOT NULL, -- 标识
    `issys` TINYINT NOT NULL DEFAULT 0 CHECK (`issys` IN (0, 1)), -- 是否系统内置
    `sort` INT NOT NULL DEFAULT 0, -- 排序值
    `type` INT NOT NULL DEFAULT 3, -- 类型
    `groupids` VARCHAR(255) NOT NULL DEFAULT '', -- 用户组ID列表
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 用户ID
    `site_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 站点ID
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 创建时间
    `update_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 更新时间
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_menu_class_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `menu` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, -- 主键ID
    `cid` INT UNSIGNED NOT NULL, -- 菜单分组ID
    `title` VARCHAR(255) NOT NULL DEFAULT '', -- 标题
    `name` VARCHAR(100) NOT NULL, -- 标识
    `url` VARCHAR(500) NOT NULL, -- 链接地址
    `sort` INT NOT NULL DEFAULT 0, -- 排序值
    `addhash` TINYINT NOT NULL DEFAULT 0 CHECK (`addhash` IN (0, 1)), -- 附加锚点
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 用户ID
    `site_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 站点ID
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 创建时间
    `update_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 更新时间
    PRIMARY KEY (`id`),
    KEY `idx_menu_class` (`cid`, `sort`),
    CONSTRAINT `fk_menu_class` FOREIGN KEY (`cid`) REFERENCES `menu_class` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `template_class` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, -- 主键ID
    `pid` INT UNSIGNED NOT NULL DEFAULT 0, -- 父级ID
    `title` VARCHAR(255) NOT NULL DEFAULT '', -- 标题
    `name` VARCHAR(100) NOT NULL, -- 标识
    `sort` INT NOT NULL DEFAULT 0, -- 排序值
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 用户ID
    `site_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 站点ID
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 创建时间
    `update_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 更新时间
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_template_class_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `template` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, -- 主键ID
    `cid` INT UNSIGNED NOT NULL DEFAULT 1, -- 模板分类ID
    `title` VARCHAR(255) NOT NULL DEFAULT '', -- 标题
    `name` VARCHAR(100) NOT NULL, -- 标识
    `content` MEDIUMTEXT, -- 模板内容
    `listvar` TEXT, -- 列表变量
    `subnews` INT NOT NULL DEFAULT 0, -- 摘要截取字数
    `subtitle` INT NOT NULL DEFAULT 0, -- 标题截取字数
    `rownum` INT NOT NULL DEFAULT 10, -- 显示条数
    `modid` INT UNSIGNED NOT NULL DEFAULT 1, -- 模型ID
    `showdate` VARCHAR(50) NOT NULL DEFAULT 'Y-m-d', -- 时间格式
    `isdefault` TINYINT NOT NULL DEFAULT 0 CHECK (`isdefault` IN (0, 1)), -- 是否默认
    `docode` TINYINT NOT NULL DEFAULT 0 CHECK (`docode` IN (0, 1)), -- 使用程序代码
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 用户ID
    `site_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 站点ID
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 创建时间
    `update_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 更新时间
    PRIMARY KEY (`id`),
    KEY `idx_template_class` (`cid`, `isdefault`),
    CONSTRAINT `fk_template_class` FOREIGN KEY (`cid`) REFERENCES `template_class` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `website` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, -- 主键ID
    `title` VARCHAR(255) NOT NULL DEFAULT '', -- 标题
    `name` VARCHAR(100) NOT NULL, -- 标识
    `url` VARCHAR(255) NOT NULL DEFAULT '', -- 链接地址
    `path` VARCHAR(255) NOT NULL DEFAULT '', -- 路径
    `temp_group_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 模板组ID
    `page_mode` TINYINT NOT NULL DEFAULT 0 CHECK (`page_mode` IN (0, 1)), -- 页面模式
    `is_close` TINYINT NOT NULL DEFAULT 0 CHECK (`is_close` IN (0, 1)), -- 是否关闭
    `close_add` TINYINT NOT NULL DEFAULT 0 CHECK (`close_add` IN (0, 1)), -- 关闭投稿
    `open_admin` TINYINT NOT NULL DEFAULT 0 CHECK (`open_admin` IN (0, 1)), -- 开放后台
    `rehtml` TINYINT NOT NULL DEFAULT 0, -- 同步静态页
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 用户ID
    `site_id` INT UNSIGNED NOT NULL DEFAULT 0, -- 站点ID
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 创建时间
    `update_time` INT UNSIGNED NOT NULL DEFAULT 0, -- 更新时间
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `user_class` (`id`, `name`, `title`, `sort`)
VALUES (1, 'default', '默认部门', 0);

INSERT IGNORE INTO `user` (`id`, `username`, `password`, `name`, `title`, `email`, `cid`)
VALUES (1, 'admin', '', 'admin', '管理员', 'admin@example.com', 1);

INSERT IGNORE INTO `category` (`id`, `pid`, `name`, `title`, `slug`, `alias`, `intro`, `page_size`, `sort`, `checked`, `show_nav`, `status`)
VALUES
    (1, NULL, 'default', '默认分类', 'default', 'default', '默认分类', 20, 0, 1, 1, 1),
    (2, NULL, 'tech', '技术', 'tech', 'tech', '技术文章', 20, 1, 1, 1, 1);

INSERT IGNORE INTO `article` (`id`, `user_id`, `cid`, `title`, `titleurl`, `smalltext`, `keywords`, `status`, `checked`)
VALUES (1, 1, 2, 'Hello microthink-framework', '/articles/1', 'The first article.', 'mini,article', 1, 1);

INSERT IGNORE INTO `article_data_1` (`id`, `cid`, `newstext`, `writer`, `befrom`, `infotags`)
VALUES (1, 2, 'The first article.', 'admin', 'microthink', 'mini,article');

INSERT IGNORE INTO `public` (`id`, `site_name`, `site_url`, `admin_email`, `site_keywords`, `site_intro`)
VALUES (1, 'microthink-framework', '', 'admin@example.com', 'mini,framework', 'A minimal PHP framework');

INSERT IGNORE INTO `menu_class` (`id`, `name`, `title`, `issys`, `sort`, `type`, `groupids`)
VALUES
    (1, 'common', '常用操作', 0, 0, 1, ''),
    (2, 'ext', '扩展菜单', 0, 1, 3, '');

INSERT IGNORE INTO `menu` (`id`, `cid`, `name`, `title`, `url`, `sort`, `addhash`)
VALUES
    (1, 1, 'home', '首页', '/', 1, 0),
    (2, 1, 'articles', '文章列表', '/articles', 2, 0),
    (3, 1, 'categories', '分类列表', '/categories', 3, 0);

INSERT IGNORE INTO `template_class` (`id`, `name`, `title`, `sort`)
VALUES (1, 'default', '默认模板', 0);

INSERT IGNORE INTO `template` (`id`, `cid`, `name`, `title`, `content`, `listvar`, `subnews`, `subtitle`, `rownum`, `modid`, `showdate`, `isdefault`, `docode`)
VALUES (1, 1, 'default', '默认列表模板', '<ul>[t:loop sql="SELECT id, title FROM article ORDER BY id DESC LIMIT ?" bind="10"]<li>[!--title--]</li>[/t:loop]</ul>', '', 120, 80, 10, 1, 'Y-m-d', 1, 1);

INSERT IGNORE INTO `website` (`id`, `name`, `title`, `url`, `path`, `temp_group_id`, `page_mode`, `is_close`, `close_add`, `open_admin`, `rehtml`)
VALUES (1, 'main', '主站', '', '', 1, 0, 0, 0, 1, 0);
