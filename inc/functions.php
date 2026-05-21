<?php
session_start();
require_once __DIR__ . '/../config/database.php';

function sanitize($value): string
{
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function getCurrentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user']);
}

function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function isRestaurantManager(): bool
{
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'restaurant_manager';
}

function isDeliveryMan(): bool
{
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'delivery_man';
}

function isCustomer(): bool
{
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'customer';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('public/index.php?route=auth&action=unified');
    }
}

function requireAdmin(): void
{
    if (!isAdmin()) {
        redirect('public/index.php?route=auth&action=unified');
    }
}

function requireRestaurantManager(): void
{
    if (!isRestaurantManager()) {
        redirect('public/index.php?route=auth&action=unified');
    }
}

function requireDeliveryMan(): void
{
    if (!isDeliveryMan()) {
        redirect('public/index.php?route=auth&action=unified');
    }
}

function cartItems(): array
{
    return $_SESSION['cart'] ?? [];
}

function cartCount(): int
{
    return array_sum(cartItems());
}

function cartTotal(): float
{
    $total = 0.0;
    $items = cartItems();
    if (empty($items)) {
        return 0.0;
    }

    $ids = array_keys($items);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT id, price,
                COALESCE((
                    SELECT d.discount_pct
                    FROM discounts d
                    WHERE d.menu_item_id = products.menu_item_id
                      AND d.is_active = 1
                      AND d.valid_from <= NOW()
                      AND d.valid_until >= NOW()
                    ORDER BY d.discount_pct DESC
                    LIMIT 1
                ), 0) AS discount_pct
            FROM products
            WHERE id IN ($placeholders) AND status = 'active'";
    $types = str_repeat('i', count($ids));
    $params = $ids;
    $rows = db_fetch_all($sql, $types, $params);

    foreach ($rows as $row) {
        $discountPct = (float) ($row['discount_pct'] ?? 0);
        $price = (float) $row['price'];
        if ($discountPct > 0) {
            $price = $price * (1 - ($discountPct / 100));
        }
        $total += $price * ($items[$row['id']] ?? 0);
    }

    return $total;
}

function addToCart(int $productId, int $quantity = 1): void
{
    $items = cartItems();
    $items[$productId] = max(1, ($items[$productId] ?? 0) + $quantity);
    $_SESSION['cart'] = $items;
}

function updateCart(array $quantities): void
{
    $items = cartItems();
    foreach ($quantities as $productId => $quantity) {
        $quantity = max(0, (int) $quantity);
        if ($quantity > 0) {
            $items[$productId] = $quantity;
        } else {
            unset($items[$productId]);
        }
    }
    $_SESSION['cart'] = $items;
}

function clearCart(): void
{
    unset($_SESSION['cart']);
}

function formatCurrency(float $amount): string
{
    return 'BDT' . number_format($amount, 2);
}


function convertPhpToBdt(float $amount): float
{
    $rate = (float) (getSetting('php_to_bdt_rate') ?? 1.0);
    return $amount * $rate;
}



function formatCurrencyWithCode(float $amount, string $code = 'PHP'): string
{
    if (strtoupper($code) === 'BDT') {
        return 'BDT ' . number_format($amount, 2);
    }
    return 'BDT ' . number_format($amount, 2);
}


function formatAsBdt(float $amount): string
{
    return 'BDT ' . number_format($amount, 2);
}

function getCartProducts(): array
{
    $items = cartItems();
    if (empty($items)) {
        return [];
    }

    $ids = array_keys($items);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT id, name, price, image, restaurant_id, menu_item_id,
                COALESCE((
                    SELECT d.id
                    FROM discounts d
                    WHERE d.menu_item_id = products.menu_item_id
                      AND d.is_active = 1
                      AND d.valid_from <= NOW()
                      AND d.valid_until >= NOW()
                    ORDER BY d.discount_pct DESC
                    LIMIT 1
                ), 0) AS discount_id,
                COALESCE((
                    SELECT d.discount_pct
                    FROM discounts d
                    WHERE d.menu_item_id = products.menu_item_id
                      AND d.is_active = 1
                      AND d.valid_from <= NOW()
                      AND d.valid_until >= NOW()
                    ORDER BY d.discount_pct DESC
                    LIMIT 1
                ), 0) AS discount_pct
            FROM products
            WHERE id IN ($placeholders) AND status = 'active'";
    $types = str_repeat('i', count($ids));
    $params = $ids;
    $products = db_fetch_all($sql, $types, $params);

    foreach ($products as &$product) {
        $product['quantity'] = $items[$product['id']];
        $product['original_price'] = (float) $product['price'];
        $discountPct = (float) ($product['discount_pct'] ?? 0);
        if ($discountPct > 0) {
            $product['price'] = (float) $product['price'] * (1 - ($discountPct / 100));
        }
        $product['subtotal'] = $product['price'] * $product['quantity'];
    }

    return $products;
}

function getSetting($key)
{
    // detect schema variations in `settings` table
    $cols = db_fetch_all("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings'");
    $colNames = array_column($cols, 'COLUMN_NAME');
    $keyCol = in_array('key', $colNames, true) ? 'key' : (in_array('k', $colNames, true) ? 'k' : null);
    $valCol = in_array('value', $colNames, true) ? 'value' : (in_array('v', $colNames, true) ? 'v' : null);
    if ($keyCol === null || $valCol === null) return null;

    // backtick identifiers to avoid reserved-word conflicts (e.g. `key`)
    $sql = "SELECT `{$valCol}` AS value FROM `settings` WHERE `{$keyCol}` = ?";
    $row = db_fetch_one($sql, 's', [$key]);
    return $row['value'] ?? null;
}
