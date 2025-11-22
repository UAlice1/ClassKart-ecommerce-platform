<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: Login.php');
    exit();
}

// Get filter
$filter = $_GET['filter'] ?? 'all';

// Build query based on filter
$whereClause = '';
if ($filter === 'pending') {
    $whereClause = "WHERE payment_status = 'pending'";
} elseif ($filter === 'successful') {
    $whereClause = "WHERE payment_status = 'successful'";
} elseif ($filter === 'failed') {
    $whereClause = "WHERE payment_status = 'failed'";
}

// Get orders
$ordersQuery = "SELECT * FROM orders $whereClause ORDER BY created_at DESC";
$ordersResult = $conn->query($ordersQuery);

// Get counts for each status
$allCount = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'] ?? 0;
$pendingCount = $conn->query("SELECT COUNT(*) as c FROM orders WHERE payment_status = 'pending'")->fetch_assoc()['c'] ?? 0;
$successCount = $conn->query("SELECT COUNT(*) as c FROM orders WHERE payment_status = 'successful'")->fetch_assoc()['c'] ?? 0;
$failedCount = $conn->query("SELECT COUNT(*) as c FROM orders WHERE payment_status = 'failed'")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - ClassKart Admin</title>
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
        .filter-tabs{
            display:flex;
            gap:1rem;
            margin-bottom:2rem;
            flex-wrap:wrap;
        }
        .filter-tab{
            padding:0.7rem 1.5rem;
            border-radius:10px;
            background:#fff;
            border:2px solid #E0E0E0;
            cursor:pointer;
            text-decoration:none;
            color:#333;
            font-weight:500;
            transition:all 0.3s;
            display:flex;
            align-items:center;
            gap:0.5rem;
        }
        .filter-tab.active{
            background:#0A5033;
            color:#fff;
            border-color:#0A5033;
        }
        .filter-tab:hover:not(.active){
            border-color:#0A5033;
        }
        .count-badge{
            background:rgba(0,0,0,0.1);
            padding:0.2rem 0.5rem;
            border-radius:999px;
            font-size:0.8rem;
            font-weight:600;
        }
        .card{
            background:#fff;
            border-radius:14px;
            padding:2rem;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
        }
        .table-container{
            overflow-x:auto;
        }
        table{
            width:100%;
            border-collapse:collapse;
            font-size:0.9rem;
        }
        th,td{
            padding:1rem;
            text-align:left;
            border-bottom:1px solid #F0F0F0;
        }
        th{
            background:#F8F8F8;
            font-weight:600;
            color:#555;
        }
        tbody tr:hover{
            background:#F8F8F8;
        }
        .badge{
            display:inline-block;
            padding:0.4rem 0.8rem;
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
        .btn-view{
            padding:0.4rem 1rem;
            background:#0A5033;
            color:#fff;
            text-decoration:none;
            border-radius:6px;
            font-size:0.85rem;
            display:inline-block;
            transition:all 0.3s;
        }
        .btn-view:hover{
            background:#084028;
        }
        .empty-state{
            text-align:center;
            padding:3rem;
            color:#999;
        }
        .empty-state-icon{
            font-size:4rem;
            margin-bottom:1rem;
        }
        @media(max-width:900px){
            body{flex-direction:column;}
            .sidebar{width:100%;flex-direction:row;align-items:center;justify-content:space-between;}
            .sidebar-nav{display:flex;gap:0.5rem;}
            .sidebar-footer{display:none;}
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
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_products.php">Products</a></li>
                <li><a href="admin_orders.php" class="active">Orders</a></li>
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
            <h1>📦 Orders Management</h1>
        </div>

        <div class="filter-tabs">
            <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                All Orders
                <span class="count-badge"><?php echo $allCount; ?></span>
            </a>
            <a href="?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                Pending
                <span class="count-badge"><?php echo $pendingCount; ?></span>
            </a>
            <a href="?filter=successful" class="filter-tab <?php echo $filter === 'successful' ? 'active' : ''; ?>">
                Successful
                <span class="count-badge"><?php echo $successCount; ?></span>
            </a>
            <a href="?filter=failed" class="filter-tab <?php echo $filter === 'failed' ? 'active' : ''; ?>">
                Failed
                <span class="count-badge"><?php echo $failedCount; ?></span>
            </a>
        </div>

        <div class="card">
            <div class="table-container">
                <?php if ($ordersResult && $ordersResult->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $ordersResult->fetch_assoc()): 
                            $status = $order['payment_status'] ?? 'pending';
                            $badgeClass = 'badge-' . $status;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($order['order_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                            <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                            <td><strong><?php echo number_format($order['total_amount'], 0); ?> Frw</strong></td>
                            <td>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y, g:i A', strtotime($order['created_at'])); ?></td>
                            <td>
                                <a href="order_confirmation.php?order=<?php echo htmlspecialchars($order['order_id']); ?>" 
                                   class="btn-view" 
                                   target="_blank">
                                    View
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📦</div>
                    <h3>No orders found</h3>
                    <p>
                        <?php 
                        if ($filter === 'pending') {
                            echo "No pending orders at the moment.";
                        } elseif ($filter === 'successful') {
                            echo "No successful orders yet.";
                        } elseif ($filter === 'failed') {
                            echo "No failed orders.";
                        } else {
                            echo "No orders have been placed yet. They will appear here once customers start purchasing.";
                        }
                        ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>