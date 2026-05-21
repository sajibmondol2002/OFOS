<?php
require_once __DIR__ . '/Model.php';

class Restaurant extends Model
{
    public function findByManagerId(int $managerId): ?array
    {
        return $this->fetchOne('SELECT * FROM restaurants WHERE manager_id = ? LIMIT 1', 'i', [$managerId]);
    }

    public function ensureForManager(int $managerId, string $managerName = 'Restaurant Manager'): array
    {
        $restaurant = $this->findByManagerId($managerId);
        if ($restaurant) {
            return $restaurant;
        }

        $name = $managerName . "'s Restaurant";
        $this->execute(
            'INSERT INTO restaurants (manager_id, name, description, cuisine_type, address, city, opening_hours, delivery_radius_km, is_open, is_approved, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            'issssssdiis',
            [$managerId, $name, '', '', '', '', '', 5.00, 0, 0, date('Y-m-d H:i:s')]
        );

        return $this->findByManagerId($managerId) ?? [];
    }

    public function createForManager(int $managerId, array $data, int $approved = 0): int
    {
        $this->execute(
            'INSERT INTO restaurants (manager_id, name, description, cuisine_type, address, city, opening_hours, delivery_radius_km, is_open, is_approved, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            'issssssdiis',
            [
                $managerId,
                $data['name'],
                $data['description'] ?? '',
                $data['cuisine_type'] ?? '',
                $data['address'] ?? '',
                $data['city'] ?? '',
                $data['opening_hours'] ?? '',
                (float) ($data['delivery_radius_km'] ?? 5),
                (int) ($data['is_open'] ?? 0),
                $approved,
                date('Y-m-d H:i:s'),
            ]
        );

        return $this->insertId();
    }

    public function updateProfile(int $restaurantId, array $data): int
    {
        return $this->execute(
            'UPDATE restaurants
             SET name = ?, description = ?, cuisine_type = ?, address = ?, city = ?, logo_path = ?, opening_hours = ?, delivery_radius_km = ?, is_open = ?
             WHERE id = ?',
            'sssssssdii',
            [
                $data['name'],
                $data['description'],
                $data['cuisine_type'],
                $data['address'],
                $data['city'],
                $data['logo_path'],
                $data['opening_hours'],
                (float) $data['delivery_radius_km'],
                (int) $data['is_open'],
                $restaurantId,
            ]
        );
    }

    public function categories(int $restaurantId): array
    {
        return $this->fetchAll(
            'SELECT id, category_id, name, display_order FROM menu_categories WHERE restaurant_id = ? ORDER BY display_order, name',
            'i',
            [$restaurantId]
        );
    }

    public function findCategory(int $restaurantId, int $categoryId): ?array
    {
        return $this->fetchOne('SELECT * FROM menu_categories WHERE id = ? AND restaurant_id = ?', 'ii', [$categoryId, $restaurantId]);
    }

    public function findCategoryByName(int $restaurantId, string $name): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM menu_categories WHERE restaurant_id = ? AND name = ? LIMIT 1',
            'is',
            [$restaurantId, $name]
        );
    }

    public function createCategory(int $restaurantId, string $name, int $displayOrder): int
    {
        $this->execute(
            'INSERT INTO categories (restaurant_id, name, description, created_at) VALUES (?, ?, ?, ?)',
            'isss',
            [$restaurantId, $name, '', date('Y-m-d H:i:s')]
        );
        $legacyCategoryId = $this->insertId();

        $this->execute(
            'INSERT INTO menu_categories (restaurant_id, category_id, name, display_order) VALUES (?, ?, ?, ?)',
            'iisi',
            [$restaurantId, $legacyCategoryId, $name, $displayOrder]
        );

        return $this->insertId();
    }

    public function updateCategory(int $restaurantId, int $categoryId, string $name, int $displayOrder): int
    {
        $category = $this->findCategory($restaurantId, $categoryId);
        if (!$category) {
            return 0;
        }

        if (!empty($category['category_id'])) {
            $this->execute('UPDATE categories SET name = ? WHERE id = ?', 'si', [$name, (int) $category['category_id']]);
        }

        return $this->execute(
            'UPDATE menu_categories SET name = ?, display_order = ? WHERE id = ? AND restaurant_id = ?',
            'siii',
            [$name, $displayOrder, $categoryId, $restaurantId]
        );
    }

    public function deleteCategory(int $restaurantId, int $categoryId): int
    {
        $category = $this->findCategory($restaurantId, $categoryId);
        if (!$category) {
            return 0;
        }

        $items = $this->fetchAll('SELECT id, product_id FROM menu_items WHERE restaurant_id = ? AND category_id = ?', 'ii', [$restaurantId, $categoryId]);
        foreach ($items as $item) {
            $this->deleteItem($restaurantId, (int) $item['id']);
        }

        $deleted = $this->execute('DELETE FROM menu_categories WHERE id = ? AND restaurant_id = ?', 'ii', [$categoryId, $restaurantId]);
        if (!empty($category['category_id'])) {
            $this->execute('DELETE FROM categories WHERE id = ?', 'i', [(int) $category['category_id']]);
        }

        return $deleted;
    }

    public function items(int $restaurantId): array
    {
        return $this->fetchAll(
            "SELECT mi.*, mc.name AS category_name,
                COALESCE((
                    SELECT d.discount_pct
                    FROM discounts d
                    WHERE d.menu_item_id = mi.id
                      AND d.is_active = 1
                      AND d.valid_from <= NOW()
                      AND d.valid_until >= NOW()
                    ORDER BY d.discount_pct DESC
                    LIMIT 1
                ), 0) AS active_discount_pct
             FROM menu_items mi
             LEFT JOIN menu_categories mc ON mc.id = mi.category_id
             WHERE mi.restaurant_id = ?
             ORDER BY mc.display_order, mi.name",
            'i',
            [$restaurantId]
        );
    }

    public function findItem(int $restaurantId, int $itemId): ?array
    {
        return $this->fetchOne('SELECT * FROM menu_items WHERE id = ? AND restaurant_id = ?', 'ii', [$itemId, $restaurantId]);
    }

    public function createItem(int $restaurantId, array $data): int
    {
        $this->execute(
            'INSERT INTO menu_items (restaurant_id, category_id, name, description, price, image_path, is_available, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            'iissdsis',
            [
                $restaurantId,
                (int) $data['category_id'],
                $data['name'],
                $data['description'],
                (float) $data['price'],
                $data['image_path'],
                (int) $data['is_available'],
                date('Y-m-d H:i:s'),
            ]
        );
        $menuItemId = $this->insertId();

        $category = $this->findCategory($restaurantId, (int) $data['category_id']);
        $legacyCategoryId = (int) ($category['category_id'] ?? 0);

        if ($legacyCategoryId > 0) {
            $this->execute(
                'INSERT INTO products (restaurant_id, menu_item_id, category_id, name, description, price, image, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                'iiissdsss',
                [
                    $restaurantId,
                    $menuItemId,
                    $legacyCategoryId,
                    $data['name'],
                    $data['description'],
                    (float) $data['price'],
                    $data['image_path'] ?: 'placeholder.png',
                    (int) $data['is_available'] === 1 ? 'active' : 'inactive',
                    date('Y-m-d H:i:s'),
                ]
            );
            $productId = $this->insertId();
            $this->execute('UPDATE menu_items SET product_id = ? WHERE id = ?', 'ii', [$productId, $menuItemId]);
        }

        return $menuItemId;
    }

    public function updateItem(int $restaurantId, int $itemId, array $data): int
    {
        $item = $this->findItem($restaurantId, $itemId);
        if (!$item) {
            return 0;
        }

        $affected = $this->execute(
            'UPDATE menu_items
             SET category_id = ?, name = ?, description = ?, price = ?, image_path = ?, is_available = ?
             WHERE id = ? AND restaurant_id = ?',
            'issdsiii',
            [
                (int) $data['category_id'],
                $data['name'],
                $data['description'],
                (float) $data['price'],
                $data['image_path'],
                (int) $data['is_available'],
                $itemId,
                $restaurantId,
            ]
        );

        $category = $this->findCategory($restaurantId, (int) $data['category_id']);
        if (!empty($item['product_id']) && !empty($category['category_id'])) {
            $this->execute(
                'UPDATE products
                 SET category_id = ?, name = ?, description = ?, price = ?, image = ?, status = ?
                 WHERE id = ? AND restaurant_id = ?',
                'issdssii',
                [
                    (int) $category['category_id'],
                    $data['name'],
                    $data['description'],
                    (float) $data['price'],
                    $data['image_path'] ?: 'placeholder.png',
                    (int) $data['is_available'] === 1 ? 'active' : 'inactive',
                    (int) $item['product_id'],
                    $restaurantId,
                ]
            );
        }

        return $affected;
    }

    public function deleteItem(int $restaurantId, int $itemId): int
    {
        $item = $this->findItem($restaurantId, $itemId);
        if (!$item) {
            return 0;
        }

        if (!empty($item['product_id'])) {
            $this->execute('DELETE FROM products WHERE id = ? AND restaurant_id = ?', 'ii', [(int) $item['product_id'], $restaurantId]);
        }

        return $this->execute('DELETE FROM menu_items WHERE id = ? AND restaurant_id = ?', 'ii', [$itemId, $restaurantId]);
    }

    public function discounts(int $restaurantId): array
    {
        return $this->fetchAll(
            'SELECT d.*, mi.name AS item_name
             FROM discounts d
             JOIN menu_items mi ON mi.id = d.menu_item_id
             WHERE d.restaurant_id = ?
             ORDER BY d.valid_from DESC',
            'i',
            [$restaurantId]
        );
    }

    public function createDiscount(int $restaurantId, int $menuItemId, float $discountPct, string $validFrom, string $validUntil, int $isActive): int
    {
        $this->execute(
            'INSERT INTO discounts (menu_item_id, restaurant_id, discount_pct, valid_from, valid_until, is_active)
             VALUES (?, ?, ?, ?, ?, ?)',
            'iidssi',
            [$menuItemId, $restaurantId, $discountPct, $validFrom, $validUntil, $isActive]
        );

        return $this->insertId();
    }

    public function setDiscountStatus(int $restaurantId, int $discountId, int $isActive): int
    {
        return $this->execute('UPDATE discounts SET is_active = ? WHERE id = ? AND restaurant_id = ?', 'iii', [$isActive, $discountId, $restaurantId]);
    }

    public function deleteDiscount(int $restaurantId, int $discountId): int
    {
        return $this->execute('DELETE FROM discounts WHERE id = ? AND restaurant_id = ?', 'ii', [$discountId, $restaurantId]);
    }

    public function orders(int $restaurantId, bool $activeOnly = false): array
    {
        $where = $activeOnly
            ? "AND o.status IN ('pending','accepted','preparing','ready','picked_up')"
            : '';

        return $this->fetchAll(
            "SELECT o.id, o.total_amount, o.subtotal, o.delivery_fee, o.delivery_address, o.payment_method, o.status, o.delivery_status, o.created_at,
                    u.name AS customer, da.name AS delivery_agent
             FROM orders o
             JOIN users u ON u.id = COALESCE(o.customer_id, o.user_id)
             LEFT JOIN users da ON da.id = COALESCE(o.agent_id, o.delivery_agent_id)
             WHERE o.restaurant_id = ? {$where}
             ORDER BY o.created_at DESC",
            'i',
            [$restaurantId]
        );
    }

    public function orderItems(int $orderId): array
    {
        return $this->fetchAll(
            'SELECT oi.quantity, COALESCE(oi.unit_price, oi.price) AS unit_price, oi.subtotal, COALESCE(mi.name, p.name) AS name
             FROM order_items oi
             LEFT JOIN menu_items mi ON mi.id = oi.menu_item_id
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = ?',
            'i',
            [$orderId]
        );
    }

    public function updateOrderStatus(int $restaurantId, int $orderId, string $status): int
    {
        $deliveryStatus = $status === 'ready' ? 'pending' : null;
        if ($deliveryStatus !== null) {
            return $this->execute(
                'UPDATE orders SET status = ?, delivery_status = ? WHERE id = ? AND restaurant_id = ?',
                'ssii',
                [$status, $deliveryStatus, $orderId, $restaurantId]
            );
        }

        return $this->execute('UPDATE orders SET status = ? WHERE id = ? AND restaurant_id = ?', 'sii', [$status, $orderId, $restaurantId]);
    }

    public function reviews(int $restaurantId): array
    {
        return $this->fetchAll(
            'SELECT r.id, r.order_id, r.rating, r.comment, r.manager_reply, r.created_at,
                    u.name AS customer, o.status AS order_status
             FROM reviews r
             JOIN users u ON u.id = COALESCE(r.customer_id, r.user_id)
             JOIN orders o ON o.id = r.order_id
             WHERE COALESCE(r.restaurant_id, o.restaurant_id) = ?
             ORDER BY r.created_at DESC',
            'i',
            [$restaurantId]
        );
    }

    public function replyToReview(int $restaurantId, int $reviewId, string $reply): int
    {
        return $this->execute(
            'UPDATE reviews r
             JOIN orders o ON o.id = r.order_id
             SET r.manager_reply = ?
             WHERE r.id = ? AND COALESCE(r.restaurant_id, o.restaurant_id) = ?',
            'sii',
            [$reply, $reviewId, $restaurantId]
        );
    }

    public function complaints(int $restaurantId): array
    {
        return $this->fetchAll(
            'SELECT c.id, c.order_id, c.subject, COALESCE(c.description, c.message) AS description, c.status, c.created_at, u.name AS submitter
             FROM complaints c
             JOIN users u ON u.id = COALESCE(c.submitter_id, c.user_id)
             LEFT JOIN orders o ON o.id = c.order_id
             WHERE c.restaurant_id = ? OR o.restaurant_id = ?
             ORDER BY c.created_at DESC',
            'ii',
            [$restaurantId, $restaurantId]
        );
    }

    public function analytics(int $restaurantId): array
    {
        $summary = $this->fetchOne(
            "SELECT COUNT(*) AS total_orders,
                    COALESCE(SUM(CASE WHEN status <> 'cancelled' THEN total_amount ELSE 0 END), 0) AS total_revenue,
                    COALESCE(AVG(CASE WHEN status <> 'cancelled' THEN total_amount ELSE NULL END), 0) AS average_order_value
             FROM orders
             WHERE restaurant_id = ?",
            'i',
            [$restaurantId]
        ) ?? ['total_orders' => 0, 'total_revenue' => 0, 'average_order_value' => 0];

        $ordersByDay = $this->fetchAll(
            "SELECT DATE(created_at) AS period, COUNT(*) AS orders, COALESCE(SUM(total_amount),0) AS revenue
             FROM orders
             WHERE restaurant_id = ?
             GROUP BY DATE(created_at)
             ORDER BY period DESC
             LIMIT 14",
            'i',
            [$restaurantId]
        );

        $ordersByWeek = $this->fetchAll(
            "SELECT YEARWEEK(created_at, 1) AS period, COUNT(*) AS orders, COALESCE(SUM(total_amount),0) AS revenue
             FROM orders
             WHERE restaurant_id = ?
             GROUP BY YEARWEEK(created_at, 1)
             ORDER BY period DESC
             LIMIT 8",
            'i',
            [$restaurantId]
        );

        $ordersByMonth = $this->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS period, COUNT(*) AS orders, COALESCE(SUM(total_amount),0) AS revenue
             FROM orders
             WHERE restaurant_id = ?
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY period DESC
             LIMIT 12",
            'i',
            [$restaurantId]
        );

        $topItems = $this->fetchAll(
            'SELECT COALESCE(mi.name, p.name) AS name, SUM(oi.quantity) AS total_quantity, COALESCE(SUM(oi.subtotal),0) AS revenue
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             LEFT JOIN menu_items mi ON mi.id = oi.menu_item_id
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE o.restaurant_id = ?
             GROUP BY COALESCE(mi.id, p.id), COALESCE(mi.name, p.name)
             ORDER BY total_quantity DESC
             LIMIT 10',
            'i',
            [$restaurantId]
        );

        $discountPerformance = $this->fetchAll(
            'SELECT d.id, mi.name AS item_name, d.discount_pct, d.valid_from, d.valid_until, d.is_active,
                    COUNT(oi.id) AS orders_used,
                    COALESCE(SUM(oi.quantity), 0) AS items_sold
             FROM discounts d
             JOIN menu_items mi ON mi.id = d.menu_item_id
             LEFT JOIN order_items oi ON oi.discount_id = d.id
             WHERE d.restaurant_id = ?
             GROUP BY d.id, mi.name, d.discount_pct, d.valid_from, d.valid_until, d.is_active
             ORDER BY d.valid_from DESC',
            'i',
            [$restaurantId]
        );

        return [
            'summary' => $summary,
            'ordersByDay' => $ordersByDay,
            'ordersByWeek' => $ordersByWeek,
            'ordersByMonth' => $ordersByMonth,
            'topItems' => $topItems,
            'discountPerformance' => $discountPerformance,
        ];
    }
}
