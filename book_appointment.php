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

$error = '';
$success = '';
$appointment_types = $appointmentModel->getAppointmentTypes();
$available_slots = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type_id = $_POST['type_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['start_time'];
    $purpose = trim($_POST['purpose']);
    
    $result = $appointmentModel->bookAppointment($_SESSION['user_id'], $type_id, $date, $time, $purpose);
    
    if ($result['success']) {
        $success = "Appointment booked successfully! Reference: " . $result['booking_reference'];
        $_SESSION['booking_success'] = $success;
        header('Location: my_appointments.php');
        exit;
    } else {
        $error = $result['message'];
    }
}

// Get available slots via AJAX
if (isset($_GET['get_slots'])) {
    header('Content-Type: application/json');
    $date = $_GET['date'];
    $type_id = $_GET['type_id'];
    $slots = $appointmentModel->getAvailableSlots($date, $type_id);
    echo json_encode($slots);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>📅 Book an Appointment</h1>
            <p>Select your preferred date, time, and appointment type</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="section">
            <form method="POST" id="bookingForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Appointment Type <span class="required">*</span></label>
                        <select name="type_id" id="appointmentType" class="form-control" required onchange="loadSlots()">
                            <option value="">Select appointment type</option>
                            <?php foreach ($appointment_types as $type): ?>
                            <option value="<?php echo $type['type_id']; ?>" 
                                    data-duration="<?php echo $type['duration_minutes']; ?>">
                                <?php echo htmlspecialchars($type['type_name']); ?> 
                                (<?php echo $type['duration_minutes']; ?> mins)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text" id="typeDescription"></small>
                    </div>
                    
                    <div class="form-group">
                        <label>Appointment Date <span class="required">*</span></label>
                        <input type="date" name="appointment_date" id="appointmentDate" 
                               class="form-control" required 
                               min="<?php echo date('Y-m-d'); ?>"
                               max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"
                               onchange="loadSlots()">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Available Time Slots <span class="required">*</span></label>
                    <div id="slotsContainer" class="slots-container">
                        <p class="text-muted">Please select appointment type and date first</p>
                    </div>
                    <input type="hidden" name="start_time" id="selectedTime" required>
                </div>
                
                <div class="form-group">
                    <label>Purpose/Description</label>
                    <textarea name="purpose" class="form-control" rows="4" 
                              placeholder="Please describe the purpose of your appointment"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        📅 Confirm Booking
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        
        <!-- Appointment Types Info -->
        <div class="section">
            <h2>Available Appointment Types</h2>
            <div class="appointment-types-grid">
                <?php foreach ($appointment_types as $type): ?>
                <div class="appointment-type-card">
                    <h3><?php echo htmlspecialchars($type['type_name']); ?></h3>
                    <p class="duration">⏱️ <?php echo $type['duration_minutes']; ?> minutes</p>
                    <p class="description"><?php echo htmlspecialchars($type['description']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
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
    .slots-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 0.75rem;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: 8px;
        min-height: 150px;
    }
    .time-slot {
        padding: 0.75rem;
        border: 2px solid var(--gray-300);
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: white;
        font-weight: 600;
    }
    .time-slot:hover {
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }
    .time-slot.selected {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    .time-slot.unavailable {
        background: var(--gray-200);
        color: var(--gray-500);
        cursor: not-allowed;
        opacity: 0.5;
    }
    .time-slot.unavailable:hover {
        transform: none;
        border-color: var(--gray-300);
    }
    .appointment-types-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    .appointment-type-card {
        background: var(--gray-50);
        padding: 1.5rem;
        border-radius: 12px;
        border-left: 4px solid var(--primary-color);
    }
    .appointment-type-card h3 {
        color: var(--dark-color);
        margin-bottom: 0.5rem;
    }
    .appointment-type-card .duration {
        color: var(--primary-color);
        font-weight: 600;
        margin: 0.5rem 0;
    }
    .appointment-type-card .description {
        color: var(--gray-600);
        font-size: 0.9rem;
        line-height: 1.5;
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
    .text-muted {
        color: var(--gray-500);
        text-align: center;
        padding: 2rem;
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
    
    <script>
    let selectedSlot = null;
    
    function loadSlots() {
        const typeId = document.getElementById('appointmentType').value;
        const date = document.getElementById('appointmentDate').value;
        const container = document.getElementById('slotsContainer');
        
        if (!typeId || !date) {
            container.innerHTML = '<p class="text-muted">Please select appointment type and date first</p>';
            return;
        }
        
        // Show loading
        container.innerHTML = '<p class="text-muted">Loading available slots...</p>';
        
        // Fetch slots via AJAX
        fetch(`book_appointment.php?get_slots=1&type_id=${typeId}&date=${date}`)
            .then(response => response.json())
            .then(slots => {
                if (slots.length === 0) {
                    container.innerHTML = '<p class="text-muted">No slots available for this date</p>';
                    return;
                }
                
                container.innerHTML = '';
                slots.forEach(slot => {
                    const slotDiv = document.createElement('div');
                    slotDiv.className = 'time-slot' + (slot.available ? '' : ' unavailable');
                    slotDiv.textContent = slot.display;
                    
                    if (slot.available) {
                        slotDiv.onclick = () => selectSlot(slot.time, slotDiv);
                    }
                    
                    container.appendChild(slotDiv);
                });
            })
            .catch(error => {
                container.innerHTML = '<p class="text-muted">Error loading slots. Please try again.</p>';
                console.error('Error:', error);
            });
    }
    
    function selectSlot(time, element) {
        // Remove previous selection
        if (selectedSlot) {
            selectedSlot.classList.remove('selected');
        }
        
        // Select new slot
        element.classList.add('selected');
        selectedSlot = element;
        document.getElementById('selectedTime').value = time;
    }
    
    // Show appointment type description
    document.getElementById('appointmentType').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const description = selectedOption.text;
        document.getElementById('typeDescription').textContent = description;
    });
    
    // Form validation
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const selectedTime = document.getElementById('selectedTime').value;
        if (!selectedTime) {
            e.preventDefault();
            alert('Please select a time slot');
            return false;
        }
    });
    </script>
</body>
</html>