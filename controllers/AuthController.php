<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Restaurant.php';

class AuthController extends Controller
{
    public function login(): void
    {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if ($email === '' || $password === '') {
                $error = 'Email and password are required.';
            } else {
                $userModel = new User();
                $user = $userModel->findByEmail($email);
                if ($user && password_verify($password, $user['password'])) {
                    if ($user['status'] !== 'active' && $user['role'] !== 'admin') {
                        $error = 'Account is not active. Please wait for admin approval.';
                    } else {
                        $_SESSION['user'] = [
                            'id' => $user['id'],
                            'name' => $user['name'],
                            'email' => $user['email'],
                            'role' => $user['role'],
                        ];
                        if ($user['role'] === 'restaurant_manager') {
                            $this->redirect('index.php?route=restaurant');
                        } elseif ($user['role'] === 'delivery_man') {
                            $this->redirect('index.php?route=delivery');
                        } elseif ($user['role'] === 'admin') {
                            $this->redirect('index.php?route=admin');
                        } else {
                            $this->redirect('index.php?route=menu');
                        }
                    }
                } else {
                    $error = 'Invalid email or password.';
                }
            }
        }

        $this->render('auth/login.php', [
            'error' => $error,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function register(): void
    {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $confirm = trim($_POST['confirm_password'] ?? '');

            if ($name === '' || $email === '' || $password === '' || $confirm === '') {
                $error = 'All fields are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                $userModel = new User();
                if ($userModel->findByEmail($email)) {
                    $error = 'Email is already registered.';
                } else {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $phone = trim($_POST['phone'] ?? '');
                    $userId = $userModel->create($name, $email, $passwordHash, 'customer', $phone, 'active');
                    $_SESSION['user'] = [
                        'id' => $userId,
                        'name' => $name,
                        'email' => $email,
                        'role' => 'customer',
                    ];
                    $this->redirect('index.php?route=menu');
                }
            }
        }

        $this->render('auth/register.php', [
            'error' => $error,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        $this->redirect('index.php?route=auth&action=unified');
    }

    // Display unified login/register page
    public function unified(): void
    {
        if (isLoggedIn()) {
            $user = getCurrentUser();
            if ($user['role'] === 'admin') {
                $this->redirect('index.php?route=admin');
            } elseif ($user['role'] === 'restaurant_manager') {
                $this->redirect('index.php?route=restaurant');
            } elseif ($user['role'] === 'delivery_man') {
                $this->redirect('index.php?route=delivery');
            } else {
                $this->redirect('index.php?route=menu');
            }
        }
        $this->render('auth/unified.php', ['currentUser' => getCurrentUser()]);
    }

    // AJAX-based login
    public function ajaxLogin(): void
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $email = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');
        $role = trim($input['role'] ?? '');

        if ($email === '' || $password === '') {
            echo json_encode(['success' => false, 'message' => 'Email and password are required']);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            return;
        }

        if ($user['role'] !== $role) {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials for this role']);
            return;
        }

        if (!password_verify($password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            return;
        }

        if ($user['status'] !== 'active' && $user['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Account is not active. Please wait for admin approval.']);
            return;
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        echo json_encode(['success' => true, 'message' => 'Login successful', 'role' => $user['role']]);
    }

    // AJAX-based registration
    public function ajaxRegister(): void
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');
        $confirm = trim($input['confirm_password'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $role = trim($input['role'] ?? 'customer');
        $restaurantName = trim($input['restaurant_name'] ?? '');
        $restaurantCuisine = trim($input['restaurant_cuisine'] ?? '');
        $restaurantAddress = trim($input['restaurant_address'] ?? '');
        $restaurantCity = trim($input['restaurant_city'] ?? '');
        $restaurantDescription = trim($input['restaurant_description'] ?? '');
        $restaurantHours = trim($input['restaurant_opening_hours'] ?? '');
        $restaurantRadius = (float) ($input['restaurant_delivery_radius_km'] ?? 5);

        if ($name === '' || $email === '' || $password === '' || $confirm === '') {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
            return;
        }

        if ($password !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            return;
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            return;
        }

        $validRoles = ['customer', 'admin', 'restaurant_manager', 'delivery_man'];
        if (!in_array($role, $validRoles)) {
            echo json_encode(['success' => false, 'message' => 'Invalid role']);
            return;
        }

        if ($role === 'restaurant_manager') {
            if ($restaurantName === '' || $restaurantCuisine === '' || $restaurantAddress === '' || $restaurantCity === '') {
                echo json_encode(['success' => false, 'message' => 'Restaurant name, cuisine type, address, and city are required for manager registration']);
                return;
            }
            if ($restaurantRadius <= 0) {
                echo json_encode(['success' => false, 'message' => 'Delivery radius must be greater than 0']);
                return;
            }
        }

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Email is already registered']);
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $status = $role === 'customer' ? 'active' : 'inactive';
        $userId = $userModel->create($name, $email, $passwordHash, $role, $phone, $status);

        if ($role === 'restaurant_manager') {
            (new Restaurant())->createForManager($userId, [
                'name' => $restaurantName,
                'description' => $restaurantDescription,
                'cuisine_type' => $restaurantCuisine,
                'address' => $restaurantAddress,
                'city' => $restaurantCity,
                'opening_hours' => $restaurantHours,
                'delivery_radius_km' => $restaurantRadius,
                'is_open' => 0,
            ], 0);
        }

        if ($role === 'customer' || $role === 'admin') {
            $_SESSION['user'] = [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => $role,
            ];
        }

        $requiresApproval = $role !== 'customer';
        $message = $requiresApproval
            ? 'Registration submitted. An admin must approve this account before normal login.'
            : 'Registration successful';

        if ($role === 'admin') {
            $message = 'Admin registration submitted. You can open User Management and approve your own account.';
        }

        echo json_encode([
            'success' => true,
            'message' => $message,
            'role' => $role,
            'requires_approval' => $requiresApproval,
            'logged_in' => $role === 'customer' || $role === 'admin',
        ]);
    }
}
