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

        .payment-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .security-note {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.85rem;
            margin-top: 1rem;
            padding: 0.75rem;
            background: #F8F8F8;
            border-radius: 8px;
        }

        .security-note::before {
            content: "🔒";
            font-size: 1rem;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-place-order:hover {
            background: #084028;
            transform: translateY(-2px);
        }

        .btn-place-order::before {
            content: "🔒";
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

        .newsletter-form {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .newsletter-form input {
            flex: 1;
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 8px;
            outline: none;
        }

        .newsletter-form button {
            padding: 0.8rem 1.5rem;
            background-color: #FFFFFF;
            color: #0A5033;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0.9;
        }

        .empty-state {
            padding: 1rem;
            background: #FFF8E1;
            color: #8a6d3b;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border: 1px solid #F5E2A3;
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
                <li><a href="index.html">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="About.html">About</a></li>
                <li><a href="Contact.html">Contact</a></li>
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
            <p>Complete your order</p>
        </div>
    </section>

    <div class="checkout-container">
        <div class="checkout-form">
            <?php if ($cartEmpty): ?>
                <div class="empty-state">
                    Your cart is empty. Please add products before proceeding to checkout.
                </div>
            <?php endif; ?>
            <form id="checkoutForm" method="POST" action="process_order.php">
                <div class="form-section">
                    <h2>Contact Information</h2>
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="fullName" required <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" required <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Shipping Address</h2>
                    <div class="form-group">
                        <label for="street">Street Address</label>
                        <input type="text" id="street" name="street" required <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" required <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="zipCode">ZIP Code</label>
                        <input type="text" id="zipCode" name="zipCode" required style="max-width: 200px;" <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                    </div>
                </div>

                <div class="form-section">
                    <div class="payment-header">
                        <h2>Payment Details (Simulation)</h2>
                    </div>
                    <div class="form-group">
                        <label for="cardNumber">Card Number</label>
                        <input type="text" id="cardNumber" name="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiryDate">Expiry Date</label>
                            <input type="text" id="expiryDate" name="expiryDate" placeholder="MM/YY" maxlength="5" <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="3" <?php echo $cartEmpty ? 'disabled' : ''; ?>>
                        </div>
                    </div>
                    <div class="security-note">
                        Payments are simulated for this demo. No real charges will be made.
                    </div>
                </div>

                <button type="submit" class="btn-place-order" <?php echo $cartEmpty ? 'disabled style="opacity:0.6;cursor:not-allowed;"' : ''; ?>>
                    Place Order
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
                    <span>Free</span>
                </div>
                <div class="summary-divider"></div>
                <div class="summary-total">
                    <span>Total</span>
                    <span class="total-value"><?php echo number_format($subtotal, 0); ?> Frw</span>
                </div>
            <?php else: ?>
                <p>Your bag is empty.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>ClassKart</h3>
                <p>Your One-Stop Shop for Learning. Quality educational materials for students, teachers, and parents.</p>
                <div class="social-icons">
                    <a href="#" class="social-icon">f</a>
                    <a href="#" class="social-icon">𝕏</a>
                    <a href="#" class="social-icon">📷</a>
                </div>
            </div>

            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="index.html">Shop</a></li>
                    <li><a href="About.html">About Us</a></li>
                    <li><a href="Contact.html">Contact</a></li>
                    <li><a href="#faq">FAQ</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Customer Service</h3>
                <ul class="footer-links">
                    <li><a href="#shipping">Shipping Info</a></li>
                    <li><a href="#returns">Returns & Refunds</a></li>
                    <li><a href="#privacy">Privacy Policy</a></li>
                    <li><a href="#terms">Terms & Conditions</a></li>
                    <li><a href="#support">Support Center</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Newsletter</h3>
                <p>Subscribe to get updates on new products and exclusive offers.</p>
                <form class="newsletter-form" onsubmit="return false;">
                    <input type="email" placeholder="Your email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© 2025 ClassKart. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.getElementById('cardNumber').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });

        document.getElementById('expiryDate').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });

        document.getElementById('cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    </script>
</body>
</html>


