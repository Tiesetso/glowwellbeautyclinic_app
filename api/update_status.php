<?php

require_once '../dbconnection.php';
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.');
}

// Only admins can use this PHP script
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    sendResponse(false, 'Access denied. Administrator privileges required.');
} 

$appointmentId = trim($_POST['appointment_id'] ?? '');
$status = trim($_POST['status'] ?? '');

if (empty($appointmentId) || empty($status) || !is_numeric($appointmentId)) {
    sendResponse(false, 'Invalid update parameters.');
}

// Whitelist validation
$allowedStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];

if (!in_array($status, $allowedStatuses)) {
    sendResponse(false, 'Invalid status choice provided.');
}

// Execute the update query
$stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");

if ($stmt->execute([$status, $appointmentId])) {
    sendResponse(true, "Appointment #{$appointmentId} status updated to {$status}.");
} else {
    sendResponse(false, 'Unable to update appointment status.');
}

?>