<?php 
include "db.php";

$action = $_POST['action'];
$product_id = $_POST['product_id'];
$user_id = 1; // For now static (you will replace with logged-in user id)

if ($action == "add") {

    // Check if product already in cart
    $check = $conn->query("SELECT * FROM cart WHERE user_id=$user_id AND product_id=$product_id");
    
    if ($check->num_rows > 0) {
        // Update quantity
        $conn->query("UPDATE cart SET quantity = quantity + 1 
                      WHERE user_id=$user_id AND product_id=$product_id");
    } else {
        // Add new item
        $conn->query("INSERT INTO cart (user_id, product_id, quantity)
                      VALUES ($user_id, $product_id, 1)");
    }

    echo "added";
}



if ($action == "remove") {
    $conn->query("DELETE FROM cart WHERE user_id=$user_id AND product_id=$product_id");
    echo "removed";
}



if ($action == "update") {
    $qty = $_POST['quantity'];
    $conn->query("UPDATE cart SET quantity=$qty 
                  WHERE user_id=$user_id AND product_id=$product_id");
    echo "updated";
}
?>
