<?php

session_start();

$host = 'localhost';
$dbname = 'glowwell_db';
$username = 'glowwell_user';
$password = 'gl0#w3llk';

// Use try and catch to trap errors
try {

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // Testing connection to the database
    //echo "The database is Online!" . "<br>";

} catch (PDOException $e) {
    die("Database Connection Failed: " . htmlspecialchars($e->getMessage()));
}

?>