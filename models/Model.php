<?php
require_once __DIR__ . '/../config/database.php';

class Model
{
    protected function fetchAll(string $sql, string $types = '', array $params = []): array
    {
        return db_fetch_all($sql, $types, $params);
    }

    protected function fetchOne(string $sql, string $types = '', array $params = []): ?array
    {
        return db_fetch_one($sql, $types, $params);
    }

    protected function execute(string $sql, string $types = '', array $params = []): int
    {
        return db_execute($sql, $types, $params);
    }

    protected function insertId(): int
    {
        return db_insert_id();
    }
}
