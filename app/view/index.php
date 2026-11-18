<?php
if (!isset($articles) || !is_array($articles)) {
    $articles = [];
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>Articles</title>
</head>
<body>
<h1>Articles</h1>

<ul>
<?php foreach ($articles as $article): ?>
    <li>
        #<?= (int) $article['id'] ?>
        <?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?>
    </li>
<?php endforeach; ?>
</ul>

<h2>Categories</h2>
<ul>
[t:loop sql="SELECT id, name FROM category WHERE status = ? ORDER BY id ASC" bind="1"]
<li>[!--i--]. [!--name--]</li>
[/t:loop]
</ul>
</body>
</html>
