<?php

declare(strict_types=1);

namespace mini;

use InvalidArgumentException;
use PDO;

final class Db
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $driver = strtolower((string) ($config['driver'] ?? 'sqlite'));

        if (!in_array($driver, ['sqlite', 'mysql'], true)) {
            throw new InvalidArgumentException('不支持的数据库驱动：' . $driver);
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        if ($driver === 'mysql') {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                (string) ($config['host'] ?? '127.0.0.1'),
                (int) ($config['port'] ?? 3306),
                (string) ($config['database'] ?? ''),
                (string) ($config['charset'] ?? 'utf8mb4')
            );

            $options[PDO::ATTR_EMULATE_PREPARES] = false;
        } else {
            $dsn = 'sqlite:' . (string) ($config['database'] ?? '');
        }

        $this->pdo = new PDO(
            $dsn,
            $config['username'] ?? null,
            $config['password'] ?? null,
            $options
        );

        if ($driver === 'sqlite') {
            $this->pdo->exec('PRAGMA foreign_keys = ON');
        }
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    public function query(string $sql, array $bindings = []): array|int|string
    {
        $statement = $this->pdo->prepare($sql);

        foreach ($bindings as $key => $value) {
            $param = is_int($key)
                ? $key + 1
                : (str_starts_with((string) $key, ':') ? (string) $key : ':' . $key);

            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };

            $statement->bindValue($param, $value, $type);
        }

        $statement->execute();

        if ($statement->columnCount() > 0) {
            return $statement->fetchAll();
        }

        if (preg_match('/^\s*INSERT\b/i', $sql) === 1) {
            return $this->pdo->lastInsertId();
        }

        return $statement->rowCount();
    }
}
