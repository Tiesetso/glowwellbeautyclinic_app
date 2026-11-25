<?php

require_once '../dbconnection.php';
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.');
}

if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Unauthorized. Please log in.');
}

$appointmentId = trim($_POST['appointment_id'] ?? '');

if (empty($appointmentId) || !is_numeric($appointmentId)) {
    sendResponse(false, 'Invalid appointment ID.');
}

// Update status to 'Cancelled' only if this booking belongs to the logged-in customer (or user is admin)
$stmt = $pdo->prepare(
    "UPDATE appointments 
    SET status = 'Cancelled' 
    WHERE appointment_id = ? AND (customer_id = ? OR ? = 'admin')"
    );
    
if ($stmt->execute([$appointmentId, $_SESSION['user_id'], $_SESSION['role']])) {
    sendResponse(true, 'Appointment successfully cancelled.');
} else {
    sendResponse(false, 'Failed to cancel appointment.');
}

?>