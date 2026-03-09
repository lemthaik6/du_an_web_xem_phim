<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>🚀 Login System Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%); color: white; padding: 40px; border-radius: 8px; margin-bottom: 30px; text-align: center; }
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        .card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h2 { color: #0066cc; border-bottom: 2px solid #0066cc; padding-bottom: 10px; margin-bottom: 15px; font-size: 20px; }
        .step { display: flex; align-items: flex-start; margin-bottom: 15px; }
        .step-number { background: #0066cc; color: white; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 15px; flex-shrink: 0; }
        .step p { margin: 0; }
        .button { display: inline-block; padding: 12px 24px; background: #0066cc; color: white; text-decoration: none; border-radius: 4px; margin-top: 10px; border: none; cursor: pointer; font-size: 14px; font-weight: bold; text-align: center; }
        .button:hover { background: #0052a3; }
        .button.secondary { background: #666; }
        .button.secondary:hover { background: #555; }
        .status { padding: 15px; border-radius: 4px; margin: 10px 0; }
        .status.ok { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .status.loading { background: #e2e3e5; color: #383d41; border: 1px solid #d6d8db; }
        .creds { background: #f0f0f0; padding: 15px; border-radius: 4px; border-left: 4px solid #0066cc; font-family: monospace; margin: 10px 0; }
        .test-links { margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        footer { text-align: center; color: #666; margin-top: 40px; padding: 20px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Login System Setup</h1>
            <p>Step-by-step guide to fix your authentication</p>
        </div>

        <div class="card">
            <h2>Step 1: Create Test User</h2>
            <div class="step">
                <div class="step-number">1</div>
                <div style="flex: 1;">
                    <p>First, we'll create a test user account in your database.</p>
                    <button class="button" onclick="setupTestUser()">Create Test User</button>
                    <div id="step1Status"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Step 2: Test Login Directly</h2>
            <div class="step">
                <div class="step-number">2</div>
                <div style="flex: 1;">
                    <p>Test if login works without using the route system.</p>
                    <div id="step2Status" class="status loading">⏳ Waiting for Step 1...</div>
                    <button class="button secondary" id="step2Btn" onclick="testDirectLogin()" disabled>Test Direct Login</button>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Step 3: Test Modal Login</h2>
            <div class="step">
                <div class="step-number">3</div>
                <div style="flex: 1;">
                    <p>Test the login modal via AJAX (how the real form works).</p>
                    <div id="step3Status" class="status loading">⏳ Waiting for Step 2...</div>
                    <button class="button secondary" id="step3Btn" onclick="testModalLogin()" disabled>Test Modal Login</button>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Step 4: Test via Route</h2>
            <div class="step">
                <div class="step-number">4</div>
                <div style="flex: 1;">
                    <p>Finally, test the actual login page route.</p>
                    <div id="step4Status" class="status loading">⏳ Waiting for Step 3...</div>
                    <div class="test-links">
                        <a href="/du_an_ca_nhan/du_an_web_xem_phim/" class="button secondary">Visit Home Page</a>
                        <a href="/du_an_ca_nhan/du_an_web_xem_phim/dang-nhap" class="button secondary">Visit Login Page</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>💡 Test Credentials</h2>
            <div class="creds">
                Email: test@test.com<br>
                Password: 123456
            </div>
        </div>

        <footer>
            <p>If you get 404 errors, check your .htaccess rewrite rules.</p>
            <p>If login fails, check browser console (F12) for JavaScript errors.</p>
        </footer>
    </div>

    <script>
        async function setupTestUser() {
            const statusDiv = document.getElementById('step1Status');
            statusDiv.innerHTML = '<div class="status loading">⏳ Creating user...</div>';
            
            try {
                const res = await fetch('create_test_user.php', { method: 'POST' });
                const data = await res.json();
                
                if (data.ok) {
                    statusDiv.innerHTML = '<div class="status ok"><strong>✅ ' + data.message + '</strong></div>';
                    document.getElementById('step2Btn').disabled = false;
                    document.getElementById('step2Status').innerHTML = '<p><strong>Ready to test!</strong> Click the button below to continue.</p>';
                } else {
                    statusDiv.innerHTML = '<div class="status error"><strong>❌ ' + data.error + '</strong></div>';
                }
            } catch (e) {
                statusDiv.innerHTML = '<div class="status error"><strong>❌ Error: ' + e.message + '</strong></div>';
            }
        }

        async function testDirectLogin() {
            const statusDiv = document.getElementById('step2Status');
            statusDiv.innerHTML = '<div class="status loading">⏳ Testing login...</div>';
            
            try {
                const formData = new FormData();
                formData.append('email', 'test@test.com');
                formData.append('password', '123456');
                
                const res = await fetch('test_login_api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await res.json();
                
                if (data.ok) {
                    statusDiv.innerHTML = '<div class="status ok"><strong>✅ ' + data.message + '</strong><br>User: ' + data.user.name + '</div>';
                    document.getElementById('step3Btn').disabled = false;
                    document.getElementById('step3Status').innerHTML = '<p><strong>Ready to test modal!</strong> Click the button below to continue.</p>';
                } else {
                    statusDiv.innerHTML = '<div class="status error"><strong>❌ ' + data.error + '</strong></div>';
                }
            } catch (e) {
                statusDiv.innerHTML = '<div class="status error"><strong>❌ Error: ' + e.message + '</strong></div>';
            }
        }

        async function testModalLogin() {
            const statusDiv = document.getElementById('step3Status');
            statusDiv.innerHTML = '<div class="status loading">⏳ Testing AJAX modal login...</div>';
            
            try {
                const formData = new FormData();
                formData.append('email', 'test@test.com');
                formData.append('password', '123456');
                
                const res = await fetch('/du_an_ca_nhan/du_an_web_xem_phim/dang-nhap', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                
                const data = await res.json();
                
                if (data.ok) {
                    statusDiv.innerHTML = '<div class="status ok"><strong>✅ Modal login works!</strong><br>' + data.message + '</div>';
                    document.getElementById('step4Status').innerHTML = '<p><strong>✅ All systems ready!</strong> You can now use the login form normally.</p>';
                } else {
                    statusDiv.innerHTML = '<div class="status error"><strong>❌ ' + (data.error || 'Unknown error') + '</strong></div>';
                }
            } catch (e) {
                statusDiv.innerHTML = '<div class="status error"><strong>❌ Error: ' + e.message + '</strong><br>This might be expected if the route doesn\'t return JSON. Check if login page still works.</div>';
            }
        }
    </script>
</body>
</html>
