<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Librarian'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'config/database.php';
require_once 'models/Donation.php';

$pdo = getDB();
$donationModel = new Donation($pdo);

$success = '';
$error = '';

// Handle verification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_donation'])) {
    $donation_id = $_POST['donation_id'];
    $action = $_POST['action'];
    $comments = trim($_POST['verification_comments']);
    
    $result = $donationModel->verifyDonation($donation_id, $_SESSION['user_id'], $action, $comments);
    
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}

$pending_donations = $donationModel->getPendingDonations();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Donations - Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>✅ Verify Book Donations</h1>
            <p>Review and approve/reject pending donations</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="section">
            <h2>Pending Donations (<?php echo count($pending_donations); ?>)</h2>
            
            <?php if (empty($pending_donations)): ?>
                <div class="empty-state">
                    <p>✅ No pending donations to review</p>
                </div>
            <?php else: ?>
                <div class="donations-verify-grid">
                    <?php foreach ($pending_donations as $donation): ?>
                    <div class="verify-card">
                        <div class="verify-header">
                            <h3><?php echo htmlspecialchars($donation['book_title']); ?></h3>
                            <span class="reference"><?php echo htmlspecialchars($donation['donation_reference']); ?></span>
                        </div>
                        
                        <div class="verify-body">
                            <div class="book-info">
                                <p><strong>Author:</strong> <?php echo htmlspecialchars($donation['author']); ?></p>
                                <p><strong>Category:</strong> <?php echo htmlspecialchars($donation['category']); ?></p>
                                <p><strong>Condition:</strong> <?php echo htmlspecialchars($donation['book_condition']); ?></p>
                                <p><strong>Quantity:</strong> <?php echo $donation['quantity']; ?></p>
                                <p><strong>Submitted:</strong> <?php echo date('M j, Y H:i', strtotime($donation['created_at'])); ?></p>
                            </div>
                            
                            <div class="donor-info">
                                <h4>Donor Information</h4>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($donation['donor_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($donation['donor_email']); ?></p>
                            </div>
                            
                            <button onclick="openVerifyModal(<?php echo $donation['donation_id']; ?>, '<?php echo htmlspecialchars($donation['book_title']); ?>', '<?php echo htmlspecialchars($donation['donation_reference']); ?>')" 
                                    class="btn btn-primary btn-block">
                                Review & Verify
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Verify Modal -->
    <div id="verifyModal" class="modal">
        <div class="modal-content modal-large">
            <h2>Verify Donation</h2>
            <p><strong>Book:</strong> <span id="modalBookTitle"></span></p>
            <p><strong>Reference:</strong> <span id="modalReference"></span></p>
            
            <form method="POST" id="verifyForm">
                <input type="hidden" name="donation_id" id="modalDonationId">
                <input type="hidden" name="action" id="modalAction">
                
                <div class="form-group">
                    <label>Verification Comments <span class="required">*</span></label>
                    <textarea name="verification_comments" id="verificationComments" 
                              class="form-control" rows="4" required
                              placeholder="Provide feedback for the donor..."></textarea>
                    <small class="form-text">This will be visible to the donor</small>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="verifyDonation('Approved')" 
                            class="btn btn-success">
                        ✅ Approve Donation
                    </button>
                    <button type="button" onclick="verifyDonation('Rejected')" 
                            class="btn btn-danger">
                        ❌ Reject Donation
                    </button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <style>
    .donations-verify-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
    }
    .verify-card {
        background: white;
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s;
    }
    .verify-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-color: var(--primary-color);
    }
    .verify-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 1.5rem;
    }
    .verify-header h3 {
        margin: 0 0 0.5rem 0;
        font-size: 1.2rem;
    }
    .verify-header .reference {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .verify-body {
        padding: 1.5rem;
    }
    .book-info,
    .donor-info {
        margin-bottom: 1.5rem;
    }
    .book-info p,
    .donor-info p {
        margin: 0.5rem 0;
        color: var(--gray-700);
    }
    .donor-info {
        background: var(--gray-50);
        padding: 1rem;
        border-radius: 8px;
    }
    .donor-info h4 {
        margin: 0 0 0.75rem 0;
        color: var(--dark-color);
        font-size: 1rem;
    }
    .btn-block {
        width: 100%;
    }
    .modal-large {
        max-width: 600px;
    }
    .modal-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }
    @media (max-width: 768px) {
        .donations-verify-grid {
            grid-template-columns: 1fr;
        }
        .modal-actions {
            flex-direction: column;
        }
        .modal-actions .btn {
            width: 100%;
        }
    }
    </style>
    
    <script>
    function openVerifyModal(donationId, bookTitle, reference) {
        document.getElementById('modalDonationId').value = donationId;
        document.getElementById('modalBookTitle').textContent = bookTitle;
        document.getElementById('modalReference').textContent = reference;
        document.getElementById('verificationComments').value = '';
        document.getElementById('verifyModal').classList.add('show');
    }
    
    function closeModal() {
        document.getElementById('verifyModal').classList.remove('show');
    }
    
    function verifyDonation(action) {
        const comments = document.getElementById('verificationComments').value.trim();
        
        if (!comments) {
            alert('Please provide verification comments');
            return;
        }
        
        if (action === 'Rejected' && comments.length < 10) {
            alert('Please provide a detailed reason for rejection (minimum 10 characters)');
            return;
        }
        
        if (confirm(`Are you sure you want to ${action.toLowerCase()} this donation?`)) {
            document.getElementById('modalAction').value = action;
            document.getElementById('verifyForm').submit();
        }
    }
    
    // Close modal on outside click
    document.getElementById('verifyModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    </script>
</body>
</html>