<?php
require_once __DIR__ . '/../config/database.php';

$default = db_fetch_one("SELECT r.id FROM restaurants r JOIN users u ON u.id = r.manager_id WHERE u.email = 'manager@food.local' LIMIT 1");
$default_id = $default['id'] ?? null;
if (!$default_id) {
    echo "Default restaurant not found (manager@food.local).\n";
    exit(1);
}

$cats = db_fetch_all("SELECT id, name, restaurant_id FROM categories WHERE restaurant_id IS NULL OR restaurant_id <> ?", 'i', [$default_id]);
if (empty($cats)) {
    echo "No non-default categories found. Nothing to delete.\n";
    exit(0);
}

echo "Categories that would be deleted (" . count($cats) . "):\n";
foreach ($cats as $c) {
    echo sprintf(" - %d: %s (restaurant_id=%s)\n", $c['id'], $c['name'], $c['restaurant_id'] ?? 'NULL');
}

if (in_array('execute', $argv)) {
    global $mysqli;
    $ids = array_column($cats, 'id');
    $idList = implode(',', array_map('intval', $ids));

    try {
        $mysqli->begin_transaction();
        // delete menu_categories that reference these category ids
        $sql1 = "DELETE FROM menu_categories WHERE category_id IN ($idList)";
        $mysqli->query($sql1);
        // delete categories
        $sql2 = "DELETE FROM categories WHERE id IN ($idList)";
        $mysqli->query($sql2);
        $mysqli->commit();
        echo "Deleted " . count($ids) . " categories and related menu_categories.\n";
    } catch (Exception $e) {
        $mysqli->rollback();
        echo "Error during delete: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "Run this script with the argument 'execute' to perform the deletion.\n";
}
