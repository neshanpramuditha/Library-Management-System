<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/database.php';
require_once 'models/Donation.php';

$pdo = getDB();
$donationModel = new Donation($pdo);

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'book_title' => trim($_POST['book_title']),
        'author' => trim($_POST['author']),
        'isbn' => trim($_POST['isbn']),
        'category' => $_POST['category'],
        'book_condition' => $_POST['book_condition'],
        'quantity' => $_POST['quantity'],
        'description' => trim($_POST['description'])
    ];
    
    // Handle file upload
    $cover_image = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $upload_result = $donationModel->uploadCoverImage($_FILES['cover_image']);
        if ($upload_result['success']) {
            $cover_image = $upload_result['filename'];
        } else {
            $error = $upload_result['message'];
        }
    }
    
    if (empty($error)) {
        $result = $donationModel->submitDonation($_SESSION['user_id'], $data, $cover_image);
        
        if ($result['success']) {
            $success = "Donation submitted successfully! Reference: " . $result['donation_reference'];
            $_SESSION['donation_success'] = $success;
            header('Location: my_donations.php');
            exit;
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
    <title>Donate Book - Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
    .page-header {
        margin-bottom: 2rem;
    }
    .page-header h1 {
        color: var(--dark-color);
        margin-bottom: 0.5rem;
    }
    .page-header p {
        color: var(--gray-600);
        font-size: 1.1rem;
    }
    .donation-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }
    .guidelines-section {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .info-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 2px solid var(--gray-200);
        transition: all 0.3s;
    }
    .info-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .info-card h3 {
        color: var(--dark-color);
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }
    .info-card ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .info-card ul li {
        padding: 0.5rem 0;
        color: var(--gray-700);
        border-bottom: 1px solid var(--gray-100);
    }
    .info-card ul li:last-child {
        border-bottom: none;
    }
    .info-card-highlight {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
        border-color: var(--primary-color);
    }
    .image-preview {
        margin-top: 1rem;
        text-align: center;
    }
    .image-preview img {
        max-width: 100%;
        max-height: 300px;
        border-radius: 8px;
        border: 2px solid var(--gray-200);
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    .form-text {
        display: block;
        margin-top: 0.5rem;
        color: var(--gray-600);
        font-size: 0.875rem;
    }
    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .alert-error {
        background: #fee;
        color: #c33;
        border: 1px solid #fcc;
    }
    .alert-success {
        background: #efe;
        color: #3c3;
        border: 1px solid #cfc;
    }
    .required {
        color: var(--danger-color);
    }
    @media (max-width: 968px) {
        .donation-layout {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        .form-actions {
            flex-direction: column;
        }
        .form-actions .btn {
            width: 100%;
        }
    }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>📚 Donate a Book</h1>
            <p>Help grow our library collection by donating books</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="donation-layout">
            <!-- Form Section -->
            <div class="section">
                <h2>Book Information</h2>
                <form method="POST" enctype="multipart/form-data" id="donationForm">
                    <div class="form-group">
                        <label>Book Title <span class="required">*</span></label>
                        <input type="text" name="book_title" class="form-control" required
                               placeholder="Enter the book title">
                    </div>
                    
                    <div class="form-group">
                        <label>Author <span class="required">*</span></label>
                        <input type="text" name="author" class="form-control" required
                               placeholder="Enter author name">
                    </div>
                    
                    <div class="form-group">
                        <label>ISBN (Optional)</label>
                        <input type="text" name="isbn" class="form-control"
                               placeholder="ISBN-10 or ISBN-13">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Category <span class="required">*</span></label>
                            <select name="category" class="form-control" required>
                                <option value="">Select category</option>
                                <option value="Fiction">Fiction</option>
                                <option value="Non-Fiction">Non-Fiction</option>
                                <option value="Academic">Academic</option>
                                <option value="Children">Children's Books</option>
                                <option value="Reference">Reference</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Book Condition <span class="required">*</span></label>
                            <select name="book_condition" class="form-control" required>
                                <option value="">Select condition</option>
                                <option value="New">New</option>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Poor">Poor</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Quantity <span class="required">*</span></label>
                        <input type="number" name="quantity" class="form-control" 
                               min="1" max="100" value="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Book Cover Image (Optional)</label>
                        <input type="file" name="cover_image" class="form-control" 
                               accept="image/jpeg,image/png,image/jpg" id="coverImage" 
                               onchange="previewImage(this)">
                        <small class="form-text">Max size: 2MB | Formats: JPEG, PNG</small>
                        <div id="imagePreview" class="image-preview"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Additional Description</label>
                        <textarea name="description" class="form-control" rows="4"
                                  placeholder="Any additional information about the book (condition notes, edition, etc.)"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">
                            📚 Submit Donation
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            
            <!-- Guidelines Section -->
            <div class="guidelines-section">
                <div class="info-card">
                    <h3>📋 Donation Guidelines</h3>
                    <ul>
                        <li>Books should be in readable condition</li>
                        <li>Please ensure pages are intact</li>
                        <li>No water damage or mold</li>
                        <li>Fiction, non-fiction, and textbooks are welcome</li>
                        <li>Multiple copies can be donated</li>
                    </ul>
                </div>
                
                <div class="info-card">
                    <h3>✅ What We Accept</h3>
                    <ul>
                        <li>✓ Fiction and Non-Fiction</li>
                        <li>✓ Academic Textbooks</li>
                        <li>✓ Children's Books</li>
                        <li>✓ Reference Materials</li>
                        <li>✓ Magazines and Journals</li>
                    </ul>
                </div>
                
                <div class="info-card">
                    <h3>❌ What We Don't Accept</h3>
                    <ul>
                        <li>✗ Books with missing pages</li>
                        <li>✗ Water-damaged books</li>
                        <li>✗ Outdated editions (>10 years for textbooks)</li>
                        <li>✗ Books with heavy markings</li>
                    </ul>
                </div>
                
                <div class="info-card info-card-highlight">
                    <h3>🎁 Benefits of Donating</h3>
                    <ul>
                        <li>📖 Help fellow students and readers</li>
                        <li>🌱 Promote sustainability</li>
                        <li>🏆 Get recognized as a top donor</li>
                        <li>📜 Receive donation acknowledgment</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file size
            const maxSize = 2 * 1024 * 1024; // 2MB
            if (file.size > maxSize) {
                alert('File size exceeds 2MB. Please choose a smaller image.');
                input.value = '';
                return;
            }
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                alert('Only JPEG and PNG images are allowed.');
                input.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                preview.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    }
    
    // Form validation on submit
    document.getElementById('donationForm').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('coverImage');
        
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const maxSize = 2 * 1024 * 1024; // 2MB
            
            if (file.size > maxSize) {
                e.preventDefault();
                alert('File size exceeds 2MB. Please choose a smaller image.');
                return false;
            }
            
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                e.preventDefault();
                alert('Only JPEG and PNG images are allowed.');
                return false;
            }
        }
        
        // Validate required fields
        const title = document.querySelector('input[name="book_title"]').value.trim();
        const author = document.querySelector('input[name="author"]').value.trim();
        const category = document.querySelector('select[name="category"]').value;
        const condition = document.querySelector('select[name="book_condition"]').value;
        
        if (!title || !author || !category || !condition) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
    });
    </script>
</body>
</html>