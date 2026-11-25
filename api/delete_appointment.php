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

if (empty($appointmentId) || !is_numeric($appointmentId)) {
    sendResponse(false, 'Invalid appointment ID.');
}

// Permanently delete the appointment record
$stmt = $pdo->prepare("DELETE FROM appointments WHERE appointment_id = ?");

if ($stmt->execute([$appointmentId])) {
    sendResponse(true, "Appointment #{$appointmentId} has been permanently deleted.");
} else {
    sendResponse(false, 'Unable to delete appointment record.');
}

?>