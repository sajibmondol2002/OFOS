<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../controllers/Controller.php';
require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'login';

$controller = new AuthController();

try {
    if ($action === 'login') {
        $controller->ajaxLogin();
    } elseif ($action === 'register') {
        $controller->ajaxRegister();
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Action not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
