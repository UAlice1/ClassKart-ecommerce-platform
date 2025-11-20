<?php
session_start();
include "db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullName = trim($_POST["fullName"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

    // VALIDATIONS
    if (empty($fullName) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword)) {
        header("Location: signup.php?error=Please fill in all fields.");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: signup.php?error=Invalid email format.");
        exit();
    }

    if ($password !== $confirmPassword) {
        header("Location: signup.php?error=Passwords do not match.");
        exit();
    }

    if (strlen($password) < 8) {
        header("Location: signup.php?error=Password must be at least 8 characters.");
        exit();
    }

    // Check if email exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        header("Location: signup.php?error=Email is already registered.");
        exit();
    }

    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);


    $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullName, $email, $phone, $hashedPassword);

    if ($stmt->execute()) {
        header("Location: signup.php?success=Account created successfully! Please login.");
    } else {
        header("Location: signup.php?error=Something went wrong. Try again.");
    }

    $stmt->close();
    $conn->close();
}
?>
