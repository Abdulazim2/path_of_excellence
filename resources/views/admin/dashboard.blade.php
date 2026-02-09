<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; }
        .header { background: #4267B2; color: white; padding: 20px; display: flex; justify-content: space-between; }
        .container { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .nav a { color: white; text-decoration: none; margin: 0 15px; }
        .logout { background: #dc3545; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <div class="nav">
            <a href="/admin/users">Users</a>
            <a href="/admin/courses">Courses</a>
            <form method="POST" action="/logout" style="display: inline;">
                @csrf
                <button type="submit" class="logout">Logout</button>
            </form>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>Welcome, {{ Auth::user()->name }}!</h2>
            <p>You are logged in as Administrator.</p>
        </div>
    </div>
</body>
</html>