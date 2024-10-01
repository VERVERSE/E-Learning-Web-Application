<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "webrtc_signaling";

// Create connection to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle POST request to store signaling data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roomId = $_POST['roomId'];
    $dataType = $_POST['type'];
    $data = $_POST['data'];

    $stmt = $conn->prepare("INSERT INTO signaling_data (room_id, data_type, data) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $roomId, $dataType, $data);
    $stmt->execute();
    echo "Data saved successfully.";
}

// Handle GET request to retrieve signaling data
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $roomId = $_GET['roomId'];
    $dataType = $_GET['type'];

    $stmt = $conn->prepare("SELECT data FROM signaling_data WHERE room_id = ? AND data_type = ?");
    $stmt->bind_param("ss", $roomId, $dataType);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo $row['data'];
    } else {
        echo "No data found.";
    }
}

$conn->close();
?>
