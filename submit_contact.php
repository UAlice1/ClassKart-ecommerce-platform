<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        // Get IP address
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        
        // Get user agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        
        // Validate required fields
        if (empty($firstName) || empty($lastName) || empty($email) || empty($subject) || empty($message)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please enter a valid email address.'
            ]);
            exit;
        }
        
        // Prepare SQL statement
        $sql = "INSERT INTO contact_submission 
                (first_name, last_name, email, phone, subject, message, ip_address, user_agent, submitted_at, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param(
            "ssssssss",
            $firstName,
            $lastName,
            $email,
            $phone,
            $subject,
            $message,
            $ipAddress,
            $userAgent
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Thank you! Your message has been sent successfully. We\'ll get back to you soon.'
            ]);
        } else {
            throw new Exception("Error submitting form: " . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred. Please try again later.',
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}

$conn->close();
?>