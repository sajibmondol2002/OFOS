<?php
require_once __DIR__ . '/Model.php';

class Category extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            'SELECT MIN(id) AS id, name FROM categories GROUP BY name ORDER BY name'
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT id, name, description FROM categories WHERE id = ?', 'i', [$id]);
    }

    public function create(string $name, string $description): int
    {
        $this->execute(
            'INSERT INTO categories (name, description, created_at) VALUES (?, ?, ?)',
            'sss',
            [$name, $description, date('Y-m-d H:i:s')]
        );
        return $this->insertId();
    }

    public function delete(int $id): int
    {
        return $this->execute('DELETE FROM categories WHERE id = ?', 'i', [$id]);
    }
}
