<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Direct Login Test</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 40px auto; }
        .section { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px; background: #f9f9f9; }
        h2 { color: #0066cc; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        form { background: white; padding: 20px; border: 1px solid #ddd; }
        input { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #0066cc; color: white; font-weight: bold; border: none; cursor: pointer; }
        button:hover { background: #0052a3; }
        #result { margin-top: 20px; padding: 15px; border-radius: 5px; display: none; }
        #result.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; display: block; }
        #result.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; display: block; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
    <h1>🧪 Direct Login Test</h1>
    
    <div class="section">
        <h2>Database Check</h2>
        <div id="dbStatus">Loading...</div>
    </div>

    <div class="section">
        <h2>Test Login</h2>
        <form id="loginForm">
            <input type="email" name="email" placeholder="Email" value="test@test.com" required>
            <input type="password" name="password" placeholder="Password" value="123456" required>
            <button type="submit">Test Login</button>
        </form>
        <div id="result"></div>
    </div>

    <script>
        // Step 1: Check database
        async function checkDatabase() {
            try {
                const res = await fetch('check_db_status.php');
                const html = await res.text();
                document.getElementById('dbStatus').innerHTML = html;
            } catch (e) {
                document.getElementById('dbStatus').innerHTML = '<p class="error">❌ Failed to check database: ' + e.message + '</p>';
            }
        }

        // Step 2: Test login
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const resultDiv = document.getElementById('result');
            
            try {
                const formData = new FormData(e.target);
                console.log('Sending login request...');
                
                const res = await fetch('test_login_api.php', {
                    method: 'POST',
                    body: formData,
                });
                
                console.log('Response status:', res.status);
                const data = await res.json();
                console.log('Response data:', data);
                
                if (data.ok) {
                    resultDiv.className = 'success';
                    resultDiv.innerHTML = '<strong>✅ Login Success!</strong><p>' + data.message + '</p>';
                } else {
                    resultDiv.className = 'error';
                    resultDiv.innerHTML = '<strong>❌ Login Failed</strong><p>' + data.error + '</p>';
                }
            } catch (e) {
                console.error('Error:', e);
                resultDiv.className = 'error';
                resultDiv.innerHTML = '<strong>❌ Request Error</strong><p>' + e.message + '</p>';
            }
        });

        // Auto check database on load
        checkDatabase();
    </script>
</body>
</html>
