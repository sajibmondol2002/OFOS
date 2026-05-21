<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/inc/functions.php';

// If user is logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user['role'] === 'admin') {
        redirect('public/index.php?route=admin');
    } elseif ($user['role'] === 'restaurant_manager') {
        redirect('public/index.php?route=restaurant');
    } elseif ($user['role'] === 'delivery_man') {
        redirect('public/index.php?route=delivery');
    } else {
        redirect('public/index.php?route=home');
    }
}

// Redirect to unified auth page
redirect('public/index.php?route=auth&action=unified');
?>
