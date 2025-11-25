<?php
session_start();
require_once 'db_connection.php';

$categoryFilter = $_GET['category'] ?? '';

$sql = "SELECT * FROM products";
if ($categoryFilter && in_array($categoryFilter, ['books','stationery','courses'])) {
    $stmt = $conn->prepare($sql . " WHERE category=? ORDER BY created_at DESC");
    $stmt->bind_param("s", $categoryFilter);
    $stmt->execute();
    $products = $stmt->get_result();
    $stmt->close();
} else {
    $products = $conn->query($sql . " ORDER BY created_at DESC");
}

function getCartCount(): int {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - ClassKart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{
            font-family:'Poppins',sans-serif;
            background-color:#FFFFFF;
            color:#333;
        }
        header{
            background-color:#FFFFFF;
            padding:1rem 5%;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
            position:sticky;
            top:0;
            z-index:1000;
        }
        nav{
            display:flex;
            justify-content:space-between;
            align-items:center;
            max-width:1400px;
            margin:0 auto;
        }
        .logo img{height:50px;object-fit:contain;}
        .nav-links{display:flex;list-style:none;gap:2.5rem;align-items:center;}
        .nav-links a{
            text-decoration:none;
            color:#333;
            font-weight:500;
            transition:color 0.3s;
        }
        .nav-links a:hover{color:#0A5033;}
        .nav-right{display:flex;align-items:center;gap:1.5rem;}
        .search-bar{
            padding:0.6rem 1.2rem;
            border:1px solid #E0E0E0;
            border-radius:25px;
            outline:none;
            width:250px;
            transition:border 0.3s;
        }
        .search-bar:focus{border-color:#0A5033;}
        .cart-icon{position:relative;cursor:pointer;font-size:1.4rem;text-decoration:none;color:#333;}
        .cart-count{
            position:absolute;
            top:-8px;
            right:-8px;
            background-color:#0A5033;
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
        /* Hero */
        .hero{
            background:linear-gradient(135deg,#0A5033 0%,#0d6b45 100%);
            color:white;
            padding:3rem 5%;
            text-align:left;
        }
        .hero-content{
            max-width:1400px;
            margin:0 auto;
        }
        .hero h1{
            font-size:2.5rem;
            margin-bottom:0.5rem;
        }
        .hero p{
            opacity:0.95;
        }
        /* Filters & grid */
        .shop-container{
            max-width:1400px;
            margin:2.5rem auto 4rem;
            padding:0 5%;
        }
        .filters{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:1.5rem;
            flex-wrap:wrap;
            gap:1rem;
        }
        .filter-buttons{
            display:flex;
            gap:0.8rem;
            flex-wrap:wrap;
        }
        .filter-btn{
            border-radius:999px;
            border:1px solid #0A5033;
            padding:0.45rem 1.2rem;
            background:#fff;
            color:#0A5033;
            cursor:pointer;
            font-size:0.9rem;
            font-weight:500;
            transition:all 0.2s;
        }
        .filter-btn.active,
        .filter-btn:hover{
            background:#0A5033;
            color:#fff;
        }
        .product-grid{
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
            gap:2rem;
        }
        .product-card{
            background:#fff;
            border-radius:15px;
            overflow:hidden;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
            display:flex;
            flex-direction:column;
            transition:all 0.3s;
        }
        .product-card:hover{
            transform:translateY(-5px);
            box-shadow:0 10px 30px rgba(0,0,0,0.15);
        }
        .product-image{
            height:220px;
            background:#F8F8F8;
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
        }
        .product-image img{
            width:100%;
            height:100%;
            object-fit:contain;
            padding:1rem;
        }
        .product-content{
            padding:1.4rem;
            display:flex;
            flex-direction:column;
            flex:1;
        }
        .product-category{
            font-size:0.8rem;
            font-weight:600;
            color:#0A5033;
            text-transform:uppercase;
            margin-bottom:0.3rem;
        }
        .product-title{
            font-size:1rem;
            font-weight:600;
            margin-bottom:0.4rem;
        }
        .product-price{
            font-size:1.3rem;
            font-weight:700;
            color:#0A5033;
            margin:0.4rem 0 0.8rem;
        }
        .product-footer{
            margin-top:auto;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:0.5rem;
        }
        .btn-add{
            background:#0A5033;
            color:#fff;
            border:none;
            border-radius:10px;
            padding:0.6rem 1.2rem;
            cursor:pointer;
            font-size:0.9rem;
            font-weight:600;
            transition:all 0.2s;
        }
        .btn-add:hover{
            background:#084028;
            transform:scale(1.03);
        }
        .btn-details{
            background:#fff;
            border:1px solid #E0E0E0;
            border-radius:10px;
            padding:0.5rem 1rem;
            font-size:0.85rem;
            cursor:pointer;
        }
        /* Footer */
        footer{
            background-color:#0A5033;
            color:white;
            padding:4rem 5% 2rem;
            margin-top:3rem;
        }
        .footer-content{
            max-width:1400px;
            margin:0 auto;
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
            gap:3rem;
            margin-bottom:3rem;
        }
        .footer-section h3{margin-bottom:1.3rem;}
        .footer-section p{opacity:0.9;margin-bottom:1rem;}
        .footer-links{list-style:none;}
        .footer-links li{margin-bottom:0.7rem;}
        .footer-links a{
            color:rgba(255,255,255,0.9);
            text-decoration:none;
            transition:all 0.3s;
        }
        .footer-links a:hover{
            color:#fff;
            padding-left:5px;
        }
        .newsletter-form{
            display:flex;
            gap:0.5rem;
            margin-top:1rem;
        }
        .newsletter-form input{
            flex:1;
            padding:0.8rem 1rem;
            border:none;
            border-radius:8px;
        }
        .newsletter-form button{
            padding:0.8rem 1.5rem;
            border:none;
            border-radius:8px;
            background:#fff;
            color:#0A5033;
            font-weight:600;
            cursor:pointer;
        }
        .footer-bottom{
            text-align:center;
            padding-top:2rem;
            border-top:1px solid rgba(255,255,255,0.1);
            opacity:0.9;
        }
        @media(max-width:768px){
            .search-bar{width:150px;}
            .product-grid{grid-template-columns:repeat(auto-fill,minmax(220px,1fr));}
        }
        @media(max-width:480px){
            .search-bar{display:none;}
            .product-grid{grid-template-columns:1fr;}
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
        <div class="hero-content">
            <h1>Shop Educational Materials</h1>
            <p>Browse books, stationery, and digital learning resources curated for students, teachers, and parents.</p>
        </div>
    </section>

    <main class="shop-container">
        <div class="filters">
            <div class="filter-buttons">
                <button class="filter-btn <?php echo $categoryFilter==='' ? 'active' : ''; ?>" onclick="window.location='shop.php'">All</button>
                <button class="filter-btn <?php echo $categoryFilter==='books' ? 'active' : ''; ?>" onclick="window.location='shop.php?category=books'">Books</button>
                <button class="filter-btn <?php echo $categoryFilter==='stationery' ? 'active' : ''; ?>" onclick="window.location='shop.php?category=stationery'">Stationery</button>
                <button class="filter-btn <?php echo $categoryFilter==='courses' ? 'active' : ''; ?>" onclick="window.location='shop.php?category=courses'">Courses</button>
            </div>
        </div>

        <section class="product-grid">
            <?php if ($products && $products->num_rows > 0): ?>
                <?php while ($p = $products->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($p['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                            <?php else: ?>
                                <img src="images/unknownbook.jpg" alt="<?php echo htmlspecialchars($p['name']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="product-content">
                            <div class="product-category"><?php echo htmlspecialchars(strtoupper($p['category'])); ?></div>
                            <div class="product-title"><?php echo htmlspecialchars($p['name']); ?></div>
                            <div class="product-price"><?php echo number_format($p['price'], 0); ?> Frw</div>
                            <div class="product-footer">
                                <form method="POST" action="add_to_cart.php" style="margin:0;">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                    <button type="submit" class="btn-add">Add to Cart</button>
                                </form>
                                <a href="product_detail.php?id=<?php echo (int)$p['id']; ?>" class="btn-details">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products available yet. Please check back soon.</p>
            <?php endif; ?>
        </section>
    </main>

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
            
        <div class="footer-bottom">
            <p>© 2025 ClassKart. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>






