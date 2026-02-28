<?php
class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Register new user

    public function register($username, $email, $password, $full_name, $phone = null) {
        // Validate inputs
        $errors = [];
        
        if (strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters";
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        }
        
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain uppercase, lowercase, and number";
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Check if username exists
        $stmt = $this->pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return ['success' => false, 'errors' => ['Username already taken']];
        }
        
        // Check if email exists
        $stmt = $this->pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'errors' => ['Email already registered']];
        }
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Insert user
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, email, password_hash, full_name, phone) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        try {
            $stmt->execute([$username, $email, $password_hash, $full_name, $phone]);
            $user_id = $this->pdo->lastInsertId();
            
            // Log registration
            $this->logActivity($user_id, 'REGISTER', 'users', $user_id, 'New user registered');
            
            return ['success' => true, 'user_id' => $user_id];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['Registration failed: ' . $e->getMessage()]];
        }
    }
    
    //Authenticate user login

    public function login($username, $password) {
        $stmt = $this->pdo->prepare("
            SELECT user_id, username, email, password_hash, full_name, role, is_active, failed_login_attempts 
            FROM users 
            WHERE (username = ? OR email = ?)
        ");
        
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Check if account is active
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Account is suspended'];
        }
        
        // Check failed attempts
        if ($user['failed_login_attempts'] >= 5) {
            return ['success' => false, 'message' => 'Account locked due to too many failed attempts. Contact admin.'];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $this->incrementFailedAttempts($user['user_id']);
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Reset failed attempts
        $this->resetFailedAttempts($user['user_id']);
        
        // Update last login
        $this->updateLastLogin($user['user_id']);
        
        // Log activity
        $this->logActivity($user['user_id'], 'LOGIN', 'users', $user['user_id'], 'User logged in');
        
        // Return user data
        unset($user['password_hash']);
        unset($user['failed_login_attempts']);
        
        return ['success' => true, 'user' => $user];
    }
    
    // Get user by ID

    public function getUserById($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT user_id, username, email, full_name, phone, role, is_active, created_at, last_login 
            FROM users 
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
    
    //Get all users (admin only)

    public function getAllUsers() {
        $stmt = $this->pdo->query("
            SELECT user_id, username, email, full_name, role, is_active, created_at, last_login 
            FROM users 
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll();
    }
    
    //Update user profile

    public function updateProfile($user_id, $full_name, $phone, $email) {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET full_name = ?, phone = ?, email = ? 
            WHERE user_id = ?
        ");
        return $stmt->execute([$full_name, $phone, $email, $user_id]);
    }
    
    //Change password

    public function changePassword($user_id, $old_password, $new_password) {
        // Get current password
        $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!password_verify($old_password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        // Validate new password
        if (strlen($new_password) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters'];
        }
        
        // Hash new password
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Update password
        $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->execute([$new_hash, $user_id]);
        
        $this->logActivity($user_id, 'PASSWORD_CHANGE', 'users', $user_id, 'Password changed');
        
        return ['success' => true, 'message' => 'Password changed successfully'];
    }
    
    //Update user role (admin only)
     
    public function updateRole($user_id, $role) {
        $stmt = $this->pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        return $stmt->execute([$role, $user_id]);
    }
    
    //Toggle user active status (admin only)
    
    public function toggleActive($user_id) {
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    }
    
    //Get user statistics
   
    public function getUserStats($user_id) {
        $stmt = $this->pdo->prepare("CALL GetUserStats(?)");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
    
    // Helper methods
    
    private function incrementFailedAttempts($user_id) {
        $stmt = $this->pdo->prepare("UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }
    
    private function resetFailedAttempts($user_id) {
        $stmt = $this->pdo->prepare("UPDATE users SET failed_login_attempts = 0 WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }
    
    private function updateLastLogin($user_id) {
        $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }
    
    private function logActivity($user_id, $action, $table, $record_id, $details = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO audit_logs (user_id, action, table_affected, record_id, details, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt->execute([$user_id, $action, $table, $record_id, $details, $ip]);
    }
}
?>