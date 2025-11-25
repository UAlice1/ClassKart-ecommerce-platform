<?php
session_start();
require_once 'db_connection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: shop.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: shop.php');
    exit();
}

function getCartCount(): int {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - ClassKart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Poppins',sans-serif;background:#F5F5F5;color:#333;}
        header{
            background:#fff;
            padding:1rem 5%;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
            position:sticky;top:0;z-index:1000;
        }
        nav{
            max-width:1400px;
            margin:0 auto;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }
        .logo img{height:50px;object-fit:contain;}
        .nav-links{display:flex;list-style:none;gap:2.5rem;}
        .nav-links a{text-decoration:none;color:#333;font-weight:500;}
        .nav-links a:hover{color:#0A5033;}
        .nav-right{display:flex;align-items:center;gap:1.5rem;}
        .search-bar{
            padding:0.6rem 1.2rem;border:1px solid #E0E0E0;border-radius:25px;outline:none;width:250px;
        }
        .cart-icon{position:relative;text-decoration:none;color:#333;font-size:1.4rem;}
        .cart-count{
            position:absolute;top:-8px;right:-8px;background:#0A5033;color:#fff;border-radius:50%;
            width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:600;
        }
        .product-container{
            max-width:1200px;
            margin:3rem auto;
            padding:0 5%;
            display:grid;
            grid-template-columns:1.2fr 1.5fr;
            gap:2.5rem;
        }
        .product-image-wrap{
            background:#fff;
            border-radius:15px;
            padding:2rem;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .product-image-wrap img{
            width:100%;
            height:100%;
            object-fit:contain;
            max-height:420px;
        }
        .product-info{
            background:#fff;
            border-radius:15px;
            padding:2rem;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
        }
        .product-category{
            font-size:0.8rem;
            font-weight:600;
            color:#0A5033;
            text-transform:uppercase;
            margin-bottom:0.4rem;
        }
        h1{font-size:1.8rem;margin-bottom:0.6rem;}
        .price{
            font-size:1.7rem;
            font-weight:700;
            color:#0A5033;
            margin:0.8rem 0 1.2rem;
        }
        .description{color:#666;font-size:0.95rem;line-height:1.7;margin-bottom:1.5rem;}
        .stock{font-size:0.9rem;margin-bottom:1.5rem;}
        .stock span{font-weight:600;}
        .btn-primary{
            background:#0A5033;
            color:#fff;
            border:none;
            border-radius:10px;
            padding:0.8rem 1.8rem;
            font-size:0.95rem;
            font-weight:600;
            cursor:pointer;
            transition:all 0.2s;
        }
        .btn-primary:hover{background:#084028;transform:translateY(-2px);}
        .btn-secondary{
            background:#fff;
            border:1px solid #E0E0E0;
            border-radius:10px;
            padding:0.8rem 1.6rem;
            font-size:0.9rem;
            cursor:pointer;
            margin-left:0.8rem;
        }
        footer{
            background:#0A5033;
            color:#fff;
            padding:3rem 5% 2rem;
            margin-top:4rem;
        }
        .footer-bottom{
            text-align:center;
            padding-top:1.5rem;
            border-top:1px solid rgba(255,255,255,0.1);
            opacity:0.9;
        }
        @media(max-width:900px){
            .product-container{grid-template-columns:1fr;}
        }
        @media(max-width:480px){
            .search-bar{display:none;}
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

<main class="product-container">
    <div class="product-image-wrap">
        <?php if (!empty($product['image_path'])): ?>
            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        <?php else: ?>
            <img src="images/unknownbook.jpg" alt="<?php echo htmlspecialchars($product['name']); ?>">
        <?php endif; ?>
    </div>
    <div class="product-info">
        <div class="product-category"><?php echo htmlspecialchars(strtoupper($product['category'])); ?></div>
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="price"><?php echo number_format($product['price'], 0); ?> Frw</div>
        <div class="description">
            <?php echo nl2br(htmlspecialchars($product['description'] ?: 'High-quality educational product from ClassKart.')); ?>
        </div>
        <div class="stock">
            Availability:
            <span><?php echo (int)$product['stock'] > 0 ? 'In stock' : 'Out of stock'; ?></span>
        </div>
        <form method="POST" action="add_to_cart.php" style="display:inline;">
            <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
            <button type="submit" class="btn-primary" <?php echo (int)$product['stock'] === 0 ? 'disabled style="opacity:0.6;cursor:not-allowed;"' : ''; ?>>
                Add to Cart
            </button>
        </form>
        <button class="btn-secondary" onclick="window.location='shop.php'">Back to Shop</button>
    </div>
</main>

<footer>
    <div class="footer-bottom">
        <p>© 2025 ClassKart. All rights reserved.</p>
    </div>
</footer>
</body>
</html>





