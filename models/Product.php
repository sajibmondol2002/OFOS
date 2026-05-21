<?php
require_once __DIR__ . '/Model.php';

class Product extends Model
{
    public function featured(): array
    {
        return $this->fetchAll(
            "SELECT p.id, p.name, p.description, p.price, p.image, p.restaurant_id, p.menu_item_id, c.name AS category_name,
                    COALESCE((
                        SELECT d.discount_pct
                        FROM discounts d
                        WHERE d.menu_item_id = p.menu_item_id
                          AND d.is_active = 1
                          AND d.valid_from <= NOW()
                          AND d.valid_until >= NOW()
                        ORDER BY d.discount_pct DESC
                        LIMIT 1
                    ), 0) AS discount_pct
             FROM products p
             JOIN categories c ON c.id = p.category_id
             LEFT JOIN restaurants r ON r.id = p.restaurant_id
             WHERE p.status = ?
             ORDER BY p.created_at DESC
             LIMIT 6",
            's',
            ['active']
        );
    }

    public function all(int $categoryId = 0): array
    {
        if ($categoryId > 0) {
            return $this->fetchAll(
                "SELECT p.id, p.name, p.description, p.price, p.image, p.restaurant_id, p.menu_item_id, c.name AS category_name, r.name AS restaurant_name,
                        COALESCE((
                            SELECT d.id
                            FROM discounts d
                            WHERE d.menu_item_id = p.menu_item_id
                              AND d.is_active = 1
                              AND d.valid_from <= NOW()
                              AND d.valid_until >= NOW()
                            ORDER BY d.discount_pct DESC
                            LIMIT 1
                        ), 0) AS discount_id,
                        COALESCE((
                            SELECT d.discount_pct
                            FROM discounts d
                            WHERE d.menu_item_id = p.menu_item_id
                              AND d.is_active = 1
                              AND d.valid_from <= NOW()
                              AND d.valid_until >= NOW()
                            ORDER BY d.discount_pct DESC
                            LIMIT 1
                        ), 0) AS discount_pct
                 FROM products p
                 JOIN categories c ON c.id = p.category_id
                 LEFT JOIN restaurants r ON r.id = p.restaurant_id
                 WHERE p.category_id = ? AND p.status = ?
                 ORDER BY p.name",
                'is',
                [$categoryId, 'active']
            );
        }

        return $this->fetchAll(
            "SELECT p.id, p.name, p.description, p.price, p.image, p.restaurant_id, p.menu_item_id, c.name AS category_name, r.name AS restaurant_name,
                    COALESCE((
                        SELECT d.id
                        FROM discounts d
                        WHERE d.menu_item_id = p.menu_item_id
                          AND d.is_active = 1
                          AND d.valid_from <= NOW()
                          AND d.valid_until >= NOW()
                        ORDER BY d.discount_pct DESC
                        LIMIT 1
                    ), 0) AS discount_id,
                    COALESCE((
                        SELECT d.discount_pct
                        FROM discounts d
                        WHERE d.menu_item_id = p.menu_item_id
                          AND d.is_active = 1
                          AND d.valid_from <= NOW()
                          AND d.valid_until >= NOW()
                        ORDER BY d.discount_pct DESC
                        LIMIT 1
                    ), 0) AS discount_pct
             FROM products p
             JOIN categories c ON c.id = p.category_id
             LEFT JOIN restaurants r ON r.id = p.restaurant_id
             WHERE p.status = ?
             ORDER BY p.name",
            's',
            ['active']
        );
    }

    public function allByCategoryName(string $categoryName): array
    {
        return $this->fetchAll(
            "SELECT p.id, p.name, p.description, p.price, p.image, p.restaurant_id, p.menu_item_id, c.name AS category_name, r.name AS restaurant_name,
                    COALESCE((
                        SELECT d.id
                        FROM discounts d
                        WHERE d.menu_item_id = p.menu_item_id
                          AND d.is_active = 1
                          AND d.valid_from <= NOW()
                          AND d.valid_until >= NOW()
                        ORDER BY d.discount_pct DESC
                        LIMIT 1
                    ), 0) AS discount_id,
                    COALESCE((
                        SELECT d.discount_pct
                        FROM discounts d
                        WHERE d.menu_item_id = p.menu_item_id
                          AND d.is_active = 1
                          AND d.valid_from <= NOW()
                          AND d.valid_until >= NOW()
                        ORDER BY d.discount_pct DESC
                        LIMIT 1
                    ), 0) AS discount_pct
             FROM products p
             JOIN categories c ON c.id = p.category_id
             LEFT JOIN restaurants r ON r.id = p.restaurant_id
             WHERE c.name = ? AND p.status = ? AND (p.restaurant_id IS NULL OR (r.is_open = 1 AND r.is_approved = 1))
             ORDER BY p.name",
            'ss',
            [$categoryName, 'active']
        );
    }

    public function allForAdmin(): array
    {
        return $this->fetchAll(
            'SELECT p.id, p.name, p.description, p.price, p.image, p.status, c.name AS category_name, r.name AS restaurant_name FROM products p JOIN categories c ON c.id = p.category_id LEFT JOIN restaurants r ON r.id = p.restaurant_id ORDER BY p.name'
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT p.*,
                    COALESCE((
                        SELECT d.id
                        FROM discounts d
                        WHERE d.menu_item_id = p.menu_item_id
                          AND d.is_active = 1
                          AND d.valid_from <= NOW()
                          AND d.valid_until >= NOW()
                        ORDER BY d.discount_pct DESC
                        LIMIT 1
                    ), 0) AS discount_id,
                    COALESCE((
                        SELECT d.discount_pct
                        FROM discounts d
                        WHERE d.menu_item_id = p.menu_item_id
                          AND d.is_active = 1
                          AND d.valid_from <= NOW()
                          AND d.valid_until >= NOW()
                        ORDER BY d.discount_pct DESC
                        LIMIT 1
                    ), 0) AS discount_pct
             FROM products p WHERE p.id = ? AND p.status = ?",
            'is',
            [$id, 'active']
        );
    }

    public function create(int $categoryId, string $name, string $description, float $price, string $image, string $status): int
    {
        $this->execute(
            'INSERT INTO products (category_id, name, description, price, image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
            'issdsss',
            [$categoryId, $name, $description, $price, $image, $status, date('Y-m-d H:i:s')]
        );
        return $this->insertId();
    }

    public function delete(int $id): int
    {
        return $this->execute('DELETE FROM products WHERE id = ?', 'i', [$id]);
    }
}