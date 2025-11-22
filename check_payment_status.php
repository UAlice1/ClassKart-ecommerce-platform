<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['ref'])) {
    echo json_encode(['success' => false, 'message' => 'Reference ID required']);
    exit;
}

$referenceId = $_GET['ref'];

// Configuration (same as process_momo_payment.php)
define('MOMO_API_USER', 'your_api_user_here');
define('MOMO_API_KEY', 'your_api_key_here');
define('MOMO_SUBSCRIPTION_KEY', 'your_subscription_key_here');
define('MOMO_ENVIRONMENT', 'sandbox');

if (MOMO_ENVIRONMENT === 'sandbox') {
    define('MOMO_BASE_URL', 'https://sandbox.momodeveloper.mtn.com');
} else {
    define('MOMO_BASE_URL', 'https://proxy.momoapi.mtn.com');
}

function getMomoAccessToken() {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => MOMO_BASE_URL . '/collection/token/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . base64_encode(MOMO_API_USER . ':' . MOMO_API_KEY),
            'Ocp-Apim-Subscription-Key: ' . MOMO_SUBSCRIPTION_KEY
        ],
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        return $result['access_token'] ?? null;
    }
    
    return null;
}

function checkPaymentStatus($referenceId, $accessToken) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => MOMO_BASE_URL . '/collection/v1_0/requesttopay/' . $referenceId,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'X-Target-Environment: ' . MOMO_ENVIRONMENT,
            'Ocp-Apim-Subscription-Key: ' . MOMO_SUBSCRIPTION_KEY
        ],
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return null;
}

try {
    // Get access token
    $accessToken = getMomoAccessToken();
    
    if (!$accessToken) {
        throw new Exception('Failed to authenticate');
    }
    
    // Check payment status
    $status = checkPaymentStatus($referenceId, $accessToken);
    
    if ($status) {
        $paymentStatus = $status['status']; // PENDING, SUCCESSFUL, FAILED
        
        // Update order in database
        $stmt = $conn->prepare("UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE reference_id = ?");
        $dbStatus = strtolower($paymentStatus);
        $stmt->bind_param('ss', $dbStatus, $referenceId);
        $stmt->execute();
        $stmt->close();
        
        // Clear cart if payment successful
        if ($paymentStatus === 'SUCCESSFUL') {
            $_SESSION['cart'] = [];
        }
        
        echo json_encode([
            'success' => true,
            'status' => $paymentStatus,
            'message' => $paymentStatus === 'SUCCESSFUL' ? 'Payment completed successfully!' : 
                        ($paymentStatus === 'FAILED' ? 'Payment failed' : 'Payment pending')
        ]);
    } else {
        throw new Exception('Failed to check payment status');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>