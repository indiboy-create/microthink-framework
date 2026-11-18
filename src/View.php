<?php

declare(strict_types=1);

namespace mini;

use RuntimeException;
use Throwable;

final class View
{
    public function __construct(private string $path)
    {
    }

    public function render(string $template, array $vars = []): string
    {
        $file = rtrim($this->path, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $template)
            . '.php';

        if (!is_file($file)) {
            throw new RuntimeException('模板不存在：' . $file);
        }

        $source = file_get_contents($file);

        if ($source === false) {
            throw new RuntimeException('模板读取失败：' . $file);
        }

        $compiled = $this->compileTags($source);
        $cacheFile = $this->compiledFile($file, $compiled);

        extract($vars, EXTR_SKIP);

        ob_start();

        try {
            include $cacheFile;
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }

    private function compiledFile(string $file, string $compiled): string
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'microthink-views';

        if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) {
            throw new RuntimeException('无法创建模板编译目录：' . $dir);
        }

        $cacheFile = $dir . DIRECTORY_SEPARATOR . sha1($file . $compiled) . '.php';

        if (!is_file($cacheFile) && file_put_contents($cacheFile, $compiled, LOCK_EX) === false) {
            throw new RuntimeException('无法写入模板编译文件：' . $cacheFile);
        }

        return $cacheFile;
    }

    private function compileTags(string $source): string
    {
        return preg_replace_callback(
            '/\[t:loop\s+sql="(.*?)"(?:\s+bind(?:ings)?="(.*?)")?\s*\](.*?)\[\/t:loop\]/s',
            static function (array $matches): string {
                $sql = $matches[1];
                $rawBindings = $matches[2] ?? '';
                $body = $matches[3];
                $bindings = [];

                if (trim($rawBindings) !== '') {
                    foreach (explode(',', $rawBindings) as $value) {
                        $value = trim($value);
                        $bindings[] = is_numeric($value) ? (int) $value : $value;
                    }
                }

                return '<?= loop('
                    . var_export($sql, true) . ', '
                    . var_export($bindings, true) . ', '
                    . var_export($body, true)
                    . ') ?>';
            },
            $source
        ) ?? $source;
    }
}
