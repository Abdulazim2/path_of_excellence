<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard - Path of Excellence</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .nav {
            display: flex;
            gap: 20px;
        }
        .nav a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            background: rgba(255,255,255,0.2);
        }
        .nav a:hover {
            background: rgba(255,255,255,0.3);
        }
        button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Teacher Dashboard</h1>
        <div class="nav">
            <a href="#">My Courses</a>
            <a href="#">Create Course</a>
            <a href="#">Students</a>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>Welcome, {{ Auth::user()->name }}!</h2>
            <p>You are logged in as Teacher.</p>
        </div>
        
        <div class="card">
            <h3>Your Courses</h3>
            <p>Create and manage your courses from this dashboard.</p>
        </div>
    </div>
</body>
</html>