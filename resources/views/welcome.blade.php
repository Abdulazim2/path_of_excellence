<!DOCTYPE html>
<html>
<head>
    <title>Path of Excellence - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #667eea;
            margin: 0;
        }
        input, select, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            opacity: 0.9;
        }
        .links {
            text-align: center;
            margin: 15px 0;
        }
        .links a {
            color: #667eea;
            text-decoration: none;
        }
        .message {
            text-align: center;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>Path of Excellence</h1>
            <p>Educational Platform</p>
        </div>
        
        <div id="loginForm">
            <h2 style="text-align: center; margin-bottom: 20px;">Login</h2>
            <form id="loginFormElement">
                <input type="email" id="email" placeholder="Email" required>
                <input type="password" id="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            
            <div class="links">
                <p>Don't have an account? <a href="#" onclick="showRegister()">Register</a></p>
            </div>
            
            <div id="message"></div>
        </div>
        
        <div id="registerForm" style="display: none;">
            <h2 style="text-align: center; margin-bottom: 20px;">Register</h2>
            <form id="registerFormElement">
                <input type="text" id="regName" placeholder="Full Name" required>
                <input type="email" id="regEmail" placeholder="Email" required>
                <input type="password" id="regPassword" placeholder="Password" required>
                <input type="password" id="regPasswordConfirm" placeholder="Confirm Password" required>
                <select id="regRole" required>
                    <option value="">Select Role</option>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                </select>
                <button type="submit">Register</button>
            </form>
            
            <div class="links">
                <p>Already have an account? <a href="#" onclick="showLogin()">Login</a></p>
            </div>
            
            <div id="regMessage"></div>
        </div>
    </div>

    <script>
        // Check if already logged in
        const token = localStorage.getItem('auth_token');
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        
        if (token && user.role) {
            window.location.href = '/' + user.role;
        }
        
        const API_BASE = '/api';
        const messageDiv = document.getElementById('message');
        const regMessageDiv = document.getElementById('regMessage');
        
        function showRegister() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
            messageDiv.innerHTML = '';
            regMessageDiv.innerHTML = '';
        }
        
        function showLogin() {
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
            messageDiv.innerHTML = '';
            regMessageDiv.innerHTML = '';
        }
        
        // Login handler
        document.getElementById('loginFormElement').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            try {
                const response = await fetch(API_BASE + '/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
                });
                
                // Check if response is JSON
                const contentType = response.headers.get("content-type");
                let data;
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    data = await response.json();
                } else {
                    const text = await response.text();
                    console.error("Non-JSON response:", text);
                    throw new Error("Server returned non-JSON response: " + text.substring(0, 100));
                }
                
                if (response.ok) {
                    localStorage.setItem('auth_token', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    messageDiv.innerHTML = '<div class="message success">Login successful! Redirecting...</div>';
                    
                    // Simple redirect
                    setTimeout(() => {
                        window.location.href = '/' + data.user.role;
                    }, 1000);
                } else {
                    messageDiv.innerHTML = '<div class="message error">' + (data.message || 'Login failed') + '</div>';
                }
            } catch (error) {
                console.error("Login Error:", error);
                messageDiv.innerHTML = '<div class="message error">Network error: ' + error.message + '</div>';
            }
        });
        
        // Register handler
        document.getElementById('registerFormElement').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const name = document.getElementById('regName').value;
            const email = document.getElementById('regEmail').value;
            const password = document.getElementById('regPassword').value;
            const passwordConfirm = document.getElementById('regPasswordConfirm').value;
            const role = document.getElementById('regRole').value;
            
            if (password !== passwordConfirm) {
                regMessageDiv.innerHTML = '<div class="message error">Passwords do not match</div>';
                return;
            }
            
            try {
                const response = await fetch(API_BASE + '/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: name,
                        email: email,
                        password: password,
                        password_confirmation: passwordConfirm,
                        role: role
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    localStorage.setItem('auth_token', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    regMessageDiv.innerHTML = '<div class="message success">Registration successful! Redirecting...</div>';
                    
                    // Simple redirect
                    setTimeout(() => {
                        window.location.href = '/' + data.user.role;
                    }, 1000);
                } else {
                    regMessageDiv.innerHTML = '<div class="message error">' + (data.message || 'Registration failed') + '</div>';
                }
            } catch (error) {
                regMessageDiv.innerHTML = '<div class="message error">Network error. Please try again.</div>';
            }
        });
    </script>
</body>
</html>