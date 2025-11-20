<?php
session_start();

// DATABASE CONNECTION
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "classkart";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// FORM DATA
$email = trim($_POST['email']);
$password = trim($_POST['password']);

// VALIDATION
if (empty($email) || empty($password)) {
    header("Location: login.html?error=Please fill in all fields");
    exit;
}

// Check if email exists
$stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 1) {
    $stmt->bind_result($id, $full_name, $hashedPassword);
    $stmt->fetch();

    // Verify password
    if (password_verify($password, $hashedPassword)) {

        // Store session
        $_SESSION['user_id'] = $id;
        $_SESSION['full_name'] = $full_name;

        header("Location: index.html?login=success");
        exit;

    } else {
        header("Location: login.html?error=Incorrect password");
        exit;
    }

} else {
    header("Location: login.html?error=Email not found");
    exit;
}

$stmt->close();
$conn->close();
?>
