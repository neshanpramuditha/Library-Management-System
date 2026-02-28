<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php if ($isLoggedIn): ?>
        <?php include 'includes/header.php'; ?>
    <?php endif; ?>
    
    <div class="container">
        <?php if (!$isLoggedIn): ?>
        <div class="public-header">
            <a href="index.php" class="btn btn-primary">← Back to Login</a>
        </div>
        <?php endif; ?>
        
        <div class="about-hero">
            <h1>About Library Appointment & Book Donation System</h1>
            <p class="lead">A modern solution for efficient library management</p>
        </div>
        
        <div class="section">
            <h2>What is This System?</h2>
            <p>
The Library Appointment & Book Donation System is a web
based application designed to modernize library operations by 
automating appointment scheduling and book donation processes. 
This system addresses the inefficiencies of manual booking systems and paper based donation tracking that many libraries currently face.
<br><br>
This project aims to:<br>
•	Automate routine library operations to reduce staff workload<br>
•	Provide 24/7 online access for library users<br>
•	Implement secure access control with role based permissions<br>
•	Enable data driven decision making through comprehensive reports<br>
•	Apply theoretical concepts from Applied Information Systems course in a practical scenario

            </p>
        </div>
        
        <div class="section">
            <h2>Key Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📅</div>
                    <h3>Smart Appointment Booking</h3>
                    <p>Real-time availability checking, multiple appointment types, instant confirmations, and automated reminders.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📚</div>
                    <h3>Book Donation Management</h3>
                    <p>Online submission, image upload support, staff verification workflow, and complete donation tracking.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Secure Access Control</h3>
                    <p>Role-based permissions, password hashing, session security, and comprehensive audit logging.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Responsive Design</h3>
                    <p>Works seamlessly on desktop, tablet, and mobile devices with intuitive navigation.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Multi-Role Support</h3>
                    <p>Admin, Librarian, and Patron roles with appropriate permissions and workflows.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Reports & Analytics</h3>
                    <p>Comprehensive statistics, donation tracking, usage reports, and performance metrics.</p>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>Technology Stack</h2>
            <div class="tech-stack">
                <div class="tech-category">
                    <h3>Frontend</h3>
                    <ul>
                        <li><strong>HTML5</strong> - Semantic structure</li>
                        <li><strong>CSS3</strong> - Responsive styling</li>
                        <li><strong>JavaScript</strong> - Interactive features</li>
                    </ul>
                </div>
                
                <div class="tech-category">
                    <h3>Backend</h3>
                    <ul>
                        <li><strong>PHP</strong> - Server side logic</li>
                        <li><strong>PDO</strong> - Database abstraction</li>
                    </ul>
                </div>
                
                <div class="tech-category">
                    <h3>Database</h3>
                    <ul>
                        <li><strong>MySQL</strong> - Data storage</li>
                        <li><strong>3NF</strong> - Normalization</li>
                </div>
                
                <div class="tech-category">
                    <h3>Security</h3>
                    <ul>
                        <li><strong>Bcrypt</strong> - Password hashing</li>
                        <li><strong>PDO</strong> - SQL injection prevention</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>Project Information</h2>
            <div class="project-info-grid">
                <div class="info-item">
                    <strong>Course:</strong>
                    <span>SWST 31032 - Applied Information Systems</span>
                </div>
                <div class="info-item">
                    <strong>Project Type:</strong>
                    <span>Mini Project</span>
                </div>
                <div class="info-item">
                    <strong>Development Time:</strong>
                    <span>3 weeks</span>
                </div>
                <div class="info-item">
                    <strong>Technologies Used:</strong>
                    <span>PHP, MySQL, HTML, CSS, JavaScript</span>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>Benefits</h2>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <h3>🚀 For Library Operations</h3>
                    <ul>
                        <li>70% reduction in scheduling time</li>
                        <li>Improved resource utilization</li>
                        <li>Reduced administrative workload</li>
                        <li>Paperless operations</li>
                    </ul>
                </div>
                
                <div class="benefit-card">
                    <h3>👤 For Library Users</h3>
                    <ul>
                        <li>24/7 online access</li>
                        <li>Instant confirmations</li>
                        <li>Mobile-friendly interface</li>
                        <li>Transparent tracking</li>
                    </ul>
                </div>
                
                <div class="benefit-card">
                    <h3>📊 For Management</h3>
                    <ul>
                        <li>Data-driven decisions</li>
                        <li>Comprehensive reports</li>
                        <li>Trend identification</li>
                        <li>Audit trails</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="section cta-section">
            <h2>Ready to Get Started?</h2>
            <p>Experience modern library management today!</p>
            <div class="cta-buttons">
                <?php if (!$isLoggedIn): ?>
                <a href="register.php" class="btn btn-primary">Register Now</a>
                <a href="index.php" class="btn btn-secondary">Login</a>
                <?php else: ?>
                <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                <a href="book_appointment.php" class="btn btn-success">Book Appointment</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if ($isLoggedIn): ?>
        <?php include 'includes/footer.php'; ?>
    <?php else: ?>
        <footer class="simple-footer">
            <p>&copy; <?php echo date('Y'); ?> Library System. All rights reserved.</p>
        </footer>
    <?php endif; ?>
    
    <style>
    .public-header {
        margin-bottom: 2rem;
    }
    .about-hero {
        text-align: center;
        padding: 3rem 1rem;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        border-radius: 12px;
        margin-bottom: 2rem;
    }
    .about-hero h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }
    .lead {
        font-size: 1.3rem;
        opacity: 0.95;
    }
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    .feature-card {
        background: var(--gray-50);
        padding: 2rem;
        border-radius: 12px;
        text-align: center;
        border: 2px solid var(--gray-200);
        transition: all 0.3s;
    }
    .feature-card:hover {
        border-color: var(--primary-color);
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    .feature-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    .feature-card h3 {
        color: var(--dark-color);
        margin-bottom: 1rem;
    }
    .tech-stack {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    .tech-category {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        border-left: 4px solid var(--primary-color);
    }
    .tech-category h3 {
        color: var(--primary-color);
        margin-bottom: 1rem;
    }
    .tech-category ul {
        list-style: none;
        padding: 0;
    }
    .tech-category li {
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--gray-100);
    }
    .tech-category li:last-child {
        border-bottom: none;
    }
    .project-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 2rem;
    }
    .info-item {
        background: var(--gray-50);
        padding: 1rem;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .info-item strong {
        color: var(--primary-color);
        font-size: 0.9rem;
    }
    .info-item span {
        color: var(--dark-color);
        font-size: 1.1rem;
        font-weight: 600;
    }
    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    .benefit-card {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        border: 2px solid var(--gray-200);
    }
    .benefit-card h3 {
        color: var(--dark-color);
        margin-bottom: 1rem;
    }
    .benefit-card ul {
        list-style: none;
        padding: 0;
    }
    .benefit-card li {
        padding: 0.5rem 0;
        padding-left: 1.5rem;
        position: relative;
    }
    .benefit-card li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: var(--success-color);
        font-weight: bold;
    }
    .cta-section {
        text-align: center;
        background: var(--gray-50);
        padding: 3rem 2rem;
        border-radius: 12px;
    }
    .cta-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
        flex-wrap: wrap;
    }
    .simple-footer {
        background: var(--dark-color);
        color: white;
        text-align: center;
        padding: 2rem;
        margin-top: 4rem;
    }
    @media (max-width: 768px) {
        .about-hero h1 {
            font-size: 1.8rem;
        }
        .features-grid,
        .benefits-grid {
            grid-template-columns: 1fr;
        }
        .cta-buttons {
            flex-direction: column;
        }
        .cta-buttons .btn {
            width: 100%;
        }
    }
    </style>
</body>
</html>