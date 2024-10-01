<?php 
session_start(); // Start the session
include 'db.php';

// Check if NIC is set
if (!isset($_SESSION["nic"])) {
    die("Access denied. Please log in.");
}

$nic = $_SESSION["nic"];

// Fetch chat messages function
function fetchChatMessages($conn, $userNic, $tutorNic) {
    $query = "SELECT * FROM chat_messages 
              WHERE (sender_nic = '$userNic' AND receiver_nic = '$tutorNic') 
              OR (sender_nic = '$tutorNic' AND receiver_nic = '$userNic')
              ORDER BY timestamp ASC";
    return mysqli_query($conn, $query);
}

// Check if the student is registered for a class with the tutor
function isRegistered($conn, $studentNic, $classId) {
    $query = "SELECT tutor_id FROM classes 
              JOIN student_classes ON student_classes.class_id = classes.id 
              WHERE nic = '$studentNic' AND class_id = '$classId'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result)['tutor_nic'] ?? null;
}

// Handle sending chat messages
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['chat_message'])) {
    $sender_nic = $_SESSION['nic']; // Get the logged-in user's NIC
    $receiver_nic = $_POST['receiver_nic']; // Get the recipient's NIC
    $class_id = $_POST['class_id']; // Get the class ID

    // Fetch the tutor's NIC from the class
    $tutor_nic = isRegistered($conn, $sender_nic, $class_id);
    
    if ($tutor_nic === $receiver_nic) {
        $message = mysqli_real_escape_string($conn, $_POST['message']);
        
        $query = "INSERT INTO chat_messages (sender_nic, receiver_nic, message) VALUES ('$sender_nic', '$receiver_nic', '$message')";
        mysqli_query($conn, $query);
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "<script>alert('You are not authorized to message this user.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Dashboard</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/css.css">
   <style>
      .chat-box {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
    background-color: #fff;
    max-width: 600px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.chat-message {
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 8px;
    background-color: #f1f1f1;
    position: relative;
}

.chat-message strong {
    display: block;
    color: #007bff;
    font-weight: bold;
}

.chat-message small {
    display: block;
    font-size: 12px;
    color: #999;
}

.chat-form {
    display: flex;
    flex-direction: column;
    margin-top: 15px;
}

.chat-form textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    resize: none;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    font-family: Arial, sans-serif;
}

.chat-form textarea:focus {
    outline: none;
    border-color: #007bff;
}

.chat-form button {
    background-color: #007bff;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.chat-form button:hover {
    background-color: #0056b3;
}

.chat-form button:active {
    transform: translateY(1px);
}

.chat-form button:focus {
    outline: none;
}

   </style>
</head>
<body>
<header class="header">
   <section class="flex">
      <a href="home.html" class="logo">VERVERSE</a>
      <!-- Additional header content -->
   </section>
</header>

<!-- Chat Box -->
<div class="chat-box">
    <h2 class="heading">Chat</h2>
    <div id="messages">
        <?php
        $userNic = $_SESSION['nic']; // Assuming NIC is stored in the session
        $tutorNic = "tutor_nic"; // Set this to the NIC of the tutor you want to chat with
        $classId = "class_id"; // Set this to the class ID the student is registered for

        // Ensure the user is allowed to chat
        if ($tutorNic) {
            $chatMessages = fetchChatMessages($conn, $userNic, $tutorNic);

            while ($message = mysqli_fetch_assoc($chatMessages)) {
                echo '<div class="chat-message">';
                echo '<strong>' . htmlspecialchars($message['sender_nic']) . ':</strong> ';
                echo htmlspecialchars($message['message']);
                echo '<small>' . htmlspecialchars($message['timestamp']) . '</small>';
                echo '</div>';
            }
        } else {
            echo "<div class='chat-message'>You are not authorized to view this chat.</div>";
        }
        ?>
    </div>

    <form action="" method="post" class="chat-form">
        <input type="hidden" name="receiver_nic" value="<?php echo $tutorNic; ?>"> <!-- Set the tutor NIC -->
        <input type="hidden" name="class_id" value="<?php echo $classId; ?>"> <!-- Set the class ID -->
        <textarea name="message" required placeholder="Type your message..."></textarea>
        <button type="submit" name="chat_message">Send</button>
    </form>
</div>

<!-- Additional content -->
<script src="js/script.js"></script>
</body>
</html>
