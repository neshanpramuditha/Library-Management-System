<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>
                    <img src="assets/uploads/logo2.png" alt="Logo" style="width:30px; height:30px; vertical-align:middle;">
                    BookStory
                </h3>
                <p class="footer-description">
                    Appointment and <br>
                    Book Donation System....<br><br>
                    A comprehensive web based information system for efficient library operations.
                </p>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="book_appointment.php">Book Appointment</a></li>
                    <li><a href="my_appointments.php">My Appointments</a></li>
                    <li><a href="donate_book.php">Donate Book</a></li>
                    <li><a href="my_donations.php">My Donations</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Support</h4>
                <ul>
                    <li><a href="about.php">About System</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Contact Information</h4>
                <ul class="contact-info">
                    <li>📧 Email: fctlibrary@UOK.edu</li>
                    <li>📞 Phone: +94 76 718 0351</li>
                    <li>📍 Location: FCT Library, UOK</li>
                    <li>⏰ Hours: Mon-Fri, 8:00 AM to 10:00 PM</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> Library Appointment System. All rights reserved.</p>
                <p class="project-info">
                    Developed for SWST 31032 - Applied Information Systems | 
                    Mini Project | 
                    <a href="https://github.com/neshanpramuditha" target="_blank">Connect With Us</a>
                </p>
            </div>
            <div class="footer-tech">
                <p>
                    <span class="tech-badge">PHP</span>
                    <span class="tech-badge">MySQL</span>
                    <span class="tech-badge">HTML</span>
                    <span class="tech-badge">CSS</span>
                    <span class="tech-badge">JavaScript</span>
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
/* Footer Styles */
.main-footer {
    background: var(--dark-color, #1f2937);
    color: white;
    margin-top: 4rem;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 3rem 2rem 1rem;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.footer-section h3 {
    margin-bottom: 1rem;
    font-size: 1.5rem;
    color: white;
}

.footer-section h4 {
    margin-bottom: 1rem;
    font-size: 1.1rem;
    color: #e5e7eb;
    font-weight: 600;
}

.footer-section p {
    color: #d1d5db;
    line-height: 1.6;
    margin-bottom: 0.5rem;
}

.footer-description {
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li {
    margin-bottom: 0.75rem;
}

.footer-section a {
    color: #9ca3af;
    transition: color 0.3s;
    text-decoration: none;
}

.footer-section a:hover {
    color: white;
    text-decoration: underline;
}

.contact-info li {
    color: #d1d5db;
    font-size: 0.9rem;
}

.footer-bottom {
    padding-top: 2rem;
    border-top: 1px solid #4b5563;
}

.footer-bottom-content {
    text-align: center;
    color: #9ca3af;
    font-size: 0.9rem;
}

.footer-bottom-content p {
    margin: 0.5rem 0;
}

.project-info a {
    color: #667eea;
    text-decoration: none;
}

.project-info a:hover {
    text-decoration: underline;
}

.footer-tech {
    text-align: center;
    margin-top: 1rem;
}

.tech-badge {
    display: inline-block;
    background: rgba(102, 126, 234, 0.2);
    color: #a5b4fc;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    margin: 0.25rem;
    border: 1px solid rgba(102, 126, 234, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .footer-section {
        text-align: center;
    }
    
    .footer-bottom-content {
        font-size: 0.85rem;
    }
}
</style>