<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="dashboard.php">
                <span class="logo-icon">
                    <img src="assets/uploads/logo.png" alt="Logo" style="width: 40px; height: 40px;">
                </span>
                <span class="logo-text">BOOK STORY</span>
            </a>
        </div>
        
        <nav class="main-nav">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="book_appointment.php">Add Appointment</a></li>
                <li><a href="my_appointments.php">My Bookings</a></li>
                <li><a href="donate_book.php">Donate</a></li>
                <li><a href="my_donations.php">My Donations</a></li>
                
                <?php if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Librarian'): ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">Staff Tools ▼</a>
                    <ul class="dropdown-menu">
                        <li><a href="all_appointments.php">All Appointments</a></li>
                        <li><a href="verify_donations.php">Verify Donations</a></li>
                        <?php if ($_SESSION['role'] == 'Admin'): ?>
                        <li><a href="manage_users.php">Manage Users</a></li>
                        <li><a href="reports.php">Reports</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <div class="user-menu">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <span class="user-role"><?php echo $_SESSION['role']; ?></span>
            </div>
            <div class="user-actions">
                <a href="profile.php" class="btn-icon" title="Profile">👼</a>
                <a href="logout.php" class="btn-icon" title="Logout">❌</a>
            </div>
        </div>
        
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">☰</button>
    </div>
</header>

<script>
function toggleMobileMenu() {
    document.querySelector('.main-nav').classList.toggle('active');
}
</script>