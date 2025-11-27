<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: Login.php');
    exit();
}

$message = '';

// Handle create / update / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = trim($_POST['name']);
        $price = (float)$_POST['price'];
        $category = trim($_POST['category']);
        $stock = (int)$_POST['stock'];
        $description = trim($_POST['description'] ?? '');
        $imagePath = null;

        if (!empty($_FILES['image']['name'])) {
            $uploadDir = 'images/';
            $filename = time() . '_' . basename($_FILES['image']['name']);
            $target   = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $imagePath = $target;
            }
        }

        $stmt = $conn->prepare("INSERT INTO products (name, price, category, stock, description, image_path) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("sdisss", $name, $price, $category, $stock, $description, $imagePath);
        $stmt->execute();
        $stmt->close();
        $message = 'Product added successfully.';
    } elseif ($action === 'update') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $price = (float)$_POST['price'];
        $category = trim($_POST['category']);
        $stock = (int)$_POST['stock'];
        $description = trim($_POST['description'] ?? '');

        $stmt = $conn->prepare("UPDATE products SET name=?, price=?, category=?, stock=?, description=? WHERE id=?");
        $stmt->bind_param("sdissi", $name, $price, $category, $stock, $description, $id);
        $stmt->execute();
        $stmt->close();
        $message = 'Product updated.';
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $message = 'Product deleted.';
    }
}

// Load products
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - ClassKart</title>
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
        .sidebar-nav li{margin-bottom:1rem;}
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
            margin-bottom:1.5rem;
        }
        .topbar h1{
            font-size:1.6rem;
            color:#0A5033;
        }
        .message{
            background:#E8F5E9;
            color:#0A5033;
            padding:0.75rem 1rem;
            border-radius:8px;
            margin-bottom:1rem;
            font-size:0.9rem;
        }
        table{
            width:100%;
            border-collapse:collapse;
            background:#fff;
            border-radius:14px;
            overflow:hidden;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
            font-size:0.9rem;
        }
        th,td{
            padding:0.75rem;
            border-bottom:1px solid #F0F0F0;
            text-align:left;
        }
        th{
            background:#F8F8F8;
            font-weight:600;
        }
        .actions-form{
            display:flex;
            gap:0.5rem;
        }
        .btn{
            border:none;
            border-radius:6px;
            padding:0.4rem 0.75rem;
            font-size:0.8rem;
            cursor:pointer;
            font-family:'Poppins',sans-serif;
        }
        .btn-primary{background:#0A5033;color:#fff;}
        .btn-danger{background:#DC3545;color:#fff;}
        .badge{
            display:inline-block;
            padding:0.15rem 0.5rem;
            border-radius:999px;
            background:#E8F5E9;
            color:#0A5033;
            font-size:0.75rem;
        }
        img.thumb{
            width:50px;
            height:50px;
            object-fit:cover;
            border-radius:6px;
        }
        @media(max-width:900px){
            body{flex-direction:column;}
            .sidebar{width:100%;flex-direction:row;align-items:center;justify-content:space-between;}
            .sidebar-nav{display:flex;gap:0.5rem;}
            .sidebar-footer{display:none;}
            table{font-size:0.8rem;}
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
                <li><a href="admin_products.php" class="active">Products</a></li>
                <li><a href="admin_orders.php">Orders</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?></strong><br>
            <a href="logout.php" style="color:#fff;text-decoration:none;">Logout</a>
        </div>
    </aside>
    <main class="main">
        <div class="topbar">
            <h1>Products</h1>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products && $products->num_rows > 0): ?>
                    <?php while ($p = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo (int)$p['id']; ?></td>
                            <td>
                                <?php if (!empty($p['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="" class="thumb">
                                <?php else: ?>
                                    <span class="badge">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td><span class="badge"><?php echo htmlspecialchars(ucfirst($p['category'])); ?></span></td>
                            <td><?php echo number_format($p['price'], 0); ?> Frw</td>
                            <td><?php echo (int)$p['stock']; ?></td>
                            <td>
                                <form method="POST" class="actions-form" onsubmit="return confirmDelete(event);">
                                    <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                    <button type="button" class="btn btn-primary" onclick="editProduct(<?php echo (int)$p['id']; ?>)">Edit</button>
                                    <button type="submit" name="action" value="delete" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No products yet. Use the Quick Add form on the dashboard to create one.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
    <script>
        function confirmDelete(e){
            if(!confirm('Delete this product?')){
                e.preventDefault();
                return false;
            }
            return true;
        }
        function editProduct(id){
            alert('For simplicity, edit is not implemented with inline form in this demo. You can extend admin_products.php to load a dedicated edit page for product ID '+id+'.');
        }
    </script>
</body>
</html>







