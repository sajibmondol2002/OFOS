<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Product.php';

class CartController extends Controller
{
    public function index(): void
    {
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'add') {
                $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
                if ($productId > 0) {
                    addToCart($productId, 1);
                    $message = 'Product added to cart.';
                }
            } elseif ($action === 'update') {
                $quantities = [];
                foreach ($_POST['quantities'] ?? [] as $productId => $quantity) {
                    $quantities[(int)$productId] = (int)$quantity;
                }
                updateCart($quantities);
                $message = 'Cart updated successfully.';
            }
        }

        $products = getCartProducts();

        $this->render('cart/index.php', [
            'cartProducts' => $products,
            'message' => $message,
            'currentUser' => getCurrentUser(),
        ]);
    }
}
