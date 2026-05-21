<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../models/Restaurant.php';

class RestaurantController extends Controller
{
    private function restaurantModel(): Restaurant
    {
        return new Restaurant();
    }

    private function currentRestaurant(): array
    {
        requireRestaurantManager();
        $currentUser = getCurrentUser();
        return $this->restaurantModel()->ensureForManager((int) $currentUser['id'], $currentUser['name'] ?? 'Restaurant Manager');
    }

    public string $uploadError = '';

    private function uploadImage(string $fieldName, string $prefix, ?string $existing = null): ?string
    {
        // কোনো file select না করলে আগের image রাখো
        if (empty($_FILES[$fieldName]['name']) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return $existing;
        }

        if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            $codes = [
                UPLOAD_ERR_INI_SIZE   => 'File too large (php.ini limit).',
                UPLOAD_ERR_FORM_SIZE  => 'File too large (form limit).',
                UPLOAD_ERR_PARTIAL    => 'File only partially uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'No temp folder found.',
                UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk.',
                UPLOAD_ERR_EXTENSION  => 'Upload blocked by extension.',
            ];
            $this->uploadError = $codes[$_FILES[$fieldName]['error']] ?? 'Upload error code: ' . $_FILES[$fieldName]['error'];
            return $existing;
        }

        $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            $this->uploadError = 'Only jpg, jpeg, png, gif, webp files are allowed.';
            return $existing;
        }

        if ($_FILES[$fieldName]['size'] > 5 * 1024 * 1024) {
            $this->uploadError = 'File too large. Maximum size is 5MB.';
            return $existing;
        }

        // XAMPP এ absolute path ব্যবহার করো
        $uploadDir = realpath(__DIR__ . '/../assets/images/restaurants');
        if ($uploadDir === false) {
            // folder না থাকলে তৈরি করো
            mkdir(__DIR__ . '/../assets/images/restaurants', 0777, true);
            $uploadDir = realpath(__DIR__ . '/../assets/images/restaurants');
        }

        if (!is_writable($uploadDir)) {
            $this->uploadError = 'Upload folder is not writable: ' . $uploadDir;
            return $existing;
        }

        $filename = $prefix . '_' . time() . '_' . random_int(1000, 9999) . '.' . $ext;
        $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $fullPath)) {
            return 'restaurants/' . $filename;
        }

        $this->uploadError = 'Failed to save file. Path: ' . $fullPath;
        return $existing;
    }

    public function dashboard(): void
    {
        $restaurant = $this->currentRestaurant();
        $model = $this->restaurantModel();
        $analytics = $model->analytics((int) $restaurant['id']);
        $orders = $model->orders((int) $restaurant['id'], true);
        $categories = $model->categories((int) $restaurant['id']);
        $items = $model->items((int) $restaurant['id']);

        $groupedOrders = [];
        foreach ($orders as $order) {
            $groupedOrders[$order['status']][] = $order;
        }

        $this->render('restaurant/dashboard.php', [
            'restaurant' => $restaurant,
            'totalItems' => count($items),
            'totalCategories' => count($categories),
            'activeOrders' => $orders,
            'groupedOrders' => $groupedOrders,
            'summary' => $analytics['summary'],
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function profile(): void
    {
        $restaurant = $this->currentRestaurant();
        $message = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $cuisineType = trim($_POST['cuisine_type'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $openingHours = trim($_POST['opening_hours'] ?? '');
            $deliveryRadius = (float) ($_POST['delivery_radius_km'] ?? 0);
            $isOpen = isset($_POST['is_open']) ? 1 : 0;
            $logoPath = $this->uploadImage('logo', 'restaurant_' . (int) $restaurant['id'], $restaurant['logo_path'] ?? null);

            if ($this->uploadError) {
                $error = $this->uploadError;
            } elseif ($name === '' || $cuisineType === '' || $address === '' || $city === '') {
                $error = 'Restaurant name, cuisine type, address, and city are required.';
            } elseif ($deliveryRadius <= 0) {
                $error = 'Delivery radius must be greater than 0.';
            } else {
                $this->restaurantModel()->updateProfile((int) $restaurant['id'], [
                    'name' => $name,
                    'description' => $description,
                    'cuisine_type' => $cuisineType,
                    'address' => $address,
                    'city' => $city,
                    'logo_path' => $logoPath,
                    'opening_hours' => $openingHours,
                    'delivery_radius_km' => $deliveryRadius,
                    'is_open' => $isOpen,
                ]);
                $message = 'Restaurant profile updated.';
                $restaurant = $this->currentRestaurant();
            }
        }

        $this->render('restaurant/profile.php', [
            'restaurant' => $restaurant,
            'message' => $message,
            'error' => $error,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function menu(): void
    {
        $restaurant = $this->currentRestaurant();
        $restaurantId = (int) $restaurant['id'];
        $model = $this->restaurantModel();
        $message = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'add_category' || $action === 'update_category') {
                $name = trim($_POST['category_name'] ?? '');
                $displayOrder = (int) ($_POST['display_order'] ?? 0);

                if (empty($name)) {
                    $error = 'Category name is required.';
                } elseif ($displayOrder < 0) {
                    $error = 'Display order must be 0 or greater.';
                } elseif ($action === 'add_category') {
                    if ($model->findCategoryByName($restaurantId, $name)) {
                        $error = 'This category already exists.';
                    } else {
                        $model->createCategory($restaurantId, $name, $displayOrder);
                        $message = 'Category created.';
                    }
                } else {
                    $categoryId = (int) ($_POST['category_id'] ?? 0);
                    if ($categoryId <= 0 || !$model->findCategory($restaurantId, $categoryId)) {
                        $error = 'Select a valid category to update.';
                    } else {
                        $model->updateCategory($restaurantId, $categoryId, $name, $displayOrder);
                        $message = 'Category updated.';
                    }
                }
            } elseif ($action === 'delete_category') {
                $categoryId = (int) ($_POST['category_id'] ?? 0);
                if ($categoryId <= 0 || !$model->findCategory($restaurantId, $categoryId)) {
                    $error = 'Select a valid category to delete.';
                } else {
                    $model->deleteCategory($restaurantId, $categoryId);
                    $message = 'Category deleted.';
                }
            } elseif ($action === 'add_item' || $action === 'update_item') {
                $itemId = (int) ($_POST['item_id'] ?? 0);
                $existing = $itemId > 0 ? $model->findItem($restaurantId, $itemId) : null;
                $categoryId = (int) ($_POST['category_id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $price = (float) ($_POST['price'] ?? 0);
                $isAvailable = isset($_POST['is_available']) ? 1 : 0;

                // আগের image path রাখো যদি নতুন upload না হয়
                $existingImage = $existing['image_path'] ?? null;
                $imagePath = $this->uploadImage('image', 'menu_' . $restaurantId, $existingImage);

                if ($this->uploadError) {
                    $error = $this->uploadError;
                } elseif ($categoryId <= 0 || !$model->findCategory($restaurantId, $categoryId)) {
                    $error = 'Please select a valid category.';
                } elseif ($name === '' || $price <= 0) {
                    $error = 'Item name and a price greater than 0 are required.';
                } else {
                    $data = [
                        'category_id'  => $categoryId,
                        'name'         => $name,
                        'description'  => $description,
                        'price'        => $price,
                        'image_path'   => $imagePath ?: 'placeholder.png',
                        'is_available' => $isAvailable,
                    ];

                    if ($action === 'add_item') {
                        $model->createItem($restaurantId, $data);
                        $message = 'Menu item created.';
                    } else {
                        $model->updateItem($restaurantId, $itemId, $data);
                        $message = 'Menu item updated.';
                    }
                }
            } elseif ($action === 'delete_item') {
                $itemId = (int) ($_POST['item_id'] ?? 0);
                $model->deleteItem($restaurantId, $itemId);
                $message = 'Menu item deleted.';
            } elseif ($action === 'add_discount') {
                $menuItemId = (int) ($_POST['menu_item_id'] ?? 0);
                $discountPct = (float) ($_POST['discount_pct'] ?? 0);
                $validFrom = trim($_POST['valid_from'] ?? '');
                $validUntil = trim($_POST['valid_until'] ?? '');
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                if (!$model->findItem($restaurantId, $menuItemId)) {
                    $error = 'Select a valid menu item for the discount.';
                } elseif ($discountPct <= 0 || $discountPct >= 100) {
                    $error = 'Discount percentage must be between 1 and 99.';
                } elseif ($validFrom === '' || $validUntil === '' || strtotime($validUntil) <= strtotime($validFrom)) {
                    $error = 'Valid until must be later than valid from.';
                } else {
                    $model->createDiscount($restaurantId, $menuItemId, $discountPct, $validFrom, $validUntil, $isActive);
                    $message = 'Discount campaign created.';
                }
            } elseif ($action === 'toggle_discount') {
                $discountId = (int) ($_POST['discount_id'] ?? 0);
                $isActive = (int) ($_POST['is_active'] ?? 0);
                $model->setDiscountStatus($restaurantId, $discountId, $isActive);
                $message = 'Discount status updated.';
            } elseif ($action === 'delete_discount') {
                $discountId = (int) ($_POST['discount_id'] ?? 0);
                $model->deleteDiscount($restaurantId, $discountId);
                $message = 'Discount deleted.';
            }
        }

        $this->render('restaurant/menu.php', [
            'restaurant' => $restaurant,
            'categories' => $model->categories($restaurantId),
            'items' => $model->items($restaurantId),
            'discounts' => $model->discounts($restaurantId),
            'message' => $message,
            'error' => $error,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function orders(): void
    {
        $restaurant = $this->currentRestaurant();
        $restaurantId = (int) $restaurant['id'];
        $model = $this->restaurantModel();
        $message = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = (int) ($_POST['order_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $allowed = ['accepted', 'preparing', 'ready', 'cancelled'];
            if ($orderId <= 0 || !in_array($status, $allowed, true)) {
                $error = 'Select a valid order status.';
            } else {
                $model->updateOrderStatus($restaurantId, $orderId, $status);
                $message = 'Order status updated.';
            }
        }

        $orders = $model->orders($restaurantId);
        foreach ($orders as &$order) {
            $order['items'] = $model->orderItems((int) $order['id']);
        }
        unset($order);

        $this->render('restaurant/orders.php', [
            'restaurant' => $restaurant,
            'orders' => $orders,
            'message' => $message,
            'error' => $error,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function ordersFeed(): void
    {
        $restaurant = $this->currentRestaurant();
        $model = $this->restaurantModel();
        $orders = $model->orders((int) $restaurant['id'], true);
        foreach ($orders as &$order) {
            $order['items'] = $model->orderItems((int) $order['id']);
        }
        unset($order);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'orders' => $orders]);
    }

    public function reviews(): void
    {
        $restaurant = $this->currentRestaurant();
        $restaurantId = (int) $restaurant['id'];
        $model = $this->restaurantModel();
        $message = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reviewId = (int) ($_POST['review_id'] ?? 0);
            $reply = trim($_POST['manager_reply'] ?? '');
            if ($reviewId <= 0 || $reply === '') {
                $error = 'Reply cannot be empty.';
            } else {
                $model->replyToReview($restaurantId, $reviewId, $reply);
                $message = 'Reply posted.';
            }
        }

        $this->render('restaurant/reviews.php', [
            'restaurant' => $restaurant,
            'reviews' => $model->reviews($restaurantId),
            'message' => $message,
            'error' => $error,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function analytics(): void
    {
        $restaurant = $this->currentRestaurant();
        $analytics = $this->restaurantModel()->analytics((int) $restaurant['id']);

        $this->render('restaurant/analytics.php', [
            'restaurant' => $restaurant,
            'summary' => $analytics['summary'],
            'ordersByDay' => $analytics['ordersByDay'],
            'ordersByWeek' => $analytics['ordersByWeek'],
            'ordersByMonth' => $analytics['ordersByMonth'],
            'topItems' => $analytics['topItems'],
            'discountPerformance' => $analytics['discountPerformance'],
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function complaints(): void
    {
        $restaurant = $this->currentRestaurant();
        $this->render('restaurant/complaints.php', [
            'restaurant' => $restaurant,
            'complaints' => $this->restaurantModel()->complaints((int) $restaurant['id']),
            'currentUser' => getCurrentUser(),
        ]);
    }
}