<?php

require_once '../dbconnection.php';
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.');
}

// Ensures that user is logged in before booking
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Unauthorized session. Please log in first.');
}

// Collect booking inputs
$customerId = $_SESSION['user_id'];
$staffId = trim($_POST['staff_id'] ?? '');
$serviceId = trim($_POST['service_id'] ?? '');
$date = trim($_POST['appointment_date'] ?? '');
$time = trim($_POST['appointment_time'] ?? '');
$notes = trim($_POST['notes'] ?? '');

// Validate required fields using empty()
if (empty($staffId) || empty($serviceId) || empty($date) || empty($time)) {
    sendResponse(false, 'Please fill in all booking details.');
}

// Validate that IDs are numbers using is_numeric() 
if (!is_numeric($staffId) || !is_numeric($serviceId)) {
    sendResponse(false, 'Invalid service or staff selection.');
}

// Prevent booking dates in the past
if (strtotime($date) < strtotime(date('Y-m-d'))) {
    sendResponse(false, 'Appointments cannot be booked on past dates.');
}

// Double-Booking Prevention Check
$conflictStmt = $pdo->prepare(
    "SELECT appointment_id FROM appointments 
    WHERE staff_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'Cancelled'"
    );

$conflictStmt->execute([$staffId, $date, $time]);

if ($conflictStmt->fetch()) {
    sendResponse(false, 'The selected specialist is already booked for this time slot. Please choose another slot or specialist.');
}

// Insert the new appointment as 'Pending'
$insert = $pdo->prepare(
    "INSERT INTO appointments (customer_id, staff_id, service_id, appointment_date, appointment_time, notes, status) 
    VALUES (?, ?, ?, ?, ?, ?, 'Pending')"
    );

if ($insert->execute([$customerId, $staffId, $serviceId, $date, $time, $notes])) {
    sendResponse(true, 'Appointment successfully booked! Status is currently Pending confirmation.');
} else {
    sendResponse(false, 'Failed to complete appointment booking.');
}

?>