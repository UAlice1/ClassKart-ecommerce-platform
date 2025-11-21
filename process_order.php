<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Cart.php');
    exit();
}

// Collect form data
$fullName = trim($_POST['fullName'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$street   = trim($_POST['street'] ?? '');
$city     = trim($_POST['city'] ?? '');
$state    = trim($_POST['state'] ?? '');
$zip      = trim($_POST['zipCode'] ?? '');

if ($fullName === '' || $email === '' || $phone === '' || $street === '' || $city === '' || $state === '' || $zip === '') {
    header('Location: Checkout.php');
    exit();
}

// Build address
$address = $street . ', ' . $city . ', ' . $state . ' ' . $zip;

// Load cart items
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header('Location: Cart.php');
    exit();
}

$cart = $_SESSION['cart'];
$productIds = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));
$types = str_repeat('i', count($productIds));

$stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->bind_param($types, ...$productIds);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

if (empty($items)) {
    header('Location: Cart.php');
    exit();
}

// Calculate totals
$totalAmount = 0;
foreach ($items as $item) {
    $id  = (int)$item['id'];
    $qty = (int)$cart[$id];
    $price = (float)$item['price'];
    $totalAmount += $qty * $price;
}

// Insert order (payment is simulated)
$userId = $_SESSION['user_id'] ?? null;

$stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, shipping_address, total_amount, status) VALUES (?,?,?,?,?,?, 'paid')");
$stmt->bind_param(
    "issssd",
    $userId,
    $fullName,
    $email,
    $phone,
    $address,
    $totalAmount
);
$stmt->execute();
$orderId = $stmt->insert_id;
$stmt->close();

// Insert order items
$stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, line_total) VALUES (?,?,?,?,?,?)");

foreach ($items as $item) {
    $id  = (int)$item['id'];
    $qty = (int)$cart[$id];
    $price = (float)$item['price'];
    $lineTotal = $qty * $price;
    $name = $item['name'];

    $stmt->bind_param("iisidd", $orderId, $id, $name, $qty, $price, $lineTotal);
    $stmt->execute();
}
$stmt->close();

// Clear cart
unset($_SESSION['cart']);

header('Location: order_success.php?order_id=' . $orderId);
exit();


