<?php
// Database connection
$servername = "localhost";  // Replace with your server name
$username = "root";  // Replace with your database username
$password = "";  // Replace with your database password
$dbname = "ververse";  // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['message'])) {
    $message = $conn->real_escape_string($_POST['message']);
    $username = 'Student';  // You can replace this with the logged-in user's name

    // Insert the message into the database
    $sql = "INSERT INTO chat_messages (username, message) VALUES ('$username', '$message')";
    if ($conn->query($sql) === TRUE) {
        echo "Message sent";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
