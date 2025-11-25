<?php
session_start();
require_once 'db_connection.php';
require_once 'includes/language_helper.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Check if cart is not empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['error'] = 'Your cart is empty';
    header('Location: cart.php');
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $transaction_id = $_POST['transaction_id'] ?? '';
    
    // Validate input
    $errors = [];
    
    if (empty($payment_method)) {
        $errors[] = 'Please select a payment method';
    }
    
    if ($payment_method === 'momo' && empty($phone_number)) {
        $errors[] = 'Phone number is required for mobile money payment';
    }
    
    if (empty($transaction_id)) {
        $errors[] = 'Transaction ID is required';
    }
    
    if (empty($errors)) {
        // Calculate total amount
        $total_amount = 0;
        $product_ids = array_keys($_SESSION['cart']);
        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
        $stmt = $conn->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
        $stmt->execute();
        $products_result = $stmt->get_result();
        
        $products = [];
        while ($product = $products_result->fetch_assoc()) {
            $product['quantity'] = $_SESSION['cart'][$product['id']];
            $products[] = $product;
            $total_amount += $product['price'] * $product['quantity'];
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $order_sql = "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, shipping_address, total_amount, status, payment_method) 
                         VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)";
            $order_stmt = $conn->prepare($order_sql);
            
            // For simplicity, using user's email as shipping address
            $shipping_address = $user['email']; 
            
            $order_stmt->bind_param("issssss", 
                $user_id,
                $user['full_name'],
                $user['email'],
                $user['phone'],
                $shipping_address,
                $total_amount,
                $payment_method
            );
            
            $order_stmt->execute();
            $order_id = $conn->insert_id;
            
            // Add order items
            $order_item_sql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, line_total) 
                              VALUES (?, ?, ?, ?, ?, ?)";
            $order_item_stmt = $conn->prepare($order_item_sql);
            
            foreach ($products as $product) {
                $line_total = $product['price'] * $product['quantity'];
                $order_item_stmt->bind_param("iisidd", 
                    $order_id,
                    $product['id'],
                    $product['name'],
                    $product['quantity'],
                    $product['price'],
                    $line_total
                );
                $order_item_stmt->execute();
            }
            
            // Record payment
            $payment_sql = "INSERT INTO payments (order_id, amount, payment_method, transaction_id, phone_number, status) 
                           VALUES (?, ?, ?, ?, ?, 'pending')";
            $payment_stmt = $conn->prepare($payment_sql);
            $payment_stmt->bind_param("idsss", $order_id, $total_amount, $payment_method, $transaction_id, $phone_number);
            $payment_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Clear cart
            unset($_SESSION['cart']);
            
            // Redirect to success page
            header('Location: order_success.php?order_id=' . $order_id);
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = 'An error occurred while processing your order. Please try again.';
            error_log('Order processing error: ' . $e->getMessage());
        }
    }
}

// If we get here, there were errors or it's a GET request
// Calculate cart total for display
$cart_total = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    if (!empty($product_ids)) {
        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
        $stmt = $conn->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
        $stmt->execute();
        $products_result = $stmt->get_result();
        
        while ($product = $products_result->fetch_assoc()) {
            $quantity = $_SESSION['cart'][$product['id']];
            $cart_total += $product['price'] * $quantity;
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <h1><?php _e('payment_method'); ?></h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><?php _e('payment_details'); ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" id="paymentForm">
                        <div class="form-group mb-4">
                            <label for="payment_method" class="form-label"><?php _e('payment_method'); ?></label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value=""><?php _e('select_payment_method'); ?></option>
                                <option value="momo" <?php echo ($_POST['payment_method'] ?? '') === 'momo' ? 'selected' : ''; ?>>
                                    <?php _e('momo_payment'); ?>
                                </option>
                                <option value="card" <?php echo ($_POST['payment_method'] ?? '') === 'card' ? 'selected' : ''; ?>>
                                    Credit/Debit Card
                                </option>
                            </select>
                        </div>
                        
                        <div id="momoDetails" class="payment-method-details" style="display: none;">
                            <div class="alert alert-info">
                                <p><strong><?php _e('momo_instructions'); ?></strong></p>
                                <p>Number: <strong>0790038006</strong></p>
                                <p>Name: <strong>ClassKart</strong></p>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="phone_number" class="form-label"><?php _e('phone_number'); ?></label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                                       placeholder="e.g., 0781234567" value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="transaction_id" class="form-label"><?php _e('transaction_id'); ?></label>
                            <input type="text" class="form-control" id="transaction_id" name="transaction_id" 
                                   placeholder="Enter transaction ID" required 
                                   value="<?php echo htmlspecialchars($_POST['transaction_id'] ?? ''); ?>">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <?php _e('submit_payment'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3><?php _e('order_summary'); ?></h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span><?php _e('subtotal'); ?>:</span>
                        <span>RWF <?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><?php _e('shipping'); ?>:</span>
                        <span>RWF 0.00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span><?php _e('total'); ?>:</span>
                        <span>RWF <?php echo number_format($cart_total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .payment-method-details {
        padding: 15px;
        margin-bottom: 20px;
        background-color: #f8f9fa;
        border-radius: 5px;
        border-left: 4px solid #0A5033;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border: 1px solid transparent;
        border-radius: 0.25rem;
    }
    
    .alert-danger {
        color: #842029;
        background-color: #f8d7da;
        border-color: #f5c2c7;
    }
    
    .alert-info {
        color: #055160;
        background-color: #cff4fc;
        border-color: #b6effb;
    }
    
    .card {
        margin-bottom: 2rem;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .card-header {
        background-color: #f8f9fa;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #dee2e6;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .btn-primary {
        background-color: #0A5033;
        border-color: #0A5033;
    }
    
    .btn-primary:hover {
        background-color: #08442b;
        border-color: #073d26;
    }
</style>

<script>
    // Show/hide payment method details
    document.getElementById('payment_method').addEventListener('change', function() {
        const momoDetails = document.getElementById('momoDetails');
        momoDetails.style.display = this.value === 'momo' ? 'block' : 'none';
    });
    
    // Trigger change event on page load in case momo is already selected
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethod = document.getElementById('payment_method');
        if (paymentMethod) {
            paymentMethod.dispatchEvent(new Event('change'));
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
