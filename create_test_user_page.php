<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Create Test User</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 40px auto; padding: 20px; }
        .section { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px; background: #f9f9f9; }
        h2 { color: #0066cc; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        button { padding: 10px 20px; background: #0066cc; color: white; border: none; cursor: pointer; font-weight: bold; border-radius: 3px; }
        button:hover { background: #0052a3; }
        .info { padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 3px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>👤 Create Test User</h1>

    <div class="section">
        <h2>Auto Setup</h2>
        <p>Click the button below to automatically:</p>
        <ul>
            <li>Create users table (if not exists)</li>
            <li>Add test user: test@test.com / 123456</li>
        </ul>
        <button onclick="createTestUser()">Create Test User</button>
        <div id="status"></div>
    </div>

    <div class="info">
        <strong>Test Credentials:</strong><br>
        Email: <code>test@test.com</code><br>
        Password: <code>123456</code>
    </div>

    <script>
        async function createTestUser() {
            const btn = event.target;
            const statusDiv = document.getElementById('status');
            
            btn.disabled = true;
            statusDiv.innerHTML = '⏳ Processing...';
            
            try {
                const res = await fetch('create_test_user.php', { method: 'POST' });
                const data = await res.json();
                
                if (data.ok) {
                    statusDiv.innerHTML = '<p class="ok">✅ ' + data.message + '</p>';
                    btn.style.display = 'none';
                    statusDiv.innerHTML += '<div class="info"><a href="direct_test.php">👉 Go to Direct Test</a></div>';
                } else {
                    statusDiv.innerHTML = '<p class="error">❌ ' + data.error + '</p>';
                }
            } catch (e) {
                statusDiv.innerHTML = '<p class="error">❌ Error: ' + e.message + '</p>';
            } finally {
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
