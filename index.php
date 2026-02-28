<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'config/database.php';
require_once 'models/User.php';

$error = '';
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (!empty($username) && !empty($password)) {
        $pdo = getDB();
        $userModel = new User($pdo);
        $result = $userModel->login($username, $password);
        
        if ($result['success']) {
            // Set session variables
            $_SESSION['user_id'] = $result['user']['user_id'];
            $_SESSION['username'] = $result['user']['username'];
            $_SESSION['full_name'] = $result['user']['full_name'];
            $_SESSION['email'] = $result['user']['email'];
            $_SESSION['role'] = $result['user']['role'];
            
            // Redirect to dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Please enter username and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Appointment System - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 100%;
            padding: 40px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .logo p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .demo-accounts {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 12px;
        }
        
        .demo-accounts h3 {
            color: #333;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .demo-accounts p {
            color: #666;
            margin: 5px 0;
        }
        
        .demo-accounts strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>
                <img src="assets/uploads/logo.png" alt="Logo" style="width: 40px; height: 40px;">
                BOOKSTORY</h1>
            <p>Appointment and Book Donation Management</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username or Email</label>
                <input type="text" name="username" required autofocus 
                       placeholder="Enter your username or email">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required 
                       placeholder="Enter your password">
            </div>
            
            <button type="submit" name="login" class="btn">Login</button>
        </form>
        
        <div class="links">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
        
        <div class="demo-accounts">
            <h4>Demo Accounts (Password: Password123) 🔑</h5>
            <p><strong>Admin:</strong> admin / admin@library.com</p>
            <p><strong>Librarian:</strong> librarian1 / librarian@library.com</p>
            <p><strong>Patron:</strong> patron1 / patron@example.com</p>
        </div>
    </div>
</body>
</html>