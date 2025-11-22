<?php
session_start();
require_once 'db_connection.php';

$orderId = $_GET['order'] ?? null;

if (!$orderId) {
    header('Location: index.html');
    exit;
}

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param('s', $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: index.html');
    exit;
}

// Fetch order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param('s', $orderId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Clear cart after successful order
if ($order['payment_status'] === 'successful' || $order['payment_status'] === 'pending') {
    $_SESSION['cart'] = [];
}

$shippingAddress = json_decode($order['shipping_address'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - ClassKart</title>
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

        /* Header Styles */
        header {
            background-color: #FFFFFF;
            padding: 1rem 5%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo a {
            display: flex;
            align-items: center;
            text-decoration: none;
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

        .nav-links a:hover,
        .nav-links a.active {
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
            text-decoration: none;
            color: #333;
            font-size: 1.2rem;
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

        .nav-right a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-right a:hover {
            color: #0A5033;
        }

        .menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 5px;
        }

        .menu-toggle span {
            width: 25px;
            height: 3px;
            background-color: #0A5033;
            transition: 0.3s;
        }

        /* Confirmation Container */
        .confirmation-container {
            max-width: 900px;
            margin: 3rem auto;
            padding: 0 5%;
        }

        .status-card {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .status-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }

        .status-icon.success {
            color: #0A5033;
        }

        .status-icon.pending {
            color: #FFA500;
        }

        .status-icon.failed {
            color: #d32f2f;
        }

        .status-card h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .status-card p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .order-id {
            background: #F5F5F5;
            padding: 1rem;
            border-radius: 8px;
            font-family: monospace;
            font-size: 1.2rem;
            margin: 1.5rem 0;
            color: #0A5033;
            font-weight: 600;
        }

        .info-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .info-section h2 {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #0A5033;
            padding-bottom: 0.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.3rem;
            font-weight: 500;
        }

        .info-value {
            font-size: 1rem;
            color: #333;
            font-weight: 500;
        }

        .order-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #F0F0F0;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            background: #F8F8F8;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .item-meta {
            color: #666;
            font-size: 0.9rem;
        }

        .item-price {
            text-align: right;
            font-weight: 600;
            color: #0A5033;
        }

        .total-section {
            background: #F8F8F8;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1.5rem;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
        }

        .total-row.grand-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0A5033;
            padding-top: 1rem;
            border-top: 2px solid #ddd;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }

        .btn-primary {
            background: #0A5033;
            color: white;
        }

        .btn-primary:hover {
            background: #084028;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: white;
            color: #0A5033;
            border: 2px solid #0A5033;
        }

        .btn-secondary:hover {
            background: #f0f7f4;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 1rem;
        }

        .status-badge.pending {
            background: #FFF3CD;
            color: #856404;
        }

        .status-badge.successful {
            background: #D4EDDA;
            color: #155724;
        }

        .status-badge.failed {
            background: #F8D7DA;
            color: #721C24;
        }

        .timeline {
            position: relative;
            padding-left: 2rem;
            margin-top: 1.5rem;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2rem;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #0A5033;
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            left: -1.7rem;
            top: 12px;
            width: 2px;
            height: calc(100% - 12px);
            background: #E0E0E0;
        }

        .timeline-item:last-child::after {
            display: none;
        }

        .timeline-time {
            font-size: 0.85rem;
            color: #666;
        }

        .timeline-title {
            font-weight: 600;
            margin-top: 0.3rem;
        }

        /* Footer Styles */
        footer {
            background-color: #0A5033;
            color: white;
            padding: 4rem 5% 2rem;
            margin-top: 4rem;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .menu-toggle {
                display: flex;
            }

            .nav-links {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: #FFFFFF;
                flex-direction: column;
                gap: 0;
                padding: 1rem 0;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                display: none;
            }

            .nav-links.active {
                display: flex;
            }

            .nav-links li {
                width: 100%;
                text-align: center;
                padding: 1rem;
            }

            .search-bar {
                width: 120px;
                font-size: 0.9rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .status-card {
                padding: 2rem 1rem;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }

        @media (max-width: 480px) {
            .logo img {
                height: 40px;
            }

            .search-bar {
                display: none;
            }
        }

        /* Print Styles */
        @media print {
            header, footer, .action-buttons, .menu-toggle {
                display: none;
            }
            
            body {
                background: white;
            }
            
            .confirmation-container {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="index.html">
                    <img src="images/logo.png" alt="ClassKart Logo">
                </a>
            </div>
            <div class="menu-toggle" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.html">Home</a></li>
                <li><a href="shop.php">Product</a></li>
                <li><a href="About.html">About</a></li>
                <li><a href="Contact.php">Contact</a></li>
            </ul>
            <div class="nav-right">
                <input type="text" class="search-bar" placeholder="Search products...">
                <a href="Cart.php" class="cart-icon">
                    🛒
                    <span class="cart-count">0</span>
                </a>
                <a href="Login.php">Login</a>
            </div>
        </nav>
    </header>

    <div class="confirmation-container">
        <div class="status-card">
            <?php if ($order['payment_status'] === 'successful'): ?>
                <div class="status-icon success">✓</div>
                <h1>Order Confirmed!</h1>
                <p>Thank you for your purchase. Your order has been received.</p>
            <?php elseif ($order['payment_status'] === 'pending'): ?>
                <div class="status-icon pending"></div>
                <h1>Payment Pending</h1>
                <p>Please complete the payment on your phone to confirm your order.</p>
                <p style="font-size: 0.9rem; margin-top: 1rem;">Check your phone (<?php echo substr($order['customer_phone'], 0, 6) . 'XXX' . substr($order['customer_phone'], -2); ?>) for the payment prompt.</p>
            <?php else: ?>
                <div class="status-icon failed">✕</div>
                <h1>Payment Failed</h1>
                <p>Unfortunately, your payment could not be processed.</p>
            <?php endif; ?>
            
            <div class="status-badge <?php echo $order['payment_status']; ?>">
                Status: <?php echo ucfirst($order['payment_status']); ?>
            </div>
            
            <div class="order-id">
                Order ID: <?php echo htmlspecialchars($order['order_id']); ?>
            </div>
        </div>

        <div class="info-section">
            <h2> Order Details</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Customer Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone Number</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Order Date</span>
                    <span class="info-value"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Method</span>
                    <span class="info-value">MTN Mobile Money</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Reference ID</span>
                    <span class="info-value" style="font-family: monospace; font-size: 0.85rem;"><?php echo htmlspecialchars($order['reference_id']); ?></span>
                </div>
            </div>
        </div>

        <?php if ($shippingAddress): ?>
        <div class="info-section">
            <h2>📍 Shipping Address</h2>
            <div style="line-height: 1.8;">
                <?php echo htmlspecialchars($shippingAddress['street'] ?? ''); ?><br>
                <?php echo htmlspecialchars($shippingAddress['city'] ?? ''); ?>, 
                <?php echo htmlspecialchars($shippingAddress['state'] ?? ''); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="info-section">
            <h2> Order Items</h2>
            <?php foreach ($items as $item): ?>
            <div class="order-item">
                <div class="item-image">
                    <span style="font-size: 2rem;"></span>
                </div>
                <div class="item-details">
                    <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                    <div class="item-meta">
                        Quantity: <?php echo $item['quantity']; ?> × 
                        <?php echo number_format($item['price'], 0); ?> Frw
                    </div>
                </div>
                <div class="item-price">
                    <?php echo number_format($item['subtotal'], 0); ?> Frw
                </div>
            </div>
            <?php endforeach; ?>

            <div class="total-section">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span><?php echo number_format($order['total_amount'], 0); ?> Frw</span>
                </div>
                <div class="total-row">
                    <span>Shipping</span>
                    <span style="color: #0A5033; font-weight: 600;">FREE</span>
                </div>
                <div class="total-row grand-total">
                    <span>Total</span>
                    <span><?php echo number_format($order['total_amount'], 0); ?> Frw</span>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h2>📍 Order Timeline</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-time"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></div>
                    <div class="timeline-title">Order Placed</div>
                </div>
                <?php if ($order['payment_status'] === 'successful'): ?>
                <div class="timeline-item">
                    <div class="timeline-time"><?php echo date('F j, Y, g:i a', strtotime($order['updated_at'])); ?></div>
                    <div class="timeline-title">Payment Confirmed</div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-time">Pending</div>
                    <div class="timeline-title">Processing Order</div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-time">Pending</div>
                    <div class="timeline-title">Out for Delivery</div>
                </div>
                <?php elseif ($order['payment_status'] === 'pending'): ?>
                <div class="timeline-item">
                    <div class="timeline-time">In Progress</div>
                    <div class="timeline-title">Waiting for Payment Confirmation</div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="action-buttons">
            <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
            <button onclick="window.print()" class="btn btn-secondary"> Print Receipt</button>
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
                    <li><a href="Contact.php">Contact</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© 2025 ClassKart. All rights reserved. | Alice & Fidele</p>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        }

        // Close mobile menu when clicking on links
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('navLinks').classList.remove('active');
            });
        });

        // Update cart count from localStorage if available
        const cartCount = localStorage.getItem('cartCount') || 0;
        document.querySelector('.cart-count').textContent = cartCount;

        // Auto-refresh page every 10 seconds if payment is pending
        <?php if ($order['payment_status'] === 'pending'): ?>
        let refreshCount = 0;
        const maxRefreshes = 12; // Refresh for 2 minutes (12 × 10 seconds)
        
        const refreshInterval = setInterval(function() {
            refreshCount++;
            if (refreshCount >= maxRefreshes) {
                clearInterval(refreshInterval);
                return;
            }
            location.reload();
        }, 10000); // 10 seconds
        <?php endif; ?>
    </script>
</body>
</html>