<?php
// MTN Mobile Money API Integration

class MTNMoMo {
    private $apiUser;
    private $apiKey;
    private $subscriptionKey;
    private $environment;
    private $baseUrl;
    
    public function __construct($apiUser, $apiKey, $subscriptionKey, $environment = 'sandbox') {
        $this->apiUser = $apiUser;
        $this->apiKey = $apiKey;
        $this->subscriptionKey = $subscriptionKey;
        $this->environment = $environment;
        $this->baseUrl = $environment === 'sandbox' 
            ? 'https://sandbox.momodeveloper.mtn.com' 
            : 'https://proxy.momoapi.mtn.com';
    }
    
    public function getAccessToken() {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/collection/token/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode($this->apiUser . ':' . $this->apiKey),
                'Ocp-Apim-Subscription-Key: ' . $this->subscriptionKey
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
    
    public function requestToPay($amount, $phone, $referenceId, $message = '') {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to get access token'];
        }
        
        $payload = [
            'amount' => (string)$amount,
            'currency' => 'RWF',
            'externalId' => $referenceId,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => $phone
            ],
            'payerMessage' => $message ?: 'Payment for ClassKart order',
            'payeeNote' => 'Thank you for shopping with ClassKart!'
        ];
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/collection/v1_0/requesttopay',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'X-Reference-Id: ' . $referenceId,
                'X-Target-Environment: ' . $this->environment,
                'Ocp-Apim-Subscription-Key: ' . $this->subscriptionKey,
                'Content-Type: application/json'
            ],
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => ($httpCode === 202),
            'httpCode' => $httpCode,
            'referenceId' => $referenceId,
            'response' => $response
        ];
    }
    
    public function checkPaymentStatus($referenceId) {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return null;
        }
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/collection/v1_0/requesttopay/' . $referenceId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'X-Target-Environment: ' . $this->environment,
                'Ocp-Apim-Subscription-Key: ' . $this->subscriptionKey
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
}
?>