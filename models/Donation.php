<?php
class Donation {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Submit new donation

    public function submitDonation($user_id, $data, $cover_image = null) {
        // Validate required fields
        $required = ['book_title', 'author', 'category', 'book_condition', 'quantity'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => 'All required fields must be filled'];
            }
        }
        
        // Generate donation reference
        $donation_ref = 'DON-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO donations 
                (user_id, book_title, author, isbn, category, book_condition, quantity, description, cover_image, donation_reference) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $user_id,
                $data['book_title'],
                $data['author'],
                $data['isbn'] ?? null,
                $data['category'],
                $data['book_condition'],
                $data['quantity'] ?? 1,
                $data['description'] ?? null,
                $cover_image,
                $donation_ref
            ]);
            
            $donation_id = $this->pdo->lastInsertId();
            
            // Log activity
            $this->logActivity($user_id, 'SUBMIT_DONATION', 'donations', $donation_id);
            
            return [
                'success' => true,
                'donation_id' => $donation_id,
                'donation_reference' => $donation_ref
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Submission failed: ' . $e->getMessage()];
        }
    }
    
    // Get user's donations

    public function getUserDonations($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT d.*, u.full_name as verifier_name 
            FROM donations d 
            LEFT JOIN users u ON d.verified_by = u.user_id 
            WHERE d.user_id = ? 
            ORDER BY d.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    // Get pending donations (for staff)

    public function getPendingDonations() {
        $stmt = $this->pdo->query("SELECT * FROM pending_donations_view");
        return $stmt->fetchAll();
    }
    
    // Get all donations with filters

    public function getAllDonations($status = null) {
        $sql = "
            SELECT d.*, u.full_name as donor_name, u.email as donor_email, 
                   v.full_name as verifier_name 
            FROM donations d 
            JOIN users u ON d.user_id = u.user_id 
            LEFT JOIN users v ON d.verified_by = v.user_id
        ";
        
        $params = [];
        
        if ($status) {
            $sql .= " WHERE d.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY d.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Get donation by ID

    public function getDonationById($donation_id) {
        $stmt = $this->pdo->prepare("
            SELECT d.*, u.full_name as donor_name, u.email as donor_email, u.phone as donor_phone,
                   v.full_name as verifier_name 
            FROM donations d 
            JOIN users u ON d.user_id = u.user_id 
            LEFT JOIN users v ON d.verified_by = v.user_id 
            WHERE d.donation_id = ?
        ");
        $stmt->execute([$donation_id]);
        return $stmt->fetch();
    }
    
    // Verify donation (approve or reject)

    public function verifyDonation($donation_id, $verifier_id, $action, $comments = '') {
        // Validate action
        if (!in_array($action, ['Approved', 'Rejected'])) {
            return ['success' => false, 'message' => 'Invalid action'];
        }
        
        // Get donation
        $donation = $this->getDonationById($donation_id);
        if (!$donation) {
            return ['success' => false, 'message' => 'Donation not found'];
        }
        
        if ($donation['status'] != 'Pending') {
            return ['success' => false, 'message' => 'Donation already verified'];
        }
        
        // Update donation
        $stmt = $this->pdo->prepare("
            UPDATE donations 
            SET status = ?, verification_comments = ?, verified_by = ?, verified_at = NOW() 
            WHERE donation_id = ?
        ");
        $stmt->execute([$action, $comments, $verifier_id, $donation_id]);
        
        // Log activity
        $this->logActivity($verifier_id, 'VERIFY_DONATION', 'donations', $donation_id);
        
        return ['success' => true, 'message' => "Donation {$action} successfully"];
    }
    
    // Get donation statistics

    public function getStatistics($user_id = null) {
        $sql = "
            SELECT 
                COUNT(*) as total_donations,
                SUM(quantity) as total_books,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
            FROM donations
        ";
        
        $params = [];
        
        if ($user_id) {
            $sql .= " WHERE user_id = ?";
            $params[] = $user_id;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    // Get top donors

    public function getTopDonors($limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT u.user_id, u.full_name, 
                   COUNT(d.donation_id) as total_donations,
                   SUM(d.quantity) as total_books 
            FROM users u 
            JOIN donations d ON u.user_id = d.user_id 
            WHERE d.status = 'Approved' 
            GROUP BY u.user_id, u.full_name 
            ORDER BY total_donations DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    // Handle file upload

    public function uploadCoverImage($file) {
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file['type'], $allowed_types)) {
            return ['success' => false, 'message' => 'Only JPEG and PNG images are allowed'];
        }
        
        // Check file size (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return ['success' => false, 'message' => 'File size must not exceed 2MB'];
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('book_', true) . '.' . $extension;
        
        // Upload directory
        $upload_dir = __DIR__ . '/../assets/uploads/book_covers/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $destination = $upload_dir . $filename;
        
        // Move file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'filename' => $filename];
        } else {
            return ['success' => false, 'message' => 'File upload failed'];
        }
    }
    
    private function logActivity($user_id, $action, $table, $record_id) {
        $stmt = $this->pdo->prepare("
            INSERT INTO audit_logs (user_id, action, table_affected, record_id, ip_address) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt->execute([$user_id, $action, $table, $record_id, $ip]);
    }
}
?>