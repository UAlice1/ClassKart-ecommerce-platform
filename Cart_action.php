<?php 
include "db.php";

$action = $_POST['action'];
$product_id = $_POST['product_id'];
$user_id = 1; 

if ($action == "add") {

  
    $check = $conn->query("SELECT * FROM cart WHERE user_id=$user_id AND product_id=$product_id");
    
    if ($check->num_rows > 0) {
       
        $conn->query("UPDATE cart SET quantity = quantity + 1 
                      WHERE user_id=$user_id AND product_id=$product_id");
    } else {
      
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
