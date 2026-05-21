<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/User.php';

class AdminController extends Controller
{
    public function dashboard(): void
    {
        requireAdmin();
        $productModel = new Product();
        $categoryModel = new Category();
        $orderModel = new Order();

        $totalProducts = count($productModel->all());
        $totalCategories = count($categoryModel->all());
        $totalOrders = count($orderModel->all());
        $recentOrders = $orderModel->all();

        // Platform-wide admin metrics
        $rows = db_fetch_one("SELECT COUNT(*) AS cnt FROM users");
        $totalRegisteredUsers = (int) ($rows['cnt'] ?? 0);

        $rows = db_fetch_one("SELECT COUNT(*) AS cnt FROM users WHERE role = 'restaurant_manager' AND status = 'active'");
        $totalActiveRestaurants = (int) ($rows['cnt'] ?? 0);

        $rows = db_fetch_one("SELECT COUNT(*) AS cnt FROM orders WHERE DATE(created_at) = CURDATE()");
        $totalOrdersToday = (int) ($rows['cnt'] ?? 0);

        $rows = db_fetch_one("SELECT COUNT(*) AS cnt FROM users WHERE role = 'delivery_man' AND COALESCE(is_available,0) = 1");
        $totalActiveDeliveryAgents = (int) ($rows['cnt'] ?? 0);

        $this->render('admin/dashboard.php', [
            'totalProducts' => $totalProducts,
            'totalCategories' => $totalCategories,
            'totalOrders' => $totalOrders,
            'recentOrders' => array_slice($recentOrders, 0, 8),
            'totalRegisteredUsers' => $totalRegisteredUsers,
            'totalActiveRestaurants' => $totalActiveRestaurants,
            'totalOrdersToday' => $totalOrdersToday,
            'totalActiveDeliveryAgents' => $totalActiveDeliveryAgents,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function orders(): void
    {
        requireAdmin();
        $orderModel = new Order();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
            $status = $_POST['status'] ?? 'pending';
            if ($orderId > 0) {
                $orderModel->updateStatus($orderId, $status);
            }
        }

        $orders = $orderModel->all();
        $this->render('admin/orders.php', [
            'orders' => $orders,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function products(): void
    {
        requireAdmin();
        $message = '';
        $error = '';
        $categoryModel = new Category();
        $productModel = new Product();

        // DELETE handler — নতুন যোগ করা হয়েছে
        if (isset($_GET['delete'])) {
            $deleteId = (int) $_GET['delete'];
            if ($deleteId > 0) {
                $productModel->delete($deleteId);
                $this->redirect('index.php?route=admin&action=products');
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = (float) ($_POST['price'] ?? 0);
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $status = $_POST['status'] ?? 'active';
            $image = trim($_POST['image'] ?? 'placeholder.png');

            if ($name === '' || $price <= 0 || $categoryId <= 0) {
                $error = 'Name, category and price are required.';
            } else {
                $productModel->create($categoryId, $name, $description, $price, $image, $status);
                $message = 'Product created successfully.';
            }
        }

        $products = $productModel->allForAdmin();
        $categories = $categoryModel->all();

        $this->render('admin/products.php', [
            'products' => $products,
            'categories' => $categories,
            'message' => $message,
            'error' => $error,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function categories(): void
    {
        requireAdmin();
        $message = '';
        $error = '';
        $categoryModel = new Category();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            if ($name === '') {
                $error = 'Category name is required.';
            } else {
                $categoryModel->create($name, $description);
                $message = 'Category created successfully.';
            }
        }

        if (isset($_GET['delete'])) {
            $deleteId = (int) $_GET['delete'];
            if ($deleteId > 0) {
                $categoryModel->delete($deleteId);
                $this->redirect('index.php?route=admin&action=categories');
            }
        }

        $categories = $categoryModel->all();

        $this->render('admin/categories.php', [
            'categories' => $categories,
            'message' => $message,
            'error' => $error,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function users(): void
    {
        requireAdmin();
        $userModel = new User();
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
            $status = ($_POST['status'] ?? 'inactive') === 'active' ? 'active' : 'inactive';
            if ($userId > 0) {
                $userModel->updateStatus($userId, $status);
                $message = 'User status updated successfully.';
            }
        }

        $users = $userModel->all();
        $this->render('admin/users.php', [
            'users' => $users,
            'message' => $message,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function restaurants(): void
    {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['restaurant_id']) && isset($_POST['status'])) {
                $rid = (int) $_POST['restaurant_id'];
                $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
                db_execute('UPDATE users SET status = ?, is_active = ? WHERE id = ?', 'sii', [$status, $status === 'active' ? 1 : 0, $rid]);
                db_execute('UPDATE restaurants SET is_approved = ? WHERE manager_id = ?', 'ii', [$status === 'active' ? 1 : 0, $rid]);
            }
            if (isset($_POST['reject_id'])) {
                $rid = (int) $_POST['reject_id'];
                db_execute('DELETE FROM users WHERE id = ?', 'i', [$rid]);
            }
            $this->redirect('index.php?route=admin&action=restaurants');
        }

        $restaurants = db_fetch_all(
            "SELECT u.id, u.name AS manager_name, u.email, u.status, u.created_at,
                    r.id AS restaurant_id, r.name AS restaurant_name, r.cuisine_type, r.address, r.city, r.is_open, r.is_approved
             FROM users u
             LEFT JOIN restaurants r ON r.manager_id = u.id
             WHERE u.role = 'restaurant_manager'
             ORDER BY u.created_at DESC"
        );
        $this->render('admin/restaurants.php', [
            'restaurants' => $restaurants,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function customers(): void
    {
        requireAdmin();
        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            $like = '%' . $q . '%';
            $users = db_fetch_all('SELECT id, name, email, status, created_at FROM users WHERE role = ? AND (name LIKE ? OR email LIKE ?) ORDER BY created_at DESC', 'sss', ['customer', $like, $like]);
        } else {
            $users = db_fetch_all("SELECT id, name, email, status, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['user_id']) && isset($_POST['status'])) {
                $uid = (int) $_POST['user_id'];
                $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
                db_execute('UPDATE users SET status = ?, is_active = ? WHERE id = ?', 'sii', [$status, $status === 'active' ? 1 : 0, $uid]);
            }
            $query = $q !== '' ? '?q=' . urlencode($q) : '';
            $this->redirect('index.php?route=admin&action=customers' . $query);
        }

        $this->render('admin/customers.php', [
            'users' => $users,
            'q' => $q,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function deliveryAgents(): void
    {
        requireAdmin();
        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            $like = '%' . $q . '%';
            $agents = db_fetch_all('SELECT id, name, email, status, is_available, created_at FROM users WHERE role = ? AND (name LIKE ? OR email LIKE ?) ORDER BY created_at DESC', 'sss', ['delivery_man', $like, $like]);
        } else {
            $agents = db_fetch_all("SELECT id, name, email, status, is_available, created_at FROM users WHERE role = 'delivery_man' ORDER BY created_at DESC");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['agent_id'])) {
                $aid = (int) $_POST['agent_id'];
                if (isset($_POST['status'])) {
                    $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
                    db_execute('UPDATE users SET status = ?, is_active = ? WHERE id = ?', 'sii', [$status, $status === 'active' ? 1 : 0, $aid]);
                }
                if (isset($_POST['is_available'])) {
                    $avail = $_POST['is_available'] === '1' ? 1 : 0;
                    db_execute('UPDATE users SET is_available = ? WHERE id = ?', 'ii', [$avail, $aid]);
                }
            }
            if (isset($_POST['reject_id'])) {
                $rid = (int) $_POST['reject_id'];
                db_execute('DELETE FROM users WHERE id = ?', 'i', [$rid]);
            }
            $query = $q !== '' ? '?q=' . urlencode($q) : '';
            $this->redirect('index.php?route=admin&action=delivery_agents' . $query);
        }

        $this->render('admin/delivery_agents.php', [
            'agents' => $agents,
            'q' => $q,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function complaints(): void
    {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['complaint_id']) && isset($_POST['action'])) {
                $cid = (int) $_POST['complaint_id'];
                $action = $_POST['action'];
                if ($action === 'resolve') {
                    $note = trim($_POST['admin_note'] ?? '');
                    db_execute('UPDATE complaints SET status = ?, admin_note = ?, resolved_by = ?, resolved_at = ? WHERE id = ?', 'ssisi', ['resolved', $note, $_SESSION['user']['id'], date('Y-m-d H:i:s'), $cid]);
                }
            }
            $this->redirect('index.php?route=admin&action=complaints');
        }

        $complaints = db_fetch_all('SELECT c.id, c.order_id, c.subject, c.status, c.created_at, u.name AS customer FROM complaints c JOIN users u ON u.id = c.user_id ORDER BY c.created_at DESC');
        $this->render('admin/complaints.php', [
            'complaints' => $complaints,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function viewComplaint(): void
    {
        requireAdmin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            $this->redirect('index.php?route=admin&action=complaints');
        }

        $complaint = db_fetch_one('SELECT c.*, u.name AS customer, u.email AS customer_email FROM complaints c JOIN users u ON u.id = c.user_id WHERE c.id = ?', 'i', [$id]);
        if (!$complaint) {
            $this->redirect('index.php?route=admin&action=complaints');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? null;
            $note = trim($_POST['admin_note'] ?? '');
            if ($status !== null) {
                $statusValue = in_array($status, ['open', 'in_progress', 'resolved'], true) ? $status : 'open';
                $resolvedBy = $statusValue === 'resolved' ? $_SESSION['user']['id'] : null;
                $resolvedAt = $statusValue === 'resolved' ? date('Y-m-d H:i:s') : null;
                db_execute(
                    'UPDATE complaints SET status = ?, admin_note = ?, resolved_by = ?, resolved_at = ? WHERE id = ?',
                    'ssisi',
                    [$statusValue, $note, $resolvedBy, $resolvedAt, $id]
                );
                $this->redirect('index.php?route=admin&action=complaints');
            }
        }

        $this->render('admin/view_complaint.php', [
            'complaint' => $complaint,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function viewUser(): void
    {
        requireAdmin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            $this->redirect('index.php?route=admin');
        }

        $user = db_fetch_one('SELECT id, name, email, phone, role, status, vehicle_type, profile_picture, is_available, created_at FROM users WHERE id = ?', 'i', [$id]);
        if (!$user) {
            $this->redirect('index.php?route=admin');
        }

        $this->render('admin/view_user.php', [
            'user' => $user,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function featured(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_id'])) {
                $restaurantId = (int) $_POST['add_id'];
                $posRow = db_fetch_one('SELECT MAX(priority) AS maxpos FROM featured_restaurants');
                $pos = (int) ($posRow['maxpos'] ?? 0) + 1;
                db_execute('INSERT IGNORE INTO featured_restaurants (restaurant_id, priority) VALUES (?, ?)', 'ii', [$restaurantId, $pos]);
            }
            if (isset($_POST['remove_id'])) {
                $restaurantId = (int) $_POST['remove_id'];
                db_execute('DELETE FROM featured_restaurants WHERE restaurant_id = ?', 'i', [$restaurantId]);
            }
            if (isset($_POST['positions']) && is_array($_POST['positions'])) {
                foreach ($_POST['positions'] as $id => $position) {
                    db_execute('UPDATE featured_restaurants SET priority = ? WHERE id = ?', 'ii', [(int) $position, (int) $id]);
                }
            }
            $this->redirect('index.php?route=admin&action=featured');
        }

        $featured = db_fetch_all('SELECT fr.id, fr.restaurant_id, fr.priority, r.name, r.logo_path FROM featured_restaurants fr JOIN restaurants r ON r.id = fr.restaurant_id ORDER BY fr.priority ASC');
        $restaurants = db_fetch_all("SELECT id, name FROM restaurants ORDER BY name");
        $this->render('admin/featured_restaurants.php', [
            'featured' => $featured,
            'restaurants' => $restaurants,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function settings(): void
    {
        requireAdmin();
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $commission = trim($_POST['commission_rate'] ?? '');
            $base_fee = trim($_POST['base_delivery_fee'] ?? '');
            $per_km = trim($_POST['per_km_fee'] ?? '');
            $formula = trim($_POST['estimated_time_formula'] ?? '');

            $cols = db_fetch_all("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings'");
            $colNames = array_column($cols, 'COLUMN_NAME');
            $keyCol = in_array('key', $colNames, true) ? 'key' : (in_array('k', $colNames, true) ? 'k' : null);
            $valCol = in_array('value', $colNames, true) ? 'value' : (in_array('v', $colNames, true) ? 'v' : null);
            if ($keyCol === null || $valCol === null) {
                $message = 'Settings table schema not recognised.';
            } else {
                $sql = "INSERT INTO settings (`{$keyCol}`, `{$valCol}`) VALUES (?,?) ON DUPLICATE KEY UPDATE `{$valCol}` = VALUES(`{$valCol}`)";
                db_execute($sql, 'ss', ['commission_rate', $commission]);
                db_execute($sql, 'ss', ['base_delivery_fee', $base_fee]);
                db_execute($sql, 'ss', ['per_km_fee', $per_km]);
                db_execute($sql, 'ss', ['estimated_time_formula', $formula]);
                $message = 'Settings updated.';
            }
        }

        $commission = getSetting('commission_rate');
        $base_fee = getSetting('base_delivery_fee');
        $per_km = getSetting('per_km_fee');
        $formula = getSetting('estimated_time_formula');

        $this->render('admin/settings.php', [
            'commission' => $commission,
            'base_fee' => $base_fee,
            'per_km' => $per_km,
            'formula' => $formula,
            'message' => $message,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function analytics(): void
    {
        requireAdmin();
        $orderModel = new Order();

        $totalRevenueRow = db_fetch_one("SELECT IFNULL(SUM(total_amount),0) AS total FROM orders WHERE status = 'delivered'");
        $totalRevenue = $totalRevenueRow['total'] ?? 0;

        $ordersByStatus = db_fetch_all("SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status");

        $busiestAgents = db_fetch_all(
            "SELECT u.id, u.name, COUNT(*) AS deliveries FROM orders o JOIN users u ON u.id = o.delivery_agent_id WHERE o.delivery_status = 'delivered' GROUP BY o.delivery_agent_id ORDER BY deliveries DESC LIMIT 10"
        );

        $peakHours = db_fetch_all(
            "SELECT HOUR(created_at) AS hour, COUNT(*) AS cnt FROM orders GROUP BY hour ORDER BY cnt DESC LIMIT 6"
        );

        $avgDeliveryRow = db_fetch_one("SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, delivered_at)) AS avg_minutes FROM orders WHERE delivered_at IS NOT NULL");
        $avgDeliveryMins = $avgDeliveryRow['avg_minutes'] ? round((float)$avgDeliveryRow['avg_minutes'],2) : 0;

        $onTimeThreshold = (int)(getSetting('on_time_threshold_minutes') ?? 30);
        $onTimeRow = db_fetch_one("SELECT SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, created_at, delivered_at) <= ? THEN 1 ELSE 0 END) AS ontime, COUNT(*) AS total FROM orders WHERE delivered_at IS NOT NULL", 'i', [$onTimeThreshold]);
        $onTimeCount = (int)($onTimeRow['ontime'] ?? 0);
        $onTimeTotal = (int)($onTimeRow['total'] ?? 0);
        $onTimeRate = $onTimeTotal > 0 ? round($onTimeCount / $onTimeTotal * 100, 2) : 0;

        $failedDeliveriesRow = db_fetch_one("SELECT COUNT(*) AS failed FROM orders WHERE status = 'cancelled'");
        $failedDeliveries = (int)($failedDeliveriesRow['failed'] ?? 0);

        $monthly = db_fetch_all(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS orders, SUM(total_amount) AS revenue, AVG(TIMESTAMPDIFF(MINUTE, created_at, delivered_at)) AS avg_delivery_mins FROM orders GROUP BY month ORDER BY month DESC LIMIT 6"
        );

        $this->render('admin/analytics.php', [
            'totalRevenue' => $totalRevenue,
            'ordersByStatus' => $ordersByStatus,
            'busiestAgents' => $busiestAgents,
            'peakHours' => $peakHours,
            'avgDeliveryMins' => $avgDeliveryMins,
            'onTimeThreshold' => $onTimeThreshold,
            'onTimeRate' => $onTimeRate,
            'failedDeliveries' => $failedDeliveries,
            'monthly' => $monthly,
            'currentUser' => getCurrentUser(),
        ]);
    }
}