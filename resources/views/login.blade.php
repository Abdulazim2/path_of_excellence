<!DOCTYPE html>
<html>
<head>
    <title>Login - Path of Excellence</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #f0f2f5; 
            margin: 0; 
            padding: 20px; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
        }
        .login-box { 
            background: white; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 0 20px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 400px; 
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
            background: #4267B2; 
            color: white; 
            border: none; 
            font-weight: bold; 
            cursor: pointer; 
        }
        button:hover { 
            background: #365899; 
        }
        .error { 
            color: red; 
            margin: 10px 0; 
        }
        .switch { 
            text-align: center; 
            margin: 15px 0; 
        }
        .switch a { 
            color: #4267B2; 
            text-decoration: none; 
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 style="text-align: center; color: #4267B2; margin-bottom: 20px;">
            Path of Excellence
        </h2>
        
        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif
        
        <form method="POST" action="/login" id="loginForm">
            @csrf
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        
        <div class="switch">
            <a href="#" onclick="showRegister()">Create New Account</a>
        </div>
        
        <form method="POST" action="/register" id="registerForm" style="display: none;">
            @csrf
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
            <select name="role" required>
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>
            <button type="submit">Register</button>
        </form>
        
        <div class="switch" id="backToLogin" style="display: none;">
            <a href="#" onclick="showLogin()">Back to Login</a>
        </div>
    </div>

    <script>
        function showRegister() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
            document.getElementById('backToLogin').style.display = 'block';
        }
        
        function showLogin() {
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('backToLogin').style.display = 'none';
        }
    </script>
</body>
</html>