<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/database.php';
require_once 'models/User.php';

$pdo = getDB();
$userModel = new User($pdo);

$user = $userModel->getUserById($_SESSION['user_id']);
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    if ($userModel->updateProfile($_SESSION['user_id'], $full_name, $phone, $email)) {
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        $success = 'Profile updated successfully!';
        $user = $userModel->getUserById($_SESSION['user_id']);
    } else {
        $error = 'Failed to update profile';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } else {
        $result = $userModel->changePassword($_SESSION['user_id'], $old_password, $new_password);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>👤 My Profile</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="profile-layout">
            <!-- Profile Info Section -->
            <div class="section">
                <h2>Profile Information</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" 
                               value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small class="form-text">Username cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Full Name <span class="required">*</span></label>
                        <input type="text" name="full_name" class="form-control" required
                               value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" required
                               value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="form-control"
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" class="form-control" 
                               value="<?php echo htmlspecialchars($user['role']); ?>" disabled>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        ✅ Update Profile
                    </button>
                </form>
            </div>
            
            <!-- Change Password Section -->
            <div class="section">
                <h2>Change Password</h2>
                <form method="POST" id="passwordForm">
                    <div class="form-group">
                        <label>Current Password <span class="required">*</span></label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>New Password <span class="required">*</span></label>
                        <input type="password" name="new_password" id="newPassword" 
                               class="form-control" required minlength="8">
                        <small class="form-text">
                            Minimum 8 characters with uppercase, lowercase, and number
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm New Password <span class="required">*</span></label>
                        <input type="password" name="confirm_password" id="confirmPassword" 
                               class="form-control" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-warning">
                        🔐 Change Password
                    </button>
                </form>
            </div>
            
            <!-- Account Info Sidebar -->
            <div class="profile-sidebar">
                <div class="info-card">
                    <h3>Account Information</h3>
                    <div class="info-item">
                        <span class="label">User ID:</span>
                        <span class="value"><?php echo $user['user_id']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Member Since:</span>
                        <span class="value"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Last Login:</span>
                        <span class="value">
                            <?php echo $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : 'N/A'; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Account Status:</span>
                        <span class="value">
                            <?php if ($user['is_active']): ?>
                                <span class="badge badge-confirmed">Active</span>
                            <?php else: ?>
                                <span class="badge badge-cancelled">Inactive</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-card">
                    <h3>Quick Actions</h3>
                    <a href="my_appointments.php" class="btn btn-secondary btn-block">
                        📅 My Appointments
                    </a>
                    <a href="my_donations.php" class="btn btn-secondary btn-block">
                        📚 My Donations
                    </a>
                    <a href="dashboard.php" class="btn btn-secondary btn-block">
                        🏠 Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <style>
    .profile-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }
    .profile-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .info-card {
        background: white;
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        padding: 1.5rem;
    }
    .info-card h3 {
        color: var(--dark-color);
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--gray-200);
    }
    .info-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--gray-100);
    }
    .info-item:last-child {
        border-bottom: none;
    }
    .info-item .label {
        font-weight: 600;
        color: var(--dark-color);
    }
    .info-item .value {
        color: var(--gray-700);
        text-align: right;
    }
    .btn-block {
        width: 100%;
        margin-bottom: 0.75rem;
    }
    @media (max-width: 968px) {
        .profile-layout {
            grid-template-columns: 1fr;
        }
    }
    </style>
    
    <script>
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const newPass = document.getElementById('newPassword').value;
        const confirmPass = document.getElementById('confirmPassword').value;
        
        if (newPass !== confirmPass) {
            e.preventDefault();
            alert('New passwords do not match!');
            return false;
        }
        
        // Validate password strength
        if (newPass.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long');
            return false;
        }
        
        if (!/[A-Z]/.test(newPass) || !/[a-z]/.test(newPass) || !/[0-9]/.test(newPass)) {
            e.preventDefault();
            alert('Password must contain uppercase, lowercase, and a number');
            return false;
        }
    });
    </script>
</body>
</html>