<?php
session_start();

// DATABASE CONNECTION
require_once 'db_connection.php';

// FORM DATA
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// VALIDATION
if ($email === '' || $password === '') {
    header("Location: Login.php?error=Please fill in all fields");
    exit;
}

// ENSURE SINGLE ADMIN USER EXISTS IN DATABASE
$adminEmail = 'umubyeyialice7@123';
$adminPlainPassword = 'Alice@123';
$adminName = 'Alice';

$adminCheck = $conn->prepare("SELECT id FROM users WHERE email = ?");
$adminCheck->bind_param("s", $adminEmail);
$adminCheck->execute();
$adminCheck->store_result();

if ($adminCheck->num_rows === 0) {
    $adminCheck->close();
    $hash = password_hash($adminPlainPassword, PASSWORD_DEFAULT);
    $createAdmin = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, 'admin')");
    $phone = '000000000';
    $createAdmin->bind_param("ssss", $adminName, $adminEmail, $phone, $hash);
    $createAdmin->execute();
    $createAdmin->close();
} else {
    $adminCheck->close();
}

// NORMAL LOGIN (ADMIN OR CUSTOMER) FROM DATABASE
$stmt = $conn->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $full_name, $hashedPassword, $role);
    $stmt->fetch();

    if (password_verify($password, $hashedPassword)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['role'] = $role;

        if ($role === 'admin') {
            header("Location: admin_dashboard.php");
            exit;
        } else {
            header("Location: index.html?login=success");
            exit;
        }
    } else {
        header("Location: Login.php?error=Incorrect password");
        exit;
    }
} else {
    header("Location: Login.php?error=Email not found");
    exit;
}

$stmt->close();
?>
