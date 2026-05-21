<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';

class DeliveryController extends Controller
{
    public function dashboard(): void
    {
        requireDeliveryMan();

        $orderModel = new Order();
        $userModel  = new User();

        $agentId            = $_SESSION['user']['id'];
        $availableOrders    = $orderModel->availableAssignments();
        $myActiveDeliveries = $orderModel->allAssignedToAgent($agentId);
        $completedOrders    = $orderModel->allDeliveredForAgent($agentId);
        $earnings           = $orderModel->calculateEarnings($agentId);
        $agent              = $userModel->findById($agentId);

        $this->render('delivery/dashboard.php', [
            'availableOrders'    => $availableOrders,
            'myActiveDeliveries' => $myActiveDeliveries,
            'completedOrders'    => $completedOrders,
            'earnings'           => $earnings,
            'agent'              => $agent,
            'currentUser'        => getCurrentUser(),
        ]);
    }

    public function assignments(): void
    {
        requireDeliveryMan();

        $orderModel = new Order();
        $agentId    = $_SESSION['user']['id'];
        $message    = '';
        $error      = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
            $action  = $_POST['action'] ?? '';

            if ($orderId > 0) {
                if ($action === 'accept') {
                    $orderModel->assignToAgent($orderId, $agentId);
                    $message = 'Order accepted! You can now pick it up.';
                } elseif ($action === 'decline') {
                    $orderModel->unassignAgent($orderId, $agentId);
                    $message = 'Order declined.';
                } elseif ($action === 'update_status') {
                    $allowed        = ['pending', 'picked_up', 'on_the_way', 'delivered'];
                    $deliveryStatus = $_POST['delivery_status'] ?? 'pending';
                    if (in_array($deliveryStatus, $allowed)) {
                        $orderModel->updateDeliveryStatus($orderId, $deliveryStatus);
                        $message = 'Delivery status updated successfully.';
                    } else {
                        $error = 'Invalid status selected.';
                    }
                }
            }
        }

        $availableOrders = $orderModel->availableAssignments();
        $myAssignments   = $orderModel->allAssignedToAgent($agentId);

        $this->render('delivery/assignments.php', [
            'availableOrders' => $availableOrders,
            'myAssignments'   => $myAssignments,
            'message'         => $message,
            'error'           => $error,
            'currentUser'     => getCurrentUser(),
        ]);
    }

    public function history(): void
    {
        requireDeliveryMan();

        $orderModel = new Order();
        $history    = $orderModel->allDeliveredForAgent($_SESSION['user']['id']);

        $this->render('delivery/history.php', [
            'history'     => $history,
            'currentUser' => getCurrentUser(),
        ]);
    }

    public function earnings(): void
    {
        requireDeliveryMan();

        $orderModel       = new Order();
        $agentId          = $_SESSION['user']['id'];
        $earnings         = $orderModel->calculateEarnings($agentId);
        $summary          = $orderModel->earningsSummary($agentId);
        $recentDeliveries = $orderModel->allDeliveredForAgent($agentId);

        $this->render('delivery/earnings.php', [
            'earnings'         => $earnings,
            'summary'          => $summary,
            'recentDeliveries' => $recentDeliveries,
            'currentUser'      => getCurrentUser(),
        ]);
    }

    public function profile(): void
    {
        requireDeliveryMan();

        $userModel = new User();
        $agentId   = $_SESSION['user']['id'];
        $agent     = $userModel->findById($agentId);
        $message   = '';
        $error     = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'toggle_availability') {
                $current  = (int) ($agent['is_available'] ?? 1);
                $newValue = $current === 1 ? 0 : 1;
                $userModel->updateAvailability($agentId, $newValue);
                $message = $newValue ? 'You are now Online.' : 'You are now Offline.';
                $agent   = $userModel->findById($agentId);

            } elseif ($action === 'update_profile') {
                $name        = trim($_POST['name'] ?? '');
                $phone       = trim($_POST['phone'] ?? '');
                $vehicleType = trim($_POST['vehicle_type'] ?? '');

                if ($name === '') {
                    $error = 'Name is required.';
                } else {
                    $picturePath = $agent['profile_picture'] ?? null;

                    if (!empty($_FILES['profile_picture']['name'])) {
                        $uploadDir = __DIR__ . '/../assets/images/profiles/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        $ext     = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        if (!in_array($ext, $allowed)) {
                            $error = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
                        } elseif ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
                            $error = 'Image must be under 2MB.';
                        } else {
                            $filename    = 'agent_' . $agentId . '_' . time() . '.' . $ext;
                            $destination = $uploadDir . $filename;
                            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                                $picturePath = 'assets/images/profiles/' . $filename;
                            } else {
                                $error = 'Failed to upload image. Please try again.';
                            }
                        }
                    }

                    if ($error === '') {
                        $userModel->updateDeliveryProfile($agentId, $name, $phone, $vehicleType, $picturePath);
                        $_SESSION['user']['name'] = $name;
                        $message = 'Profile updated successfully.';
                        $agent   = $userModel->findById($agentId);
                    }
                }
            }
        }

        $this->render('delivery/profile.php', [
            'agent'       => $agent,
            'message'     => $message,
            'error'       => $error,
            'currentUser' => getCurrentUser(),
        ]);
    }
}