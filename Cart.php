<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle updates from quantity buttons
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update']) && isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $productId => $qty) {
            $productId = (int)$productId;
            $qty = max(1, (int)$qty);
            if ($productId > 0) {
                $_SESSION['cart'][$productId] = $qty;
            }
        }
    }
    if (isset($_POST['remove'])) {
        $removeId = (int)$_POST['remove'];
        unset($_SESSION['cart'][$removeId]);
    }
}

$cart = $_SESSION['cart'];
$productIds = array_keys($cart);
$items = [];

if (!empty($productIds)) {
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $types = str_repeat('i', count($productIds));
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$productIds);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();
}

function cartCount(array $cart): int {
    return array_sum($cart);
}

$totalItems = cartCount($cart);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - ClassKart</title>
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

        /* Header */
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

        /* Cart Hero Section */
        .cart-hero {
            background: linear-gradient(135deg, #0A5033 0%, #0d6b45 100%);
            color: white;
            padding: 3rem 5%;
            text-align: left;
        }

        .cart-hero-content {
            max-width: 1400px;
            margin: 0 auto;
        }

        .cart-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .cart-hero p {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Cart Content */
        .cart-container {
            max-width: 1400px;
            margin: 3rem auto;
            padding: 0 5%;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        /* Cart Items */
        .cart-items {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .cart-item {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            gap: 1.5rem;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .cart-item:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .item-image {
            width: 100px;
            height: 100px;
            background: #F8F8F8;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 0.5rem;
        }

        .item-details {
            flex: 1;
        }

        .item-details h3 {
            font-size: 1.1rem;
            color: #0A5033;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .item-price {
            font-size: 1.3rem;
            color: #0A5033;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .qty-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #E0E0E0;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            color: #333;
        }

        .qty-btn:hover {
            background: #0A5033;
            color: white;
            border-color: #0A5033;
        }

        .qty-input {
            width: 50px;
            text-align: center;
            border: 1px solid #E0E0E0;
            border-radius: 6px;
            padding: 0.4rem;
            font-weight: 600;
            font-size: 1rem;
        }

        .item-total {
            text-align: right;
        }

        .item-total-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0A5033;
            margin-bottom: 1rem;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 1.5rem;
            transition: color 0.3s;
        }

        .remove-btn:hover {
            color: #DC3545;
        }

        /* Order Summary */
        .order-summary {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            height:fit-content;
            sticky-top: 100px;
        }

        .order-summary h2 {
            font-size: 1.5rem;
            color: #0A5033;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .summary-row.label {
            color: #666;
        }

        .summary-row.value {
            font-weight: 600;
            color: #333;
        }

        .summary-divider {
            height: 1px;
            background: #E0E0E0;
            margin: 1.5rem 0;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.5rem 0;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .total-label {
            color: #333;
        }

        .total-value {
            color: #0A5033;
            font-size: 1.5rem;
        }

        .btn-checkout {
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
            margin-bottom: 1rem;
        }

        .btn-checkout:hover {
            background: #084028;
            transform: translateY(-2px);
        }

        .btn-continue {
            width: 100%;
            background: white;
            color: #0A5033;
            padding: 1rem;
            border: 2px solid #0A5033;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-continue:hover {
            background: #F5F5F5;
        }

        /* Footer */
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

        .social-icons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-icon {
            width: 35px;
            height: 35px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .social-icon:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
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

        .newsletter-form button:hover {
            background-color: #F5F5F5;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0.9;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .cart-container {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .cart-hero h1 {
                font-size: 2rem;
            }

            .cart-item {
                flex-direction: column;
                text-align: center;
            }

            .item-total {
                width: 100%;
                text-align: center;
            }

            .search-bar {
                width: 150px;
            }

            .nav-links {
                gap: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .logo img {
                height: 40px;
            }

            .search-bar {
                display: none;
            }

            .cart-hero h1 {
                font-size: 1.8rem;
            }

            .item-image {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <div class="logo">
                <img src="images/logo.png" alt="ClassKart Logo">
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#shop">Shop</a></li>
                <li><a href="index.php#about">About</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <div class="nav-right">
                <input type="text" class="search-bar" placeholder="Search products...">
                <a href="Cart.php" class="cart-icon">
                    🛒
                    <span class="cart-count" id="cartCount"><?php echo (int)$totalItems; ?></span>
                </a>
                <a href="<?php echo isset($_SESSION['user_id']) ? 'logout.php' : 'Login.php'; ?>">
                    <?php echo isset($_SESSION['user_id']) ? 'Logout' : 'Login'; ?>
                </a>
            </div>
        </nav>
    </header>

    <!-- Cart Hero -->
    <section class="cart-hero">
        <div class="cart-hero-content">
            <h1>Shopping Cart</h1>
            <p><span id="itemCount"><?php echo (int)$totalItems; ?></span> items in your cart</p>
        </div>
    </section>

    <!-- Cart Container -->
    <div class="cart-container">
        <!-- Cart Items -->
        <form method="POST" class="cart-items" id="cartItems">
            <?php
            $subtotal = 0;
            if (!empty($items)):
                foreach ($items as $item):
                    $id = (int)$item['id'];
                    $qty = (int)($cart[$id] ?? 1);
                    $price = (float)$item['price'];
                    $lineTotal = $price * $qty;
                    $subtotal += $lineTotal;
            ?>
            <div class="cart-item" data-id="<?php echo $id; ?>" data-price="<?php echo htmlspecialchars($price); ?>">
                <div class="item-image">
                    <?php if (!empty($item['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <?php else: ?>
                        <img src="images/unknownbook.jpg" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <?php endif; ?>
                </div>
                <div class="item-details">
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <div class="item-price"><?php echo number_format($price, 0); ?> Frw</div>
                    <div class="quantity-controls">
                        <button type="button" class="qty-btn" onclick="changeQty(<?php echo $id; ?>,-1)">-</button>
                        <input type="number" class="qty-input" value="<?php echo $qty; ?>" id="qty-<?php echo $id; ?>" name="qty[<?php echo $id; ?>]" readonly>
                        <button type="button" class="qty-btn" onclick="changeQty(<?php echo $id; ?>,1)">+</button>
                    </div>
                </div>
                <div class="item-total">
                    <div class="item-total-price" id="total-<?php echo $id; ?>"><?php echo number_format($lineTotal, 0); ?> Frw</div>
                    <button class="remove-btn" name="remove" value="<?php echo $id; ?>">🗑️</button>
                </div>
            </div>
            <?php
                endforeach;
            else:
            ?>
                <p>Your cart is empty. <a href="shop.php">Start shopping</a>.</p>
            <?php endif; ?>
            <input type="hidden" name="update" value="1">
        </form>

        <!-- Order Summary -->
        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="summary-row">
                <span class="label">Subtotal</span>
                <span class="value" id="subtotal"><?php echo number_format($subtotal ?? 0, 0); ?> Frw</span>
            </div>
            <div class="summary-row">
                <span class="label">Shipping</span>
                <span class="value" id="shipping">Free</span>
            </div>
            <div class="summary-divider"></div>
            <div class="summary-total">
                <span class="total-label">Total</span>
                <span class="total-value" id="total"><?php echo number_format($subtotal ?? 0, 0); ?> Frw</span>
            </div>
            <button class="btn-checkout" <?php echo $totalItems === 0 ? 'disabled style="opacity:0.6;cursor:not-allowed;"' : ''; ?> onclick="window.location.href='Checkout.php'">Proceed to Checkout →</button>
            <button class="btn-continue" onclick="window.location.href='shop.php'">Continue Shopping</button>
        </div>
    </div>

    <!-- Footer -->
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
                    <li><a href="index.html#about">About Us</a></li>
                    <li><a href="contact.html">Contact</a></li>
                    <li><a href="#faq">FAQ</a></li>
                </ul>
            </div>

          

        <div class="footer-bottom">
            <p>© 2025 ClassKart. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function changeQty(id, delta) {
            const input = document.getElementById('qty-' + id);
            let value = parseInt(input.value) + delta;
            if (value < 1) value = 1;
            input.value = value;
            document.getElementById('cartItems').submit();
        }
    </script>
</body>
</html>