<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    if ($productId > 0) {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = 0;
        }
        $_SESSION['cart'][$productId] += 1;
    }
}

$redirect = $_SERVER['HTTP_REFERER'] ?? 'shop.php';
header('Location: ' . $redirect);
exit();






