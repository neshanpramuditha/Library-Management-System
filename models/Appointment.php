<?php
class Appointment {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Get all appointment types

    public function getAppointmentTypes() {
        $stmt = $this->pdo->query("
            SELECT * FROM appointment_types 
            WHERE is_active = TRUE 
            ORDER BY type_name
        ");
        return $stmt->fetchAll();
    }
    
    // Get appointment type by ID

    public function getTypeById($type_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM appointment_types WHERE type_id = ?");
        $stmt->execute([$type_id]);
        return $stmt->fetch();
    }
    
    // Check if slot is available

    public function isSlotAvailable($date, $start_time, $type_id) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM appointments 
            WHERE appointment_date = ? 
            AND start_time = ? 
            AND type_id = ? 
            AND status = 'Confirmed'
        ");
        $stmt->execute([$date, $start_time, $type_id]);
        $result = $stmt->fetch();
        return $result['count'] == 0;
    }
    
    // Get available time slots for a date

    public function getAvailableSlots($date, $type_id) {
        // Get appointment type details
        $type = $this->getTypeById($type_id);
        if (!$type) {
            return [];
        }
        
        $duration = $type['duration_minutes'];
        
        // Generate time slots (9 AM to 5 PM)
        $slots = [];
        $start_hour = 9;
        $end_hour = 17;
        
        for ($hour = $start_hour; $hour < $end_hour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 30) {
                $time = sprintf('%02d:%02d:00', $hour, $minute);
                
                // Calculate end time
                $end_timestamp = strtotime($time) + ($duration * 60);
                if (date('H', $end_timestamp) >= $end_hour) {
                    break; // Don't add slots that go past closing time
                }
                
                // Check if slot is available
                if ($this->isSlotAvailable($date, $time, $type_id)) {
                    $slots[] = [
                        'time' => $time,
                        'display' => date('h:i A', strtotime($time)),
                        'available' => true
                    ];
                } else {
                    $slots[] = [
                        'time' => $time,
                        'display' => date('h:i A', strtotime($time)),
                        'available' => false
                    ];
                }
            }
        }
        
        return $slots;
    }
    
    // Book new appointment

    public function bookAppointment($user_id, $type_id, $date, $start_time, $purpose = '') {
        // Validate future date
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            return ['success' => false, 'message' => 'Cannot book appointments in the past'];
        }
        
        // Get appointment type
        $type = $this->getTypeById($type_id);
        if (!$type) {
            return ['success' => false, 'message' => 'Invalid appointment type'];
        }
        
        // Calculate end time
        $end_timestamp = strtotime($start_time) + ($type['duration_minutes'] * 60);
        $end_time = date('H:i:s', $end_timestamp);
        
        // Generate booking reference
        $booking_ref = 'APT-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        
        try {
            // Start transaction
            $this->pdo->beginTransaction();
            
            // Check availability again (with lock)
            if (!$this->isSlotAvailable($date, $start_time, $type_id)) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'This time slot is no longer available'];
            }
            
            // Insert appointment
            $stmt = $this->pdo->prepare("
                INSERT INTO appointments (user_id, type_id, appointment_date, start_time, end_time, purpose, booking_reference) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $type_id, $date, $start_time, $end_time, $purpose, $booking_ref]);
            
            $appointment_id = $this->pdo->lastInsertId();
            
            // Log activity
            $this->logActivity($user_id, 'BOOK_APPOINTMENT', 'appointments', $appointment_id);
            
            // Commit transaction
            $this->pdo->commit();
            
            return [
                'success' => true, 
                'appointment_id' => $appointment_id,
                'booking_reference' => $booking_ref
            ];
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Booking failed: ' . $e->getMessage()];
        }
    }
    
    // Get user's appointments

    public function getUserAppointments($user_id, $status = null) {
        $sql = "
            SELECT a.*, at.type_name, at.description 
            FROM appointments a 
            JOIN appointment_types at ON a.type_id = at.type_id 
            WHERE a.user_id = ?
        ";
        
        $params = [$user_id];
        
        if ($status) {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY a.appointment_date DESC, a.start_time DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Get upcoming appointments for user

    public function getUpcomingAppointments($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT a.*, at.type_name 
            FROM appointments a 
            JOIN appointment_types at ON a.type_id = at.type_id 
            WHERE a.user_id = ? 
            AND a.status = 'Confirmed' 
            AND a.appointment_date >= CURDATE() 
            ORDER BY a.appointment_date, a.start_time 
            LIMIT 10
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    //Get all appointments (admin/librarian)

    public function getAllAppointments($date = null) {
        $sql = "
            SELECT a.*, at.type_name, u.full_name, u.email 
            FROM appointments a 
            JOIN appointment_types at ON a.type_id = at.type_id 
            JOIN users u ON a.user_id = u.user_id
        ";
        
        $params = [];
        
        if ($date) {
            $sql .= " WHERE a.appointment_date = ?";
            $params[] = $date;
        }
        
        $sql .= " ORDER BY a.appointment_date DESC, a.start_time DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Get appointment by ID
    public function getAppointmentById($appointment_id) {
        $stmt = $this->pdo->prepare("
            SELECT a.*, at.type_name, at.description, u.full_name, u.email 
            FROM appointments a 
            JOIN appointment_types at ON a.type_id = at.type_id 
            JOIN users u ON a.user_id = u.user_id 
            WHERE a.appointment_id = ?
        ");
        $stmt->execute([$appointment_id]);
        return $stmt->fetch();
    }
    
    // Cancel appointment

    public function cancelAppointment($appointment_id, $user_id, $reason = '') {
        // Get appointment
        $appointment = $this->getAppointmentById($appointment_id);
        
        if (!$appointment) {
            return ['success' => false, 'message' => 'Appointment not found'];
        }
        
        // Check ownership
        if ($appointment['user_id'] != $user_id) {
            return ['success' => false, 'message' => 'You can only cancel your own appointments'];
        }
        
        // Check if already cancelled
        if ($appointment['status'] == 'Cancelled') {
            return ['success' => false, 'message' => 'Appointment already cancelled'];
        }
        
        // Check if appointment is in the past
        $appointment_datetime = $appointment['appointment_date'] . ' ' . $appointment['start_time'];
        if (strtotime($appointment_datetime) < time()) {
            return ['success' => false, 'message' => 'Cannot cancel past appointments'];
        }
        
        // Update status
        $stmt = $this->pdo->prepare("
            UPDATE appointments 
            SET status = 'Cancelled', cancellation_reason = ? 
            WHERE appointment_id = ?
        ");
        $stmt->execute([$reason, $appointment_id]);
        
        // Log activity
        $this->logActivity($user_id, 'CANCEL_APPOINTMENT', 'appointments', $appointment_id);
        
        return ['success' => true, 'message' => 'Appointment cancelled successfully'];
    }
    
    /**
     * Get appointment statistics
     */
    public function getStatistics($from_date = null, $to_date = null) {
        if (!$from_date) $from_date = date('Y-m-01'); // First day of current month
        if (!$to_date) $to_date = date('Y-m-d'); // Today
        
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_appointments,
                SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed
            FROM appointments 
            WHERE appointment_date BETWEEN ? AND ?
        ");
        $stmt->execute([$from_date, $to_date]);
        return $stmt->fetch();
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