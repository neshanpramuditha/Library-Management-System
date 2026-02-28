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

// Get user's donations
$donations = $donationModel->getUserDonations($_SESSION['user_id']);

// Success message after submitting donation
$success = '';
if (isset($_SESSION['donation_success'])) {
    $success = $_SESSION['donation_success'];
    unset($_SESSION['donation_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Donations - Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .page-header {
            margin-bottom: 2rem;
        }
        .page-header h1 {
            color: var(--dark-color);
        }
        .donation-list {
            display: grid;
            gap: 1.5rem;
        }
        .donation-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            display: flex;
            gap: 1.2rem;
            border: 2px solid var(--gray-200);
            transition: 0.3s;
        }
        .donation-card:hover {
            border-color: var(--primary-color);
        }
        .donation-image img {
            width: 120px;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }
        .donation-details h3 {
            margin-bottom: 0.3rem;
            color: var(--dark-color);
        }
        .tag {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            margin-right: 6px;
        }
        .tag-status {
            background: #eef;
            color: #445;
        }
        .tag-category {
            background: #efe;
            color: #353;
        }
        .empty-message {
            padding: 2rem;
            text-align: center;
            background: white;
            border-radius: 12px;
            border: 2px dashed var(--gray-300);
            font-size: 1.1rem;
            color: var(--gray-700);
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">

    <div class="page-header">
        <h1>📦 My Book Donations</h1>
        <p>View all books you donated to the library</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($donations)): ?>
        <div class="empty-message">
            You have not donated any books yet.<br>
            <a href="donate_book.php" class="btn btn-primary" style="margin-top:1rem;">Donate a Book</a>
        </div>
    <?php else: ?>

    <div class="donation-list">
        <?php foreach ($donations as $d): ?>
        <div class="donation-card">

            <div class="donation-image">
                <?php if (!empty($d['cover_image'])): ?>
                    <img src="uploads/covers/<?php echo htmlspecialchars($d['cover_image']); ?>" alt="Book Cover">
                <?php else: ?>
                    <img src="assets/img/default_book.png" alt="Default Cover">
                <?php endif; ?>
            </div>

            <div class="donation-details">
                <h3><?php echo htmlspecialchars($d['book_title']); ?></h3>
                <p><strong>Author:</strong> <?php echo htmlspecialchars($d['author']); ?></p>
                <?php if (!empty($d['isbn'])): ?>
                    <p><strong>ISBN:</strong> <?php echo htmlspecialchars($d['isbn']); ?></p>
                <?php endif; ?>

                <p>
                    <span class="tag tag-category"><?php echo htmlspecialchars($d['category']); ?></span>
                    <span class="tag tag-status">Status: <?php echo htmlspecialchars($d['status']); ?></span>
                </p>

                <p><strong>Quantity:</strong> <?php echo $d['quantity']; ?></p>
                <p><strong>Reference:</strong> <?php echo htmlspecialchars($d['donation_reference']); ?></p>
                <p><small>Donated on: <?php echo date('Y-m-d', strtotime($d['created_at'])); ?></small></p>
            </div>

        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>
