<!DOCTYPE html>
<html>
<head>
    <title>Test Payment Connection</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .result {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        button {
            background: #0A5033;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #084028;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🧪 Payment System Test</h1>
    
    <div id="result"></div>
    
    <button onclick="testPayment()">Test Payment Processing</button>
    
    <h2>Debug Information:</h2>
    <pre id="debug"></pre>
    
    <script>
        async function testPayment() {
            const resultDiv = document.getElementById('result');
            const debugDiv = document.getElementById('debug');
            
            resultDiv.innerHTML = '<div class="result">Testing connection...</div>';
            
            const testData = {
                fullName: 'Test User',
                email: 'test@example.com',
                phone: '250788888888',
                amount: 5000,
                street: 'Test Street',
                city: 'Kigali',
                state: 'Gasabo'
            };
            
            debugDiv.textContent = 'Request Data:\n' + JSON.stringify(testData, null, 2);
            
            try {
                const response = await fetch('process_momo_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(testData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    resultDiv.innerHTML = `
                        <div class="result success">
                            <h3> SUCCESS!</h3>
                            <p>${result.message}</p>
                            <p><strong>Order ID:</strong> ${result.orderId}</p>
                            <p><strong>Reference:</strong> ${result.referenceId}</p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="result error">
                            <h3>❌ FAILED</h3>
                            <p>${result.message}</p>
                        </div>
                    `;
                }
                
                debugDiv.textContent += '\n\nResponse Data:\n' + JSON.stringify(result, null, 2);
                
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="result error">
                        <h3>❌ CONNECTION ERROR</h3>
                        <p>${error.message}</p>
                        <p><strong>Possible causes:</strong></p>
                        <ul>
                            <li>process_momo_payment.php file doesn't exist</li>
                            <li>File is in wrong location</li>
                            <li>PHP syntax error in the file</li>
                            <li>Database connection error</li>
                        </ul>
                    </div>
                `;
                
                debugDiv.textContent += '\n\nError:\n' + error.toString();
            }
        }
    </script>
    
    <h2>Checklist:</h2>
    <ul>
        <li>✓ Upload process_momo_payment.php to your server</li>
        <li>✓ Make sure db_connection.php exists and works</li>
        <li>✓ Run database_schema.sql to create tables</li>
        <li>✓ Check file permissions (should be readable)</li>
        <li>✓ Check PHP error logs</li>
    </ul>
    
    <h2>Check Database Tables:</h2>
    <p>Run this SQL to verify tables exist:</p>
    <pre>SHOW TABLES LIKE 'orders';
SHOW TABLES LIKE 'order_items';
SHOW TABLES LIKE 'payment_logs';</pre>
    
</body>
</html>