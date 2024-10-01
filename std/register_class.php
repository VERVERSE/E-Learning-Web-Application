<?php
include 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['nic'])) {
    echo json_encode(['message' => 'Error: User not logged in']);
    exit();         
}

// Check if the form is submitted and the class_id is set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['class_id'])) {
    $class_id = trim($_POST['class_id']);
    $nic = $_SESSION['nic'];

    // Input validation for class_id
    if (empty($class_id) || !ctype_digit($class_id)) {
        echo json_encode(['message' => 'Error: Invalid class ID']);
        exit();
    }

    // Retrieve the user's category from the update_profile table
    $stmt = $conn->prepare("SELECT category FROM update_profile WHERE nic = ?");
    $stmt->bind_param("s", $nic);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['message' => 'Error: User category not found']);
        exit();
    }

    $user_profile = $result->fetch_assoc();
    $category = $user_profile['category'];

    // Prepare and bind to check if the student is already registered
    if ($category === 'A/L') {
        $stmt = $conn->prepare("SELECT registration_date FROM student_classes WHERE class_id = ? AND nic = ?");
    } elseif ($category === 'Course') {
        $stmt = $conn->prepare("SELECT registration_date FROM student_courses WHERE course_id = ? AND nic = ?");
    } else {
        echo json_encode(['message' => 'Error: Unknown category']);
        exit();
    }

    if ($stmt === false) {
        echo json_encode(['message' => 'Error: Database query failed']);
        exit();
    }

    $stmt->bind_param("ss", $class_id, $nic);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the student is already registered for the class/course
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $registration_date = $row['registration_date'];
        echo json_encode(['message' => 'Error: You are already registered for this class/course', 'registration_date' => $registration_date]);
    } else {
        // Insert the new registration based on the category
        if ($category === 'A/L') {
            $stmt = $conn->prepare("INSERT INTO student_classes (class_id, nic, registration_date) VALUES (?, ?, NOW())");
        } elseif ($category === 'Course') {
            $stmt = $conn->prepare("INSERT INTO student_courses (course_id, nic, registration_date) VALUES (?, ?, NOW())");
        }

        if ($stmt === false) {
            echo json_encode(['message' => 'Error: Failed to prepare registration query']);
            exit();
        }

        $stmt->bind_param("ss", $class_id, $nic);
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Success: You have registered for the class/course', 'registration_date' => date('Y-m-d H:i:s')]);
        } else {
            echo json_encode(['message' => 'Error: ' . $stmt->error]);
        }
    }

    $stmt->close();
} else {
    echo json_encode(['message' => 'Error: Invalid request']);
}

$conn->close();
?>
