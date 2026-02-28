<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/database.php';
require_once 'models/Appointment.php';

$pdo = getDB();
$appointmentModel = new Appointment($pdo);

$success = $_SESSION['booking_success'] ?? '';
unset($_SESSION['booking_success']);

$error = '';

// Handle cancellation
if (isset($_POST['cancel_appointment'])) {
    $appointment_id = $_POST['appointment_id'];
    $reason = $_POST['cancellation_reason'] ?? '';
    
    $result = $appointmentModel->cancelAppointment($appointment_id, $_SESSION['user_id'], $reason);
    
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$status = null;
if ($filter == 'confirmed') $status = 'Confirmed';
elseif ($filter == 'cancelled') $status = 'Cancelled';
elseif ($filter == 'completed') $status = 'Completed';

$appointments = $appointmentModel->getUserAppointments($_SESSION['user_id'], $status);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>📋 My Appointments</h1>
            <a href="book_appointment.php" class="btn btn-primary">+ Book New Appointment</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="filters">
            <a href="?filter=all" class="filter-btn <?php echo $filter == 'all' ? 'active' : ''; ?>">
                All Appointments
            </a>
            <a href="?filter=confirmed" class="filter-btn <?php echo $filter == 'confirmed' ? 'active' : ''; ?>">
                Confirmed
            </a>
            <a href="?filter=completed" class="filter-btn <?php echo $filter == 'completed' ? 'active' : ''; ?>">
                Completed
            </a>
            <a href="?filter=cancelled" class="filter-btn <?php echo $filter == 'cancelled' ? 'active' : ''; ?>">
                Cancelled
            </a>
        </div>
        
        <!-- Appointments List -->
        <div class="section">
            <?php if (empty($appointments)): ?>
                <div class="empty-state">
                    <p>📭 No appointments found</p>
                    <a href="book_appointment.php" class="btn btn-primary">Book Your First Appointment</a>
                </div>
            <?php else: ?>
                <div class="appointments-grid">
                    <?php foreach ($appointments as $apt): ?>
                    <div class="appointment-card">
                        <div class="appointment-header">
                            <h3><?php echo htmlspecialchars($apt['type_name']); ?></h3>
                            <span class="badge badge-<?php echo strtolower($apt['status']); ?>">
                                <?php echo $apt['status']; ?>
                            </span>
                        </div>
                        
                        <div class="appointment-details">
                            <p><strong>📅 Date:</strong> <?php echo date('l, F j, Y', strtotime($apt['appointment_date'])); ?></p>
                            <p><strong>⏰ Time:</strong> <?php echo date('h:i A', strtotime($apt['start_time'])); ?> - <?php echo date('h:i A', strtotime($apt['end_time'])); ?></p>
                            <p><strong>🔖 Reference:</strong> <?php echo htmlspecialchars($apt['booking_reference']); ?></p>
                            
                            <?php if ($apt['purpose']): ?>
                            <p><strong>📝 Purpose:</strong> <?php echo htmlspecialchars($apt['purpose']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($apt['status'] == 'Cancelled' && $apt['cancellation_reason']): ?>
                            <p><strong>❌ Cancellation Reason:</strong> <?php echo htmlspecialchars($apt['cancellation_reason']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="appointment-actions">
                            <?php if ($apt['status'] == 'Confirmed' && strtotime($apt['appointment_date'] . ' ' . $apt['start_time']) > time()): ?>
                            <button onclick="showCancelModal(<?php echo $apt['appointment_id']; ?>, '<?php echo htmlspecialchars($apt['booking_reference']); ?>')" 
                                    class="btn btn-sm btn-danger">
                                Cancel Appointment
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Cancel Modal -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <h2>Cancel Appointment</h2>
            <p>Are you sure you want to cancel this appointment?</p>
            <p><strong>Reference:</strong> <span id="modalReference"></span></p>
            
            <form method="POST">
                <input type="hidden" name="appointment_id" id="modalAppointmentId">
                
                <div class="form-group">
                    <label>Cancellation Reason (Optional)</label>
                    <textarea name="cancellation_reason" class="form-control" rows="3" 
                              placeholder="Please provide a reason for cancellation"></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" name="cancel_appointment" class="btn btn-danger">
                        Yes, Cancel Appointment
                    </button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">
                        No, Keep Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    .filters {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }
    .filter-btn {
        padding: 0.75rem 1.5rem;
        border: 2px solid var(--gray-300);
        border-radius: 8px;
        background: white;
        color: var(--dark-color);
        font-weight: 600;
        transition: all 0.3s;
    }
    .filter-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }
    .filter-btn.active {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }
    .appointments-grid {
        display: grid;
        gap: 1.5rem;
    }
    .appointment-card {
        background: white;
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s;
    }
    .appointment-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-color: var(--primary-color);
    }
    .appointment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--gray-200);
    }
    .appointment-header h3 {
        color: var(--dark-color);
        margin: 0;
    }
    .appointment-details p {
        margin: 0.75rem 0;
        color: var(--gray-700);
    }
    .appointment-details strong {
        color: var(--dark-color);
        margin-right: 0.5rem;
    }
    .appointment-actions {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--gray-200);
    }
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    .modal.show {
        display: flex;
    }
    .modal-content {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }
    .modal-content h2 {
        color: var(--dark-color);
        margin-bottom: 1rem;
    }
    .modal-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
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
    function showCancelModal(appointmentId, reference) {
        document.getElementById('modalAppointmentId').value = appointmentId;
        document.getElementById('modalReference').textContent = reference;
        document.getElementById('cancelModal').classList.add('show');
    }
    
    function closeModal() {
        document.getElementById('cancelModal').classList.remove('show');
    }
    
    // Close modal on outside click
    document.getElementById('cancelModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    </script>
</body>
</html>