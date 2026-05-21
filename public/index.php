<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../controllers/Controller.php';
require_once __DIR__ . '/../controllers/HomeController.php';
require_once __DIR__ . '/../controllers/MenuController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../controllers/RestaurantController.php';
require_once __DIR__ . '/../controllers/DeliveryController.php';

$route = $_GET['route'] ?? 'home';
$action = $_GET['action'] ?? 'index';

switch ($route) {
    case 'home':
        (new HomeController())->index();
        break;
    case 'menu':
        (new MenuController())->index();
        break;
    case 'auth':
        $controller = new AuthController();
        if ($action === 'register') {
            $controller->register();
        } elseif ($action === 'logout') {
            $controller->logout();
        } elseif ($action === 'unified') {
            $controller->unified();
        } else {
            $controller->login();
        }
        break;
    case 'cart':
        (new CartController())->index();
        break;
    case 'order':
        $controller = new OrderController();
        if ($action === 'checkout') {
            $controller->checkout();
        } elseif ($action === 'success') {
            $controller->success();
        } else {
            $controller->history();
        }
        break;
    case 'admin':
        $controller = new AdminController();
        if ($action === 'orders') {
            $controller->orders();
        } elseif ($action === 'products') {
            $controller->products();
        } elseif ($action === 'categories') {
            $controller->categories();
        } elseif ($action === 'users') {
            $controller->users();
        } elseif ($action === 'restaurants') {
            $controller->restaurants();
        } elseif ($action === 'customers') {
            $controller->customers();
        } elseif ($action === 'delivery_agents') {
            $controller->deliveryAgents();
        } elseif ($action === 'complaints') {
            $controller->complaints();
        } elseif ($action === 'view_complaint') {
            $controller->viewComplaint();
        } elseif ($action === 'view_user') {
            $controller->viewUser();
        } elseif ($action === 'featured') {
            $controller->featured();
        } elseif ($action === 'settings') {
            $controller->settings();
        } elseif ($action === 'analytics') {
            $controller->analytics();
        } else {
            $controller->dashboard();
        }
        break;
    case 'restaurant':
        $controller = new RestaurantController();
        if ($action === 'profile') {
            $controller->profile();
        } elseif ($action === 'menu') {
            $controller->menu();
        } elseif ($action === 'orders') {
            $controller->orders();
        } elseif ($action === 'orders_feed') {
            $controller->ordersFeed();
        } elseif ($action === 'reviews') {
            $controller->reviews();
        } elseif ($action === 'analytics') {
            $controller->analytics();
        } elseif ($action === 'complaints') {
            $controller->complaints();
        } else {
            $controller->dashboard();
        }
        break;
    case 'delivery':
        $controller = new DeliveryController();
        if ($action === 'assignments') {
            $controller->assignments();
        } elseif ($action === 'history') {
            $controller->history();
        } elseif ($action === 'earnings') {
            $controller->earnings();
        } elseif ($action === 'profile') {
            $controller->profile();
        } else {
            $controller->dashboard();
        }
        break;
    default:
        http_response_code(404);
        echo 'Page not found';
        break;
}
