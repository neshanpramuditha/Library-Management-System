CREATE DATABASE IF NOT EXISTS library_system;
USE library_system;


-- Table 1: users

CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('Admin', 'Librarian', 'Patron') DEFAULT 'Patron',
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    failed_login_attempts INT DEFAULT 0,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role)
);


-- Table 2: appointment_types

CREATE TABLE appointment_types (
    type_id INT PRIMARY KEY AUTO_INCREMENT,
    type_name VARCHAR(100) NOT NULL,
    description TEXT,
    duration_minutes INT NOT NULL DEFAULT 60,
    max_bookings_per_user INT DEFAULT 2,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
);


-- Table 3: appointments

CREATE TABLE appointments (
    appointment_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    purpose TEXT,
    status ENUM('Confirmed', 'Cancelled', 'Completed') DEFAULT 'Confirmed',
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    cancellation_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (type_id) REFERENCES appointment_types(type_id),
    INDEX idx_user_date (user_id, appointment_date),
    INDEX idx_date_time (appointment_date, start_time),
    INDEX idx_status (status),
    UNIQUE KEY unique_slot (appointment_date, start_time, type_id)
);


-- Table 4: donations

CREATE TABLE donations (
    donation_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20),
    category ENUM('Fiction', 'Non-Fiction', 'Academic', 'Children', 'Reference') NOT NULL,
    book_condition ENUM('New', 'Good', 'Fair', 'Poor') NOT NULL,
    quantity INT DEFAULT 1,
    description TEXT,
    cover_image VARCHAR(255),
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    verification_comments TEXT,
    verified_by INT,
    verified_at DATETIME,
    donation_reference VARCHAR(20) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_status (user_id, status),
    INDEX idx_status (status)
);


-- Table 5: audit_logs

CREATE TABLE audit_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_affected VARCHAR(50),
    record_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_action (user_id, action),
    INDEX idx_created_at (created_at)
);


-- Insert Sample Data
-- Sample Users (password for all: Password123)
INSERT INTO users (username, email, password_hash, full_name, phone, role) VALUES
('admin', 'admin@library.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lW3j5n0RqZ5S', 'System Administrator', '0771234567', 'Admin'),
('librarian1', 'librarian@library.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lW3j5n0RqZ5S', 'John Librarian', '0779876543', 'Librarian'),
('patron1', 'patron@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lW3j5n0RqZ5S', 'Jane Patron', '0765432109', 'Patron');

-- Sample Appointment Types
INSERT INTO appointment_types (type_name, description, duration_minutes, max_bookings_per_user) VALUES
('Study Room', 'Private study room for individual or group study', 120, 3),
('Consultation', 'One-on-one consultation with librarian for research assistance', 30, 2),
('Workshop', 'Library workshops and training sessions', 90, 1),
('Event', 'Library events and special programs', 120, 1);

-- Sample Appointments
INSERT INTO appointments (user_id, type_id, appointment_date, start_time, end_time, purpose, booking_reference) VALUES
(3, 1, '2025-11-20', '10:00:00', '12:00:00', 'Group study for final exams', 'APT-2025-00001'),
(3, 2, '2025-11-21', '14:00:00', '14:30:00', 'Research consultation for thesis', 'APT-2025-00002');

-- Sample Donations
INSERT INTO donations (user_id, book_title, author, isbn, category, book_condition, quantity, donation_reference, status) VALUES
(3, 'Introduction to Algorithms', 'Thomas H. Cormen', '9780262033848', 'Academic', 'Good', 1, 'DON-2025-00001', 'Approved'),
(3, 'Harry Potter and the Philosopher Stone', 'J.K. Rowling', '9780747532699', 'Fiction', 'Fair', 1, 'DON-2025-00002', 'Pending');

-- Sample Audit Logs
INSERT INTO audit_logs (user_id, action, table_affected, record_id, ip_address) VALUES
(1, 'LOGIN', 'users', 1, '127.0.0.1'),
(3, 'BOOK_APPOINTMENT', 'appointments', 1, '127.0.0.1'),
(3, 'SUBMIT_DONATION', 'donations', 1, '127.0.0.1');


DELIMITER //
CREATE PROCEDURE GetUserStats(IN p_user_id INT)
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM appointments WHERE user_id = p_user_id AND status = 'Confirmed' AND appointment_date >= CURDATE()) AS upcoming_appointments,
        (SELECT COUNT(*) FROM appointments WHERE user_id = p_user_id) AS total_appointments,
        (SELECT COUNT(*) FROM donations WHERE user_id = p_user_id AND status = 'Approved') AS approved_donations,
        (SELECT COUNT(*) FROM donations WHERE user_id = p_user_id) AS total_donations;
END //
DELIMITER ;

CREATE VIEW pending_donations_view AS
SELECT 
    d.donation_id,
    d.book_title,
    d.author,
    d.category,
    d.book_condition,
    d.donation_reference,
    d.created_at,
    u.full_name AS donor_name,
    u.email AS donor_email
FROM donations d
JOIN users u ON d.user_id = u.user_id
WHERE d.status = 'Pending'
ORDER BY d.created_at ASC;