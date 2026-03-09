<!DOCTYPE html>
<html>
<head>
    <title>Test Login Form</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; }
        .box { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        input, button { padding: 8px; margin: 5px 0; width: 100%; }
        button { background: #0066cc; color: white; cursor: pointer; border: none; border-radius: 3px; }
        button:hover { background: #0052a3; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        .log { border-left: 4px solid #ccc; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🧪 Test Login Form</h1>
    
    <div class="box">
        <h2>Form Test (Direct POST)</h2>
        <form action="/du_an_ca_nhan/du_an_web_xem_phim/dang-nhap" method="POST">
            <input type="email" name="email" placeholder="Email" value="test@test.com" required>
            <input type="password" name="password" placeholder="Password" value="123456" required>
            <button type="submit">Đăng nhập (Direct POST)</button>
        </form>
    </div>
    
    <div class="box">
        <h2>Form Test (AJAX)</h2>
        <div id="status"></div>
        <form id="ajaxForm">
            <input type="email" name="email" placeholder="Email" value="test@test.com" required>
            <input type="password" name="password" placeholder="Password" value="123456" required>
            <button type="submit">Đăng nhập (AJAX)</button>
        </form>
    </div>
    
    <div class="box">
        <h2>Request Log</h2>
        <div id="log"></div>
    </div>

    <script>
        const statusDiv = document.getElementById('status');
        const logDiv = document.getElementById('log');
        
        function log(msg) {
            console.log(msg);
            logDiv.innerHTML += `<div class="log">${new Date().toLocaleTimeString()}: ${msg}</div>`;
        }
        
        document.getElementById('ajaxForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            log('📤 Submitting AJAX form...');
            
            const formData = new FormData(e.target);
            
            log('📦 Form data: email=' + formData.get('email') + ', password=***');
            
            try {
                log('🌐 Sending to: /du_an_ca_nhan/du_an_web_xem_phim/dang-nhap');
                const res = await fetch('/du_an_ca_nhan/du_an_web_xem_phim/dang-nhap', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'multipart/form-data',
                    },
                    body: formData,
                });
                
                log('📥 Response status: ' + res.status);
                log('📥 Response headers: ' + JSON.stringify({
                    'content-type': res.headers.get('content-type')
                }));
                
                const text = await res.text();
                log('📥 Response body (first 200 chars): ' + text.substring(0, 200));
                
                if (res.headers.get('content-type')?.includes('application/json')) {
                    const data = JSON.parse(text);
                    log('✅ JSON parsed: ' + JSON.stringify(data));
                    
                    if (data.ok) {
                        statusDiv.innerHTML = '<p class="success">✅ Login success! ' + data.message + '</p>';
                    } else {
                        statusDiv.innerHTML = '<p class="error">❌ ' + data.error + '</p>';
                    }
                } else {
                    log('❌ Response is not JSON!');
                    statusDiv.innerHTML = '<p class="error">❌ Server response is not JSON. Check browser console.</p>';
                }
                
            } catch (err) {
                log('❌ Error: ' + err.message);
                statusDiv.innerHTML = '<p class="error">❌ ' + err.message + '</p>';
            }
        });
    </script>
</body>
</html>
