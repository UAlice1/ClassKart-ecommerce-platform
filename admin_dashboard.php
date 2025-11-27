<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: Login.php');
    exit();
}

// Simple stats
$totalProducts = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'] ?? 0;
$totalOrders   = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'] ?? 0;
$totalSalesRow = $conn->query("SELECT SUM(total_amount) AS s FROM orders WHERE payment_status = 'successful'")->fetch_assoc();
$totalSales    = $totalSalesRow && $totalSalesRow['s'] ? $totalSalesRow['s'] : 0;

// Get pending orders count
$pendingOrdersRow = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE payment_status = 'pending'")->fetch_assoc();
$pendingOrders = $pendingOrdersRow['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ClassKart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{
            font-family:'Poppins',sans-serif;
            background:#F5F5F5;
            color:#333;
            display:flex;
            min-height:100vh;
        }
        .sidebar{
            width:260px;
            background:#0A5033;
            color:#fff;
            padding:2rem 1.5rem;
            display:flex;
            flex-direction:column;
        }
        .sidebar-logo{
            display:flex;
            align-items:center;
            margin-bottom:2rem;
        }
        .sidebar-logo img{height:45px;}
        .sidebar-nav{
            list-style:none;
            margin-top:1rem;
        }
        .sidebar-nav li{
            margin-bottom:1rem;
        }
        .sidebar-nav a{
            color:#fff;
            text-decoration:none;
            padding:0.8rem 1rem;
            border-radius:10px;
            display:block;
            font-size:0.95rem;
            transition:background 0.3s;
        }
        .sidebar-nav a.active,
        .sidebar-nav a:hover{
            background:rgba(255,255,255,0.15);
        }
        .sidebar-footer{
            margin-top:auto;
            font-size:0.85rem;
            opacity:0.8;
        }
        .main{
            flex:1;
            padding:2rem 2.5rem;
        }
        .topbar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:2rem;
        }
        .topbar h1{
            font-size:1.6rem;
            color:#0A5033;
        }
        .stats-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
            gap:1.5rem;
            margin-bottom:2.5rem;
        }
        .stat-card{
            background:#fff;
            border-radius:14px;
            padding:1.5rem;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-label{
            font-size:0.9rem;
            color:#777;
            margin-bottom:0.4rem;
        }
        .stat-value{
            font-size:1.8rem;
            font-weight:700;
            color:#0A5033;
        }
        .stat-extra{
            font-size:0.8rem;
            color:#999;
            margin-top:0.3rem;
        }
        .content-grid{
            display:grid;
            grid-template-columns:2fr 1.5fr;
            gap:2rem;
        }
        .card{
            background:#fff;
            border-radius:14px;
            padding:1.5rem;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
        }
        .card h2{
            font-size:1.1rem;
            margin-bottom:1rem;
            color:#0A5033;
        }
        table{
            width:100%;
            border-collapse:collapse;
            font-size:0.9rem;
        }
        th,td{
            padding:0.75rem;
            text-align:left;
            border-bottom:1px solid #F0F0F0;
        }
        th{
            background:#F8F8F8;
            font-weight:600;
        }
        .badge{
            display:inline-block;
            padding:0.3rem 0.7rem;
            border-radius:999px;
            font-size:0.75rem;
            font-weight:600;
        }
        .badge-pending{
            background:#FFF3CD;
            color:#856404;
        }
        .badge-successful{
            background:#D4EDDA;
            color:#155724;
        }
        .badge-failed{
            background:#F8D7DA;
            color:#721C24;
        }
        .badge-cancelled{
            background:#E2E3E5;
            color:#383D41;
        }
        .btn{
            border:none;
            border-radius:8px;
            padding:0.5rem 1rem;
            font-size:0.85rem;
            cursor:pointer;
            font-family:'Poppins',sans-serif;
        }
        .btn-primary{
            background:#0A5033;
            color:#fff;
        }
        .btn-primary:hover{
            background:#084028;
        }
        .btn-danger{
            background:#DC3545;
            color:#fff;
        }
        .product-form{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:1rem;
            margin-top:0.5rem;
        }
        .product-form .form-group{
            display:flex;
            flex-direction:column;
            font-size:0.85rem;
        }
        .product-form label{
            margin-bottom:0.3rem;
            color:#555;
        }
        .product-form input,
        .product-form select,
        .product-form textarea{
            padding:0.55rem 0.7rem;
            border-radius:8px;
            border:1px solid #E0E0E0;
            font-family:'Poppins',sans-serif;
            font-size:0.85rem;
        }
        .product-form textarea{grid-column:1/3;min-height:70px;resize:vertical;}
        .product-form .form-actions{
            grid-column:1/3;
            display:flex;
            justify-content:flex-end;
            margin-top:0.5rem;
        }
        .view-link{
            color:#0A5033;
            text-decoration:none;
            font-size:0.85rem;
            font-weight:600;
        }
        .view-link:hover{
            text-decoration:underline;
        }
        @media(max-width:900px){
            body{flex-direction:column;}
            .sidebar{width:100%;flex-direction:row;align-items:center;justify-content:space-between;}
            .sidebar-nav{display:flex;gap:0.5rem;}
            .sidebar-footer{display:none;}
            .content-grid{grid-template-columns:1fr;}
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div>
            <div class="sidebar-logo">
                <img src="images/logo.png" alt="ClassKart Logo">
            </div>
            <ul class="sidebar-nav">
                <li><a href="admin_dashboard.php" class="active">Dashboard</a></li>
                <li><a href="admin_products.php">Products</a></li>
                <li><a href="admin_orders.php">Orders</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            Signed in as<br>
            <strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?></strong>
            <br><br>
            <a href="logout.php" style="color:#fff;text-decoration:none;">Logout</a>
        </div>
    </aside>
    <main class="main">
        <div class="topbar">
            <h1>Admin Dashboard</h1>
        </div>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Products</div>
                <div class="stat-value"><?php echo (int)$totalProducts; ?></div>
                <div class="stat-extra">Items currently in your catalog</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?php echo (int)$totalOrders; ?></div>
                <div class="stat-extra">All time orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Sales</div>
                <div class="stat-value"><?php echo number_format($totalSales, 0); ?> Frw</div>
                <div class="stat-extra">From successful payments</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending Orders</div>
                <div class="stat-value"><?php echo (int)$pendingOrders; ?></div>
                <div class="stat-extra">Awaiting payment confirmation</div>
            </div>
        </section>

        <section class="content-grid">
            <div class="card">
                <h2>Quick Add Product</h2>
                <form class="product-form" method="POST" action="admin_products.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (Frw)</label>
                        <input type="number" id="price" name="price" min="0" step="1" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select</option>
                            <option value="books">Books</option>
                            <option value="stationery">Stationery</option>
                            <option value="courses">Courses</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" id="stock" name="stock" min="0" step="1" value="0" required>
                    </div>
                    <div class="form-group">
                        <label for="image">Image (optional)</label>
                        <input type="file" id="image" name="image">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Short description"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2>Recent Orders</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // FIXED QUERY - using payment_status instead of status
                        $ordersRes = $conn->query("SELECT id, order_id, customer_name, total_amount, payment_status, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
                        
                        if ($ordersRes && $ordersRes->num_rows > 0):
                            while ($o = $ordersRes->fetch_assoc()):
                                $status = $o['payment_status'] ?? 'pending';
                                $badgeClass = 'badge-' . $status;
                        ?>
                        <tr>
                            <td>
                                <a href="order_confirmation.php?order=<?php echo htmlspecialchars($o['order_id']); ?>" class="view-link">
                                    <?php echo htmlspecialchars($o['order_id']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($o['customer_name'] ?? 'Customer'); ?></td>
                            <td><strong><?php echo number_format($o['total_amount'], 0); ?> Frw</strong></td>
                            <td>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars(ucfirst($status)); ?>
                                </span>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 2rem; color: #999;">
                                No orders yet. Orders will appear here once customers start purchasing.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if ($ordersRes && $ordersRes->num_rows > 0): ?>
                <div style="margin-top: 1rem; text-align: right;">
                    <a href="admin_orders.php" class="view-link">View All Orders →</a>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>



