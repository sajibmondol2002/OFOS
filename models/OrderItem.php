<?php
require_once __DIR__ . '/Model.php';

class OrderItem extends Model
{
    public function create(int $orderId, int $productId, int $quantity, float $price, float $subtotal, ?int $menuItemId = null, ?int $discountId = null): int
    {
        $this->execute(
            'INSERT INTO order_items (order_id, product_id, menu_item_id, quantity, price, unit_price, subtotal, discount_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            'iiiidddi',
            [$orderId, $productId, $menuItemId, $quantity, $price, $price, $subtotal, $discountId]
        );
        return $this->insertId();
    }

    public function allByOrderId(int $orderId): array
    {
        return $this->fetchAll('SELECT oi.*, COALESCE(mi.name, p.name) AS name FROM order_items oi LEFT JOIN menu_items mi ON mi.id = oi.menu_item_id LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?', 'i', [$orderId]);
    }
}
