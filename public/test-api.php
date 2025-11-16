<!DOCTYPE html>
<html>
<head>
    <title>API Test</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .test { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>API Test - ROBBOEB Libra</h1>
    <button onclick="testAll()">Test All APIs</button>
    <div id="results"></div>
    
    <script>
        const API_BASE = '/library-pro/api';
        
        async function testAPI(name, endpoint) {
            const resultsDiv = document.getElementById('results');
            const testDiv = document.createElement('div');
            testDiv.className = 'test';
            testDiv.innerHTML = `<h3>${name}</h3><p>Testing ${endpoint}...</p>`;
            resultsDiv.appendChild(testDiv);
            
            try {
                const response = await fetch(API_BASE + endpoint, {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                
                if (data.success) {
                    testDiv.className = 'test success';
                    testDiv.innerHTML = `
                        <h3>✓ ${name}</h3>
                        <p><strong>Status:</strong> ${response.status} OK</p>
                        <p><strong>Data count:</strong> ${Array.isArray(data.data) ? data.data.length : 'N/A'}</p>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                } else {
                    testDiv.className = 'test error';
                    testDiv.innerHTML = `
                        <h3>✗ ${name}</h3>
                        <p><strong>Status:</strong> ${response.status}</p>
                        <p><strong>Error:</strong> ${data.error?.message || 'Unknown error'}</p>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                }
            } catch (error) {
                testDiv.className = 'test error';
                testDiv.innerHTML = `
                    <h3>✗ ${name}</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                `;
            }
        }
        
        async function testAll() {
            document.getElementById('results').innerHTML = '';
            
            await testAPI('Dashboard Stats', '/reports/dashboard');
            await testAPI('Books List', '/books');
            await testAPI('Users List', '/users');
            await testAPI('Loans List', '/loans');
            await testAPI('Categories List', '/categories');
            await testAPI('Authors List', '/authors');
            await testAPI('Popular Books', '/reports/popular-books');
            await testAPI('Active Users', '/reports/active-users');
            await testAPI('Category Distribution', '/reports/categories');
        }
    </script>
</body>
</html>
