<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = $_SESSION['cart'];
$productIds = array_keys($cart);
$items = [];
$subtotal = 0;

if (!empty($productIds)) {
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $types = str_repeat('i', count($productIds));
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$productIds);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $id = (int)$row['id'];
        $qty = (int)$cart[$id];
        $row['cart_qty'] = $qty;
        $row['line_total'] = $qty * (float)$row['price'];
        $items[] = $row;
        $subtotal += $row['line_total'];
    }
    $stmt->close();
}

$cartCount = array_sum($cart);
$cartEmpty = empty($items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - ClassKart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F5F5F5;
            color: #333;
        }

        header {
            background-color: #FFFFFF;
            padding: 1rem 5%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo img {
            height: 50px;
            width: auto;
            object-fit: contain;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2.5rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #0A5033;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .search-bar {
            padding: 0.6rem 1.2rem;
            border: 1px solid #E0E0E0;
            border-radius: 25px;
            outline: none;
            width: 250px;
            transition: border 0.3s;
        }

        .search-bar:focus {
            border-color: #0A5033;
        }

        .cart-icon {
            position: relative;
            cursor: pointer;
            font-size: 1.5rem;
            text-decoration: none;
            color: #333;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #0A5033;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .checkout-hero {
            background: linear-gradient(135deg, #0A5033 0%, #0d6b45 100%);
            color: white;
            padding: 3rem 5%;
            text-align: left;
        }

        .checkout-hero-content {
            max-width: 1400px;
            margin: 0 auto;
        }

        .checkout-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .checkout-container {
            max-width: 1400px;
            margin: 3rem auto;
            padding: 0 5%;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 3rem;
        }

        .checkout-form {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .form-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .form-section h2 {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #0A5033;
        }

        .phone-input-group {
            position: relative;
        }

        .phone-prefix {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-weight: 500;
            pointer-events: none;
            margin-top: 0.75rem;
        }

        .phone-input-group input {
            padding-left: 4rem !important;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }

        .payment-method {
            padding: 1.5rem;
            border: 2px solid #E0E0E0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .payment-method.selected {
            border-color: #0A5033;
            background: #f0f7f4;
        }

        .payment-method-info h4 {
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
        }

        .payment-method-info p {
            font-size: 0.85rem;
            color: #666;
        }

        .security-note {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.85rem;
            margin-top: 1rem;
            padding: 0.75rem;
            background: #E8F5E9;
            border-radius: 8px;
        }

        .order-summary {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .order-summary h2 {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .order-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #F0F0F0;
        }

        .order-item:last-of-type {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .item-image-small {
            width: 60px;
            height: 60px;
            background: #F8F8F8;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .item-image-small img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 0.3rem;
        }

        .item-info {
            flex: 1;
        }

        .item-info h4 {
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 0.3rem;
            font-weight: 500;
        }

        .item-qty {
            font-size: 0.85rem;
            color: #666;
        }

        .item-price {
            font-weight: 600;
            color: #0A5033;
        }

        .summary-divider {
            height: 1px;
            background: #E0E0E0;
            margin: 1.5rem 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.5rem 0;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .total-value {
            color: #0A5033;
            font-size: 1.4rem;
        }

        .btn-place-order {
            width: 100%;
            background: #0A5033;
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-place-order:hover:not(:disabled) {
            background: #084028;
            transform: translateY(-2px);
        }

        .btn-place-order:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .empty-state {
            padding: 1rem;
            background: #FFF8E1;
            color: #8a6d3b;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border: 1px solid #F5E2A3;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            text-align: center;
            animation: slideIn 0.3s;
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #0A5033;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 1rem auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .success-icon {
            font-size: 4rem;
            color: #0A5033;
            margin-bottom: 1rem;
        }

        .error-icon {
            font-size: 4rem;
            color: #d32f2f;
            margin-bottom: 1rem;
        }

        footer {
            background-color: #0A5033;
            color: white;
            padding: 4rem 5% 2rem;
            margin-top: 5rem;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-section h3 {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: #FFFFFF;
        }

        .footer-section p {
            line-height: 1.8;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-links a:hover {
            color: #FFFFFF;
            padding-left: 5px;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0.9;
        }

        @media (max-width: 1024px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .search-bar {
                width: 150px;
            }

            .checkout-hero h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .search-bar {
                display: none;
            }

            .checkout-hero h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <img src="images/logo.png" alt="ClassKart Logo">
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="About.php">About</a></li>
                <li><a href="Contact.php">Contact</a></li>
            </ul>
            <div class="nav-right">
                <input type="text" class="search-bar" placeholder="Search products...">
                <a href="Cart.php" class="cart-icon">
                    🛒
                    <span class="cart-count"><?php echo (int)$cartCount; ?></span>
                </a>
                <a href="<?php echo isset($_SESSION['user_id']) ? 'logout.php' : 'Login.php'; ?>">
                    <?php echo isset($_SESSION['user_id']) ? 'Logout' : 'Login'; ?>
                </a>
            </div>
        </nav>
    </header>

    <section class="checkout-hero">
        <div class="checkout-hero-content">
            <h1>Checkout</h1>
            <p>Complete your order with Mobile Money</p>
        </div>
    </section>

    <div class="checkout-container">
        <div class="checkout-form">
            <?php if ($cartEmpty): ?>
                <div class="empty-state">
                    Your cart is empty. Please add products before proceeding to checkout.
                </div>
            <?php endif; ?>
            
            <form id="checkoutForm">
                <div class="form-section">
                    <h2>Contact Information</h2>
                    <div class="form-group">
                        <label for="fullName">Full Name *</label>
                        <input type="text" id="fullName" name="fullName" required <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                        </div>
                        <div class="form-group phone-input-group">
                            <label for="phone">Mobile Money Number *</label>
                            <span class="phone-prefix">+250</span>
                            <input type="tel" id="phone" name="phone" placeholder="7XXXXXXXX" maxlength="9" required <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Shipping Address</h2>
                    <div class="form-group">
                        <label for="street">Street Address *</label>
                        <input type="text" id="street" name="street" required <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city" required <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label for="state">District *</label>
                            <input type="text" id="state" name="state" placeholder="e.g., Gasabo" required <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Payment Method</h2>
                    <div class="payment-methods">
                        <div class="payment-method selected">
                            <svg width="60" height="60" viewBox="0 0 60 60" fill="none">
                                <circle cx="30" cy="30" r="28" fill="#FFCC00"/>
                                <path d="M20 25L30 35L40 25" stroke="#000" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            <div class="payment-method-info">
                                <h4>MTN Mobile Money</h4>
                                <p>Pay with your MTN MoMo </p>
                            </div>
                        </div>
                    </div>
                   
                </div>

                <button type="submit" class="btn-place-order" <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                    Pay <?php echo number_format($subtotal, 0); ?> Frw with Mobile Money
                </button>
            </form>
        </div>

        <div class="order-summary">
            <h2>Your Order</h2>
            <?php if (!$cartEmpty): ?>
                <?php foreach ($items as $item): ?>
                    <div class="order-item">
                        <div class="item-image-small">
                            <?php if (!empty($item['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                <img src="images/unknownbook.jpg" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="item-info">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <div class="item-qty">Qty: <?php echo (int)$item['cart_qty']; ?></div>
                        </div>
                        <div class="item-price"><?php echo number_format($item['line_total'], 0); ?> Frw</div>
                    </div>
                <?php endforeach; ?>

                <div class="summary-divider"></div>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo number_format($subtotal, 0); ?> Frw</span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span style="color: #0A5033; font-weight: 600;">Free</span>
                </div>
                <div class="summary-divider"></div>
                <div class="summary-total">
                    <span>Total</span>
                    <span class="total-value"><?php echo number_format($subtotal, 0); ?> Frw</span>
                </div>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div id="modalContent"></div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>ClassKart</h3>
                <p>Empowering Learning Through Access. Quality educational materials for students, teachers, and parents.</p>
            </div>

            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="index.html">Home</a></li>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="About.html">About Us</a></li>
                    <li><a href="Contact.html">Contact</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© 2025 ClassKart. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const cartTotal = <?php echo $subtotal; ?>;

        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('0')) {
                value = value.substring(1);
            }
            if (value.startsWith('250')) {
                value = value.substring(3);
            }
            e.target.value = value.substring(0, 9);
        });

        // Form submission
        document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const phone = '250' + formData.get('phone').replace(/\D/g, '');
            
            // Validate phone number
            if (phone.length !== 12) {
                alert('Please enter a valid 9-digit phone number');
                return;
            }

            // Show loading modal
            showModal('loading');

            try {
                const response = await fetch('process_momo_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        fullName: formData.get('fullName'),
                        email: formData.get('email'),
                        phone: phone,
                        amount: cartTotal,
                        street: formData.get('street'),
                        city: formData.get('city'),
                        state: formData.get('state')
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showModal('success', result);
                    
                    // Redirect after 3 seconds
                    setTimeout(() => {
                        window.location.href = 'order_confirmation.php?order=' + result.orderId;
                    }, 3000);
                } else {
                    showModal('error', result);
                }
            } catch (error) {
                showModal('error', { message: 'Connection error. Please try again.' });
            }
        });

        function showModal(type, data = {}) {
            const modal = document.getElementById('paymentModal');
            const content = document.getElementById('modalContent');
            
            if (type === 'loading') {
                content.innerHTML = `
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📱</div>
                    <h2>Processing Payment</h2>
                    <div class="spinner"></div>
                    <p>Please check your phone to approve the payment...</p>
                `;
            } else if (type === 'success') {
                content.innerHTML = `
                    <div class="success-icon">✓</div>
                    <h2>Payment Request Sent!</h2>
                    <p>Please check your phone and enter your PIN to complete the payment.</p>
                    <p style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
                        Order ID: ${data.orderId || 'N/A'}
                    </p>
                    <p style="margin-top: 0.5rem; color: #0A5033; font-weight: 600;">
                        Redirecting to confirmation page...
                    </p>
                `;
            } else if (type === 'error') {
                content.innerHTML = `
                    <div class="error-icon">✕</div>
                    <h2>Payment Failed</h2>
                    <p>${data.message || 'An error occurred. Please try again.'}</p>
                    <button onclick="closeModal()" style="margin-top: 1.5rem; padding: 0.8rem 2rem; background: #0A5033; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        Try Again
                    </button>
                `;
            }
            
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('paymentModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>


