<?php
require_once __DIR__ . '/Model.php';

class Order extends Model
{
    public function create(int $userId, float $total, string $deliveryAddress, ?int $restaurantId = null, float $subtotal = 0.0, float $deliveryFee = 0.0, string $paymentMethod = 'cash_on_delivery'): int
    {
        $this->execute(
            'INSERT INTO orders (user_id, customer_id, restaurant_id, subtotal, delivery_fee, total_amount, delivery_address, payment_method, status, estimated_delivery_minutes, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            'iiidddsssis',
            [$userId, $userId, $restaurantId, $subtotal ?: $total, $deliveryFee, $total, $deliveryAddress, $paymentMethod, 'pending', 45, date('Y-m-d H:i:s')]
        );
        return $this->insertId();
    }

    public function allByUser(int $userId): array
    {
        return $this->fetchAll('SELECT id, total_amount, status, delivery_address, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC', 'i', [$userId]);
    }

    public function findById(int $orderId, int $userId): ?array
    {
        return $this->fetchOne('SELECT * FROM orders WHERE id = ? AND user_id = ?', 'ii', [$orderId, $userId]);
    }

    public function all(): array
    {
        return $this->fetchAll('SELECT o.id, o.total_amount, o.status, o.created_at, u.name AS customer FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC');
    }

    public function updateStatus(int $orderId, string $status): int
    {
        return $this->execute('UPDATE orders SET status = ? WHERE id = ?', 'si', [$status, $orderId]);
    }

    public function availableAssignments(): array
    {
        return $this->fetchAll(
            "SELECT o.id, o.total_amount, o.delivery_address, o.delivery_status, o.created_at, u.name AS customer, r.name AS restaurant_name
             FROM orders o
             JOIN users u ON u.id = COALESCE(o.customer_id, o.user_id)
             LEFT JOIN restaurants r ON r.id = o.restaurant_id
             WHERE o.status = ? AND (o.delivery_agent_id IS NULL OR o.delivery_agent_id = 0)
             ORDER BY o.created_at DESC",
            's',
            ['ready']
        );
    }

    public function assignToAgent(int $orderId, int $agentId): int
    {
        return $this->execute('UPDATE orders SET delivery_agent_id = ?, agent_id = ?, delivery_status = ?, status = ? WHERE id = ?', 'iissi', [$agentId, $agentId, 'pending', 'picked_up', $orderId]);
    }

    public function unassignAgent(int $orderId, int $agentId): int
    {
        return $this->execute('UPDATE orders SET delivery_agent_id = NULL, agent_id = NULL, delivery_status = ?, status = ? WHERE id = ? AND delivery_agent_id = ?', 'ssii', ['pending', 'ready', $orderId, $agentId]);
    }

    public function allAssignedToAgent(int $agentId): array
    {
        return $this->fetchAll(
            'SELECT o.*, u.name AS customer FROM orders o JOIN users u ON u.id = o.user_id WHERE o.delivery_agent_id = ? ORDER BY o.created_at DESC',
            'i',
            [$agentId]
        );
    }

    public function allDeliveredForAgent(int $agentId): array
    {
        return $this->fetchAll(
            'SELECT o.*, u.name AS customer FROM orders o JOIN users u ON u.id = o.user_id WHERE o.delivery_agent_id = ? AND o.delivery_status = ? ORDER BY o.created_at DESC',
            'is',
            [$agentId, 'delivered']
        );
    }

    public function updateDeliveryStatus(int $orderId, string $deliveryStatus): int
    {
        if ($deliveryStatus === 'delivered') {
            return $this->execute('UPDATE orders SET delivery_status = ?, status = ?, delivered_at = ? WHERE id = ?', 'sssi', [$deliveryStatus, 'delivered', date('Y-m-d H:i:s'), $orderId]);
        }

        return $this->execute('UPDATE orders SET delivery_status = ? WHERE id = ?', 'si', [$deliveryStatus, $orderId]);
    }

    public function calculateEarnings(int $agentId): array
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS deliveries, SUM(total_amount * 0.10) AS total FROM orders WHERE delivery_agent_id = ? AND delivery_status = ?', 'is', [$agentId, 'delivered']);
        return [
            'deliveries' => (int) ($row['deliveries'] ?? 0),
            'total' => (float) ($row['total'] ?? 0),
        ];
    }

    public function earningsSummary(int $agentId): array
    {
        $today = $this->fetchOne('SELECT SUM(total_amount * 0.10) AS total FROM orders WHERE delivery_agent_id = ? AND delivery_status = ? AND DATE(created_at) = CURDATE()', 'is', [$agentId, 'delivered']);
        $week = $this->fetchOne('SELECT SUM(total_amount * 0.10) AS total FROM orders WHERE delivery_agent_id = ? AND delivery_status = ? AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)', 'is', [$agentId, 'delivered']);
        $month = $this->fetchOne('SELECT SUM(total_amount * 0.10) AS total FROM orders WHERE delivery_agent_id = ? AND delivery_status = ? AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())', 'is', [$agentId, 'delivered']);
        $allTime = $this->fetchOne('SELECT SUM(total_amount * 0.10) AS total FROM orders WHERE delivery_agent_id = ? AND delivery_status = ?', 'is', [$agentId, 'delivered']);

        return [
            'today' => (float) ($today['total'] ?? 0),
            'week' => (float) ($week['total'] ?? 0),
            'month' => (float) ($month['total'] ?? 0),
            'all_time' => (float) ($allTime['total'] ?? 0),
        ];
    }
}
