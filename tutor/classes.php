<?php
include 'db.php';
session_start();

// Retrieve the NIC from the session
$nic = $_SESSION["nic"];

// Retrieve profile data
$sql = mysqli_query($conn, "SELECT * FROM tutor WHERE nic ='{$nic}'");

if (mysqli_num_rows($sql) > 0) {
    $profile = mysqli_fetch_assoc($sql);
} else {
    $profile = null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $code = $_POST['code'] ?? '';
    $date = $_POST['date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $tutor_id = $_SESSION["nic"];

    // Check if required fields are filled
    if ($name && $date && $start_time && $end_time) {
        // Query to get the tutor's subject and course from the tutor table using the NIC
        $query = "SELECT subject, course, category FROM tutor WHERE nic = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $tutor_id);
        $stmt->execute();
        $stmt->bind_result($subject, $course, $tutor_category);
        $stmt->fetch();
        $stmt->close();

        // Format start and end times to 12-hour format with AM/PM
        $formatted_start_time = date("g:i A", strtotime($start_time));
        $formatted_end_time = date("g:i A", strtotime($end_time));
        $time_range = $formatted_start_time . ' to ' . $formatted_end_time;

        // Insert the class or course data based on the tutor category
        $tutor_name = $_SESSION["name"];
        if ($tutor_category === 'A/L') {
            $stmt = $conn->prepare("INSERT INTO classes (tutor_id, code, name, tutor_name, stream, date, time) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $tutor_id, $code, $name, $tutor_name, $subject, $date, $time_range);
        } elseif ($tutor_category === 'Course') {
            $stmt = $conn->prepare("INSERT INTO courses (tutor_id, code, name, tutor_name, course, date, time) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $tutor_id, $code, $name, $tutor_name, $course, $date, $time_range);
        } else {
            echo '<p class="error">Invalid tutor category.</p>';
            exit;
        }

        if ($stmt->execute()) {
            // Optionally handle success
        } else {
            echo '<p class="error">Error: ' . $stmt->error . '</p>';
        }

        $stmt->close();
    } else {
        echo '<p class="error">Please fill in all fields.</p>';
    }
}

// Handle deletion of a class or course
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']); // Convert to integer for safety
    $tutor_id = $_SESSION["nic"];

    // Query to get the tutor's category
    $tutor_category_query = "SELECT category FROM tutor WHERE nic = ?";
    $tutor_category_stmt = $conn->prepare($tutor_category_query);
    $tutor_category_stmt->bind_param("s", $tutor_id);
    $tutor_category_stmt->execute();
    $tutor_category_stmt->bind_result($tutor_category);
    $tutor_category_stmt->fetch();
    $tutor_category_stmt->close();

    if ($id) {
        if ($tutor_category === 'A/L') {
            // Delete related entries from student_classes first
            $delete_students_stmt = $conn->prepare("DELETE FROM student_classes WHERE class_id = ?");
            $delete_students_stmt->bind_param("i", $id);
            $delete_students_stmt->execute();
            $delete_students_stmt->close();

            // Then delete the class itself
            $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
        } elseif ($tutor_category === 'Course') {
            // Delete related entries from student_courses first
            $delete_students_stmt = $conn->prepare("DELETE FROM student_courses WHERE course_id = ?");
            $delete_students_stmt->bind_param("i", $id);
            $delete_students_stmt->execute();
            $delete_students_stmt->close();

            // Then delete the course itself
            $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        } else {
            echo '<p class="error">Invalid tutor category.</p>';
            exit;
        }

        if ($stmt) {
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                // Optionally handle success
            } else {
                echo '<p class="error">Error: ' . $stmt->error . '</p>';
            }

            $stmt->close();
        } else {
            echo '<p class="error">Error preparing statement.</p>';
        }
    } else {
        echo '<p class="error">Invalid ID.</p>';
    }
}
// Retrieve classes or courses based on tutor's category
$tutor_id = $_SESSION["nic"];

// Query to get the tutor's category
$tutor_category_query = "SELECT category FROM tutor WHERE nic = ?";
$tutor_category_stmt = $conn->prepare($tutor_category_query);
$tutor_category_stmt->bind_param("s", $tutor_id);
$tutor_category_stmt->execute();
$tutor_category_stmt->bind_result($tutor_category);
$tutor_category_stmt->fetch();
$tutor_category_stmt->close();

// Retrieve scheduled classes or courses based on category
if ($tutor_category === 'A/L') {
    $sql_classes = "SELECT * FROM classes WHERE tutor_id = ?";
    $stmt_classes = $conn->prepare($sql_classes);
    $stmt_classes->bind_param("s", $tutor_id);
    $stmt_classes->execute();
    $result_classes = $stmt_classes->get_result();

    if ($result_classes->num_rows > 0) {
        $classes = $result_classes->fetch_all(MYSQLI_ASSOC);
    } else {
        $classes = [];
    }
    $stmt_classes->close();
} elseif ($tutor_category === 'Course') {
    $sql_courses = "SELECT * FROM courses WHERE tutor_id = ?";
    $stmt_courses = $conn->prepare($sql_courses);
    $stmt_courses->bind_param("s", $tutor_id);
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();

    if ($result_courses->num_rows > 0) {
        $courses = $result_courses->fetch_all(MYSQLI_ASSOC);
    } else {
        $courses = [];
    }
    $stmt_courses->close();
} else {
    $classes = [];
    $courses = [];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/css.css">
    <script src="/vv/js/jquery.1.8.3.min.js"></script>
    <style>
        .container {
            position: relative;
            padding: 35px;
        }

        .add-class-btn {
            position: absolute;
            top: 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 15px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
        }

        .add-class-btn:hover {
            background-color: #45a049;
        }

        .card {
            margin-top: 17px;
        }

        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1000;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.5);
            /* Black w/ opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            /* Could be more or less, depending on screen size */
        }
    </style>
</head>

<body>
    <header class="header">
        <section class="flex">
            <a href="home.html" class="logo">VERVERSE</a>
            <form action="search.html" method="post" class="search-form">
                <input type="text" name="search_box" required placeholder="Search courses..." maxlength="100">
                <button type="submit" class="fas fa-search"></button>
            </form>
            <div class="icons">
                <div id="menu-btn" class="fas fa-bars"></div>
                <div id="search-btn" class="fas fa-search"></div>
                <div id="user-btn" class="fa-solid fa-right-from-bracket"></div>
                <div id="toggle-btn" class="fas fa-sun"></div>
            </div>
            <div class="profile">
                <div class="flex-btn">
                    <a href="#" id="logout-btn" class="option-btn">Log Out</a>
                </div>
            </div>
        </section>
    </header>

    <div class="side-bar">
        <div id="close-btn">
            <i class="fas fa-times"></i>
        </div>
        <div class="profile">
            <?php if ($profile && !empty($profile['pic'])): ?>
                <img src="<?php echo htmlspecialchars($profile['pic']); ?>" class="image" alt="Profile Picture">
            <?php else: ?>
                <img src="images/pic-1.jpg" class="image" alt="Default Profile Picture">
            <?php endif; ?>
            <h3 class="name"><?php echo htmlspecialchars($_SESSION['name']); ?></h3>
            <p class="role"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
            <a href="profile.php" class="btn">View Profile</a>
        </div>
        <nav class="navbar">
            <a href="home.php"><i class="fas fa-home"></i><span>HOME</span></a>
            <a href="profile.php"><i class="fa-solid fa-user"></i><span>PROFILE</span></a>
            <a href="classes.php"><i class="fas fa-graduation-cap"></i><span>CLASS</span></a>
            <a href="calendar.php"><i class="fa-regular fa-calendar-days"></i><span>CALENDAR</span></a>
            <a href="help.php"><i class="fas fa-headset"></i><span>HELP</span></a>
        </nav>
    </div>

    <div class="container">
        <h1>Class Management</h1>
        <button type="button" id="btnAddClass" class="add-class-btn">Schedule New Classes</button>

        <!-- Display list of classes or courses -->
        <div class="card-container">
            <?php if ($tutor_category === 'A/L' && !empty($classes)): ?>
                <?php foreach ($classes as $class): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($class['id']); ?></h3>
                        <h3><?php echo htmlspecialchars($class['name']); ?></h3>
                        <p><?php echo htmlspecialchars($class['tutor_name']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($class['date']); ?></p>
                        <p><strong>Time:</strong> <?php echo htmlspecialchars($class['time']); ?></p>
                        <a href="/vv/live_chat/index.html?id=<?php echo $class['id']; ?>" class="view-button">Start</a>
                        <a href="classes.php?delete=<?php echo $class['id']; ?>" onclick="return confirm('Are you sure you want to delete this class?');" class="delete-button">Delete</a>
                    </div>
                <?php endforeach; ?>
            <?php elseif ($tutor_category === 'Course' && !empty($courses)): ?>
                <?php foreach ($courses as $course): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($course['id']); ?></h3>
                        <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                        <p><?php echo htmlspecialchars($course['tutor_name']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($course['date']); ?></p>
                        <p><strong>Time:</strong> <?php echo htmlspecialchars($course['time']); ?></p>
                        <a href="/vv/live_chat/index.html?id=<?php echo $course['id']; ?>" class="view-button">Start</a>
                        <a href="classes.php?delete=<?php echo $course['id']; ?>" onclick="return confirm('Are you sure you want to delete this course?');" class="delete-button">Delete</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No classes or courses scheduled for this category.</p>
            <?php endif; ?>
        </div>
    </div>


    <!-- Modal for adding a class -->
    <div id="addClassModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <form action="classes.php" method="post">
                <label for="code">Class Code:</label>
                <input type="text" id="code" name="code" required>

                <label for="name">Class Name:</label>
                <input type="text" id="name" name="name" required>

                <label for="date">Date:</label>
                <input type="date" id="date" name="date" required>

                <label for="start_time">Start Time:</label>
                <input type="time" id="start_time" name="start_time" required>

                <label for="end_time">End Time:</label>
                <input type="time" id="end_time" name="end_time" required>

                <label for="repeat">Repeat Weekly:</label>
                <input type="checkbox" id="repeat" name="repeat">

                <input type="submit" value="Add Class">
            </form>
        </div>
    </div>


    <!-- Modal structure for logout confirmation -->
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p>Do you want to log out?</p>
            <div class="modal-buttons">
                <button class="btn-yes" id="confirm-logout">Yes</button>
                <button class="btn-no" id="cancel-logout">No</button>
            </div>
        </div>
    </div>

    <!-- Custom JS file link -->
    <script src="js/script.js"></script>

    <script>
        // Handle Add Class Modal display
        $("#btnAddClass").click(function() {
            $("#addClassModal").show();
        });

        $(".close").click(function() {
            $("#addClassModal").hide();
            $("#logoutModal").hide(); // Ensure logout modal is closed as well
        });

        $(window).click(function(event) {
            if (event.target == $("#addClassModal")[0]) {
                $("#addClassModal").hide();
            }
        });

        // Logout modal handling
        var modal = document.getElementById("logoutModal");
        var logoutBtn = document.getElementById("logout-btn");
        var closeBtn = document.getElementsByClassName("close")[1];
        var cancelBtn = document.getElementById("cancel-logout");
        var confirmLogoutBtn = document.getElementById("confirm-logout");

        // Show modal when logout button is clicked
        logoutBtn.onclick = function(event) {
            event.preventDefault();
            modal.style.display = "block";
        };

        // Hide modal when close button is clicked
        closeBtn.onclick = function() {
            modal.style.display = "none";
        };

        // Hide modal when cancel button is clicked
        cancelBtn.onclick = function() {
            modal.style.display = "none";
        };

        // Handle logout confirmation
        confirmLogoutBtn.onclick = function() {
            window.location.href = "/vv/otp/login.php"; // Redirect to the logout script
        };

        // Hide modal when clicking outside of the modal content
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };
    </script>
</body>

</html>