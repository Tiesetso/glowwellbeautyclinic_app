<?php

require_once '../dbconnection.php';
require_once 'helpers.php';

// Clear all session variables
session_unset();

// Destroy the session on the server
session_destroy();

// redirects 
if (isset($_GET['redirect'])) {
    header('Location: ../index.html');
    exit;
}

sendResponse(true, 'Logged out successfully.', ['redirect' => 'index.html']);

?>