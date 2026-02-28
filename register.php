<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'config/database.php';
require_once 'models/User.php';

$errors = [];
$success = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    
    // Validate passwords match
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($errors)) {
        $pdo = getDB();
        $userModel = new User($pdo);
        $result = $userModel->register($username, $email, $password, $full_name, $phone);
        
        if ($result['success']) {
            $success = 'Registration successful! You can now login.';
            // Clear form
            $username = $email = $full_name = $phone = '';
        } else {
            $errors = $result['errors'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Library System</title>
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
        
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
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
        
        .required {
            color: #e74c3c;
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
        
        .alert-error ul {
            margin-left: 20px;
            margin-top: 5px;
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
        
        .password-requirements {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>📚 Library System</h1>
            <p>Create Your Account</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Please fix the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username <span class="required">*</span></label>
                <input type="text" name="username" required 
                       value="<?php echo htmlspecialchars($username ?? ''); ?>"
                       placeholder="Choose a username (min 3 characters)">
            </div>
            
            <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <input type="email" name="email" required 
                       value="<?php echo htmlspecialchars($email ?? ''); ?>"
                       placeholder="your.email@example.com">
            </div>
            
            <div class="form-group">
                <label>Full Name <span class="required">*</span></label>
                <input type="text" name="full_name" required 
                       value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                       placeholder="Your full name">
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" 
                       value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                       placeholder="0771234567">
            </div>
            
            <div class="form-group">
                <label>Password <span class="required">*</span></label>
                <input type="password" name="password" required 
                       placeholder="Create a strong password">
                <div class="password-requirements">
                    Password must be at least 8 characters and include uppercase, lowercase, and a number
                </div>
            </div>
            
            <div class="form-group">
                <label>Confirm Password <span class="required">*</span></label>
                <input type="password" name="confirm_password" required 
                       placeholder="Re-enter your password">
            </div>
            
            <button type="submit" class="btn">Register</button>
        </form>
        
        <div class="links">
            Already have an account? <a href="index.php">Login here</a>
        </div>
    </div>
</body>
</html>