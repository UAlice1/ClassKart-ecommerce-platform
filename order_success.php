<?php
session_start();
require_once 'db_connection.php';

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($orderId <= 0) {
    header('Location: shop.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: shop.php');
    exit();
}

$itemsStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id=?");
$itemsStmt->bind_param("i", $orderId);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();
$items = [];
while ($row = $itemsResult->fetch_assoc()) {
    $items[] = $row;
}
$itemsStmt->close();

$cartCount = isset($_SESSION['cart']) ? array_sum((array)$_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - ClassKart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Poppins',sans-serif;background:#F5F5F5;color:#333;}
        header{
            background:#fff;
            padding:1rem 5%;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
        }
        nav{
            max-width:1400px;
            margin:0 auto;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }
        .logo img{height:50px;}
        .nav-links{display:flex;list-style:none;gap:2.5rem;}
        .nav-links a{text-decoration:none;color:#333;font-weight:500;}
        .nav-links a:hover{color:#0A5033;}
        .nav-right{display:flex;align-items:center;gap:1.5rem;}
        .search-bar{
            padding:0.6rem 1.2rem;
            border:1px solid #E0E0E0;
            border-radius:25px;
            width:250px;
        }
        .cart-icon{
            position:relative;
            text-decoration:none;
            color:#333;
            font-size:1.4rem;
        }
        .cart-count{
            position:absolute;
            top:-8px;
            right:-8px;
            background:#0A5033;
            color:#fff;
            border-radius:50%;
            width:20px;
            height:20px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:0.75rem;
            font-weight:600;
        }
        .hero{
            background:linear-gradient(135deg,#0A5033 0%,#0d6b45 100%);
            color:#fff;
            padding:4rem 5%;
            text-align:center;
        }
        .hero h1{font-size:2.5rem;margin-bottom:0.5rem;}
        .hero p{opacity:0.9;}
        .confirmation-card{
            max-width:900px;
            margin: -80px auto 3rem;
            background:#fff;
            border-radius:18px;
            padding:3rem;
            box-shadow:0 20px 60px rgba(10,80,51,0.15);
        }
        .order-number{
            font-size:1rem;
            color:#0A5033;
            margin-bottom:1rem;
            font-weight:600;
        }
        .order-items{
            margin:2rem 0;
            border-top:1px solid #F0F0F0;
            border-bottom:1px solid #F0F0F0;
        }
        .order-item{
            display:flex;
            justify-content:space-between;
            padding:1rem 0;
            border-bottom:1px solid #F5F5F5;
        }
        .order-item:last-child{
            border-bottom:none;
        }
        .order-item h4{
            font-size:1rem;
            margin-bottom:0.3rem;
        }
        .summary{
            margin-top:1.5rem;
        }
        .summary-row{
            display:flex;
            justify-content:space-between;
            margin-bottom:0.7rem;
        }
        .total{
            font-size:1.3rem;
            font-weight:700;
            color:#0A5033;
        }
        .cta-buttons{
            margin-top:2.5rem;
            display:flex;
            gap:1rem;
            flex-wrap:wrap;
        }
        .btn{
            border:none;
            border-radius:10px;
            padding:0.9rem 1.8rem;
            font-size:0.95rem;
            font-weight:600;
            cursor:pointer;
            transition:all 0.2s;
        }
        .btn-primary{
            background:#0A5033;
            color:#fff;
        }
        .btn-secondary{
            background:#fff;
            color:#0A5033;
            border:1px solid #0A5033;
        }
        .shipping-info{
            margin-top:1.5rem;
            background:#F8FDF9;
            border-radius:12px;
            padding:1rem 1.5rem;
            border:1px solid #D9F1E4;
        }
        footer{
            background:#0A5033;
            color:#fff;
            padding:3rem 5% 2rem;
        }
        .footer-bottom{
            text-align:center;
            padding-top:2rem;
            border-top:1px solid rgba(255,255,255,0.1);
        }
        @media(max-width:768px){
            .search-bar{display:none;}
            .confirmation-card{margin-top: -50px;padding:2rem;}
            .btn{width:100%;text-align:center;}
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
                <a href="Cart.php" class="cart-icon">🛒
                    <span class="cart-count"><?php echo (int)$cartCount; ?></span>
                </a>
                <a href="<?php echo isset($_SESSION['user_id']) ? 'logout.php' : 'Login.php'; ?>">
                    <?php echo isset($_SESSION['user_id']) ? 'Logout' : 'Login'; ?>
                </a>
            </div>
        </nav>
    </header>

    <section class="hero">
        <h1>Thank You for Your Order!</h1>
        <p>Your educational materials are on the way.</p>
    </section>

    <div class="confirmation-card">
        <div class="order-number">Order #<?php echo str_pad($orderId, 5, '0', STR_PAD_LEFT); ?></div>
        <h2>Hi <?php echo htmlspecialchars($order['customer_name']); ?>, your order has been received.</h2>
        <p>We’ve sent confirmation details to <strong><?php echo htmlspecialchars($order['customer_email']); ?></strong>.</p>

        <div class="order-items">
            <?php foreach ($items as $item): ?>
                <div class="order-item">
                    <div>
                        <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                        <small>Qty: <?php echo (int)$item['quantity']; ?></small>
                    </div>
                    <div><?php echo number_format($item['line_total'], 0); ?> Frw</div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="summary">
            <div class="summary-row">
                <span>Subtotal</span>
                <span><?php echo number_format($order['total_amount'], 0); ?> Frw</span>
            </div>
            <div class="summary-row">
                <span>Shipping</span>
                <span>Free</span>
            </div>
            <div class="summary-row total">
                <span>Total</span>
                <span><?php echo number_format($order['total_amount'], 0); ?> Frw</span>
            </div>
        </div>

        <div class="shipping-info">
            <strong>Shipping to:</strong>
            <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            <small>Status: <?php echo ucfirst($order['status']); ?></small>
        </div>

        <div class="cta-buttons">
            <button class="btn btn-primary" onclick="window.location='shop.php'">Continue Shopping</button>
            <button class="btn btn-secondary" onclick="window.location='Cart.php'">View Cart</button>
        </div>
    </div>

    <footer>
        <div class="footer-bottom">
            <p>© 2025 ClassKart. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>





