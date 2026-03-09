<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo Tài Khoản Test</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; }
        .box { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px; background: #f9f9f9; }
        h2 { color: #0066cc; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
        .cred { background: #f0f0f0; padding: 10px; margin: 5px 0; border-left: 3px solid #0066cc; font-family: monospace; }
        button { padding: 10px 20px; background: #0066cc; color: white; border: none; cursor: pointer; border-radius: 3px; font-weight: bold; }
        button:hover { background: #0052a3; }
        .success { color: green; font-weight: bold; }
        .status { margin-top: 15px; padding: 15px; border-radius: 3px; }
        .status.ok { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h1>👤 Tạo Tài Khoản Test</h1>

    <div class="box">
        <h2>Tài Khoản Mẫu</h2>
        <p>Click nút bên dưới để tạo các tài khoản test:</p>

        <button onclick="createUsers()">Tạo 3 Tài Khoản Test</button>
        <div id="status"></div>

        <h3 style="margin-top: 20px;">Tài khoản sẽ được tạo:</h3>
        <div class="cred">
            👤 test1@test.com<br>
            🔑 password: 123456
        </div>
        <div class="cred">
            👤 test2@test.com<br>
            🔑 password: 123456
        </div>
        <div class="cred">
            👤 test3@test.com<br>
            🔑 password: 123456
        </div>
    </div>

    <script>
        async function createUsers() {
            const statusDiv = document.getElementById('status');
            statusDiv.className = 'status ok';
            statusDiv.innerHTML = '⏳ Đang tạo tài khoản...';

            try {
                const res = await fetch('add_test_accounts.php', { method: 'POST' });
                const data = await res.json();

                if (data.ok) {
                    statusDiv.className = 'status ok';
                    statusDiv.innerHTML = '<h3>✅ Thành công!</h3><p>' + data.message + '</p>';
                } else {
                    statusDiv.className = 'status error';
                    statusDiv.innerHTML = '<h3>❌ Lỗi</h3><p>' + data.error + '</p>';
                }
            } catch (e) {
                statusDiv.className = 'status error';
                statusDiv.innerHTML = '<h3>❌ Lỗi</h3><p>' + e.message + '</p>';
            }

            // Reload page after 2 seconds
            setTimeout(() => location.reload(), 2000);
        }
    </script>
</body>
</html>
