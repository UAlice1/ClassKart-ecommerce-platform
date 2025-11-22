<?php
// Ultra Simple Payment Processor
// This version has minimal code to avoid errors

// Start session
session_start();

// Set header for JSON response
header('Content-Type: application/json');

// Enable error logging
ini_set('display_errors', 0); // Don't display errors to user
error_log("Payment script started at " . date('Y-m-d H:i:s'));

try {
    // Try to connect to database
    if (!file_exists('db_connection.php')) {
        throw new Exception('Database connection file not found');
    }
    
    require_once 'db_connection.php';
    
    if (!isset($conn)) {
        throw new Exception('Database connection failed');
    }
    
    
    $input = file_get_contents('php://input');
    error_log("Received input: " . $input);
    
    $data = json_decode($input, true);

    if (!$data) {
        $data = $_POST;
    }
    

    if (empty($data['fullName'])) {
        throw new Exception('Name is required');
    }
    
    if (empty($data['email'])) {
        throw new Exception('Email is required');
    }
    
    if (empty($data['phone'])) {
        throw new Exception('Phone is required');
    }
    
    if (empty($data['amount']) || $data['amount'] <= 0) {
        throw new Exception('Invalid amount');
    }
    
    // Clean the data
    $fullName = trim($data['fullName']);
    $email = trim($data['email']);
    $phone = trim($data['phone']);
    $amount = floatval($data['amount']);
    $street = isset($data['street']) ? trim($data['street']) : '';
    $city = isset($data['city']) ? trim($data['city']) : '';
    $state = isset($data['state']) ? trim($data['state']) : '';
    
    // Generate IDs
    $orderId = 'ORDER_' . date('YmdHis') . '_' . rand(1000, 9999);
    $referenceId = 'REF_' . uniqid() . '_' . time();
    

    $shippingAddress = json_encode([
        'street' => $street,
        'city' => $city,
        'state' => $state
    ]);
    
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    $sql = "INSERT INTO orders (order_id, user_id, customer_name, customer_email, customer_phone, total_amount, payment_status, payment_method, reference_id, shipping_address, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'momo', ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('sisssdss', $orderId, $userId, $fullName, $email, $phone, $amount, $referenceId, $shippingAddress);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create order: ' . $stmt->error);
    }
    
    $stmt->close();
    
    error_log("Order created successfully: " . $orderId);
    
    // Insert order items from cart
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            // Get product details
            $productSql = "SELECT name, price FROM products WHERE id = ?";
            $productStmt = $conn->prepare($productSql);
            $productStmt->bind_param('i', $productId);
            $productStmt->execute();
            $productResult = $productStmt->get_result();
            
            if ($product = $productResult->fetch_assoc()) {
                $productName = $product['name'];
                $price = floatval($product['price']);
                $qty = intval($quantity);
                $subtotal = $price * $qty;
                
                // Insert order item
                $itemSql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
                $itemStmt = $conn->prepare($itemSql);
                $itemStmt->bind_param('sissdd', $orderId, $productId, $productName, $qty, $price, $subtotal);
                $itemStmt->execute();
                $itemStmt->close();
            }
            
            $productStmt->close();
        }
    }
    
    // Log the payment attempt
    $logSql = "INSERT INTO payment_logs (reference_id, order_id, phone_number, amount, status, created_at) VALUES (?, ?, ?, ?, 'initiated', NOW())";
    $logStmt = $conn->prepare($logSql);
    $logStmt->bind_param('sssd', $referenceId, $orderId, $phone, $amount);
    $logStmt->execute();
    $logStmt->close();
    
    // Return success
    $response = [
        'success' => true,
        'message' => 'Payment request sent! Please check your phone to complete payment.',
        'orderId' => $orderId,
        'referenceId' => $referenceId,
        'phone' => substr($phone, 0, 6) . 'XXX' . substr($phone, -2),
        'amount' => $amount
    ];
    
    error_log("Success response: " . json_encode($response));
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log the error
    error_log("Payment error: " . $e->getMessage());
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>