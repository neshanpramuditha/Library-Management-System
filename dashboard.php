<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/Appointment.php';
require_once 'models/Donation.php';

$pdo = getDB();
$userModel = new User($pdo);
$appointmentModel = new Appointment($pdo);
$donationModel = new Donation($pdo);

// Get user data
$user = $userModel->getUserById($_SESSION['user_id']);

// Get statistics
$user_stats = $userModel->getUserStats($_SESSION['user_id']);
$upcoming_appointments = $appointmentModel->getUpcomingAppointments($_SESSION['user_id']);
$recent_donations = $donationModel->getUserDonations($_SESSION['user_id']);
$recent_donations = array_slice($recent_donations, 0, 5); // Last 5

// Admin/Librarian specific data
$all_appointments = [];
$pending_donations = [];
if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Librarian') {
    $all_appointments = $appointmentModel->getAllAppointments(date('Y-m-d')); // Today's appointments
    $pending_donations = $donationModel->getPendingDonations();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>! 👋</h1>
            <p class="role-badge role-<?php echo strtolower($user['role']); ?>">
                <?php echo $user['role']; ?>
            </p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-info">
                    <h3><?php echo $user_stats['upcoming_appointments'] ?? 0; ?></h3>
                    <p>Upcoming Appointments</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <h3><?php echo $user_stats['total_appointments'] ?? 0; ?></h3>
                    <p>Total Appointments</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📖</div>
                <div class="stat-info">
                    <h3><?php echo $user_stats['approved_donations'] ?? 0; ?></h3>
                    <p>Approved Donations</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🎁</div>
                <div class="stat-info">
                    <h3><?php echo $user_stats['total_donations'] ?? 0; ?></h3>
                    <p>Total Donations</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="section">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="book_appointment.php" class="btn btn-primary">
                    📅 Book Appointment
                </a>
                <a href="donate_book.php" class="btn btn-success">
                    📚 Donate Book
                </a>
                <a href="my_appointments.php" class="btn btn-secondary">
                    📋 My Appointments
                </a>
                <a href="my_donations.php" class="btn btn-info">
                    📖 My Donations
                </a>
            </div>
        </div>
        
        <!-- Upcoming Appointments -->
        <div class="section">
            <h2>Upcoming Appointments</h2>
            <?php if (empty($upcoming_appointments)): ?>
                <div class="empty-state">
                    <p>📭 No upcoming appointments</p>
                    <a href="book_appointment.php" class="btn btn-primary">Book Now</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcoming_appointments as $apt): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($apt['booking_reference']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($apt['start_time'])); ?></td>
                                <td><?php echo htmlspecialchars($apt['type_name']); ?></td>
                                <td><span class="badge badge-<?php echo strtolower($apt['status']); ?>">
                                    <?php echo $apt['status']; ?>
                                </span></td>
                                <td>
                                    <a href="appointment_details.php?id=<?php echo $apt['appointment_id']; ?>" 
                                       class="btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Donations -->
        <div class="section">
            <h2>Recent Donations</h2>
            <?php if (empty($recent_donations)): ?>
                <div class="empty-state">
                    <p>📭 No donations yet</p>
                    <a href="donate_book.php" class="btn btn-success">Donate Now</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_donations as $donation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($donation['donation_reference']); ?></td>
                                <td><?php echo htmlspecialchars($donation['book_title']); ?></td>
                                <td><?php echo htmlspecialchars($donation['author']); ?></td>
                                <td><?php echo htmlspecialchars($donation['category']); ?></td>
                                <td><span class="badge badge-<?php echo strtolower($donation['status']); ?>">
                                    <?php echo $donation['status']; ?>
                                </span></td>
                                <td><?php echo date('M d, Y', strtotime($donation['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Admin/Librarian Section -->
        <?php if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Librarian'): ?>
        <div class="section">
            <h2>Staff Dashboard</h2>
            
            <div class="stats-grid">
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-info">
                        <h3><?php echo count($pending_donations); ?></h3>
                        <p>Pending Donations</p>
                        <a href="verify_donations.php" class="btn-sm btn-warning">Review</a>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">📋</div>
                    <div class="stat-info">
                        <h3><?php echo count($all_appointments); ?></h3>
                        <p>Today's Appointments</p>
                        <a href="all_appointments.php" class="btn-sm btn-info">View All</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($_SESSION['role'] == 'Admin'): ?>
        <div class="section">
            <h2>Admin Tools</h2>
            <div class="action-buttons">
                <a href="manage_users.php" class="btn btn-primary">👥 Manage Users</a>
                <a href="reports.php" class="btn btn-secondary">📊 Reports</a>
                <a href="settings.php" class="btn btn-info">⚙️ Settings</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>