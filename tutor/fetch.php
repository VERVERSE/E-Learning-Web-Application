<?php
// Database configuration
$host = 'localhost'; // Database host
$db = 'ververse'; // Your database name
$user = 'root'; // Your database username
$pass = ''; // Your database password

// Create a new PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch participants data
    $stmt = $pdo->query("SELECT * FROM registered_classes");
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Output data as JSON
    header('Content-Type: application/json');
    echo json_encode($participants);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>