<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/OrderItem.php';
require_once __DIR__ . '/../models/Review.php';

class OrderController extends Controller
{
    public function history(): void
    {
        requireLogin();
        $orderModel = new Order();
        $orders = $orderModel->allByUser($_SESSION['user']['id']);

        $this->render('order/history.php', [
            'orders' => $orders,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function checkout(): void
    {
        requireLogin();
        $cartProducts = getCartProducts();

        if (empty($cartProducts)) {
            $this->redirect('index.php?route=cart');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deliveryAddress = sanitize($_POST['delivery_address'] ?? '');
            $paymentMethod = sanitize($_POST['payment_method'] ?? 'cash_on_delivery');
            if ($deliveryAddress === '') {
                $error = 'Delivery address is required.';
            }

            $restaurantIds = array_values(array_unique(array_filter(array_map(static function ($product) {
                return isset($product['restaurant_id']) ? (int) $product['restaurant_id'] : 0;
            }, $cartProducts))));
            if (count($restaurantIds) > 1) {
                $error = 'Please order from one restaurant at a time.';
            }

            if ($error === '') {
                $orderModel = new Order();
                $subtotal = cartTotal();
                $deliveryFee = (float) (getSetting('base_delivery_fee') ?? 20);
                $orderTotal = $subtotal + $deliveryFee;
                $restaurantId = $restaurantIds[0] ?? null;
                $orderId = $orderModel->create($_SESSION['user']['id'], $orderTotal, $deliveryAddress, $restaurantId, $subtotal, $deliveryFee, $paymentMethod);
                $orderItemModel = new OrderItem();
                foreach ($cartProducts as $product) {
                    $orderItemModel->create(
                        $orderId,
                        (int) $product['id'],
                        (int) $product['quantity'],
                        (float) $product['price'],
                        (float) $product['subtotal'],
                        !empty($product['menu_item_id']) ? (int) $product['menu_item_id'] : null,
                        !empty($product['discount_id']) ? (int) $product['discount_id'] : null
                    );
                }
                clearCart();
                $this->redirect('index.php?route=order&action=success&id=' . $orderId);
            }
        }

        $this->render('order/checkout.php', [
            'cartProducts' => $cartProducts,
            'error' => $error,
            'currentUser' => getCurrentUser(),
            'deliveryAddress' => $_POST['delivery_address'] ?? '',
            'paymentMethod' => $_POST['payment_method'] ?? 'cash_on_delivery',
        ]);
    }

    public function success(): void
    {
        requireLogin();
        $orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $orderModel = new Order();
        $order = $orderModel->findById($orderId, $_SESSION['user']['id']);
        if (!$order) {
            $this->redirect('index.php?route=home');
        }

        $items = (new OrderItem())->allByOrderId($orderId);

        $reviewModel = new Review();
        $reviewSubmitted = false;
        $reviewMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
            $comment = sanitize($_POST['comment'] ?? '');
            if ($rating < 1 || $rating > 5 || $comment === '') {
                $reviewMessage = 'Please provide a valid rating and comment.';
            } elseif ($reviewModel->existsByOrderId($orderId)) {
                $reviewMessage = 'You have already submitted a review for this order.';
            } else {
                $reviewModel->create($orderId, $_SESSION['user']['id'], $rating, $comment, !empty($order['restaurant_id']) ? (int) $order['restaurant_id'] : null);
                $reviewSubmitted = true;
                $reviewMessage = 'Thank you! Your review has been submitted.';
            }
        }

        $this->render('order/success.php', [
            'order' => $order,
            'items' => $items,
            'currentUser' => getCurrentUser(),
            'reviewSubmitted' => $reviewSubmitted,
            'reviewMessage' => $reviewMessage,
            'reviewExists' => $reviewModel->existsByOrderId($orderId),
        ]);
    }
}
