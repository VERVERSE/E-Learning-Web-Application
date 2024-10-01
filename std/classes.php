<?php
include 'db.php';
session_start();


// Retrieve the NIC from the session
$nic = $_SESSION["nic"];

// Retrieve profile data
$sql = mysqli_query($conn, "SELECT * FROM update_profile WHERE nic ='{$nic}'");

if (mysqli_num_rows($sql) > 0) {
    $profile = mysqli_fetch_assoc($sql);
} else {
    $profile = null;
}

// Retrieve student's profile data (category and stream)
$stmt = $conn->prepare("SELECT category, stream, course FROM update_profile WHERE nic = ?");
$stmt->bind_param("s", $nic);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
    $category = $profile['category']; // A/L or Course
    $student_stream = $profile['stream']; // Stream for A/L
    $student_course = $profile['course'];
} else {
    $category = '';
    $student_stream = ''; // Default if no category or stream found
    $student_course = '';
}

$courses = array();

if (!empty($category)) {
    if ($category == 'A/L' && !empty($student_stream)) {
        // Fetch classes that match the student's A/L stream
        $stmt = $conn->prepare("SELECT * FROM classes WHERE stream = ?");
        $stmt->bind_param("s", $student_stream);
    } elseif ($category == 'Course') {
        // Fetch courses if the category is a course
        $stmt = $conn->prepare("SELECT * FROM courses WHERE course = ?");
        $stmt->bind_param("s", $student_course);
    } else {
        $stmt = null;
    }

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $courses[] = array(
                "id" => $row["id"],
                "code" => $row["code"],
                "name" => $row["name"],
                "tutor_name" => $row["tutor_name"],
                "stream" => isset($row["stream"]) ? $row["stream"] : '', // Check if key exists
                "time" => $row["time"],
                "date" => $row["date"],
            );
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Styles for message modal */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0, 0, 0, 0.6); 
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto; 
            padding: 30px; 
            border-radius: 12px; 
            width: 80%; 
            max-width: 600px; 
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.3); 
            text-align: center;
        }
        .modal-content h2 {
            font-size: 2em; 
            margin-bottom: 20px;
            color: black;
        }
        .modal-content p {
            font-size: 1.5em; 
            color: black;
            margin-bottom: 25px;
        }
        .modal-content button {
            font-size: 1.3em; 
            padding: 15px 30px; 
            border: none;
            border-radius: 8px; 
            background: linear-gradient(145deg, #007BFF, #0056b3); 
            color: #fff;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
        }
        .modal-content button:hover {
            background-color: #0056b3;
            transform: scale(1.05); 
        }
        .close-message {
            color: #aaa;
            float: right;
            font-size: 2em; 
            font-weight: bold;
            cursor: pointer;
        }
        .close-message:hover,
        .close-message:focus {
            color: #000;
        }
        .register-btn {
            font-size: 1.2em; 
            padding: 10px 20px; 
            border: none;
            border-radius: 8px; 
            background: linear-gradient(145deg, #007BFF, #0056b3); 
            color: #fff;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
            text-align: center;
            outline: none; 
        }
        .register-btn:hover {
            background: linear-gradient(145deg, #0056b3, #003d7a); 
            transform: scale(1.05); 
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); 
        }
        .join-btn {
    font-size: 1.2em; 
    padding: 10px 20px; 
    border: none;
    border-radius: 8px; 
    background: linear-gradient(145deg, #28a745, #218838); /* Green gradient for Join button */
    color: #fff;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease; 
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
    text-align: center; 
    outline: none; 
}

.join-btn:hover {
    background: linear-gradient(145deg, #218838, #1e7e34); /* Darker green on hover */
    transform: scale(1.05); 
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); 
}

/* Add spacing between buttons */
.course-box {
    margin-bottom: 20px; /* Space below each course box */
}

.course-box button {
    margin-right: 70px; /* Space between Register and Join buttons */
}
.register-btn, .join-btn {
    font-size: 1.2em; 
    padding: 10px 20px; 
    border: none;
    border-radius: 8px; 
    color: #fff;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease; 
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
    text-align: center; 
    outline: none; 
    width: 150px; /* Same width for both buttons */
}

.register-btn {
    background: linear-gradient(145deg, #007BFF, #0056b3); /* Blue gradient for Register button */
}

.register-btn:hover {
    background: linear-gradient(145deg, #0056b3, #003d7a); /* Darker blue on hover */
}

.join-btn {
    background: linear-gradient(145deg, #28a745, #218838); /* Green gradient for Join button */
    margin-left: -10px; /* Shift Join button slightly to the left */
}

.join-btn:hover {
    background: linear-gradient(145deg, #218838, #1e7e34); /* Darker green on hover */
}

/* Add spacing below each course box */
.course-box {
    margin-bottom: 20px; 
}

.course-box div {
    display: flex; /* Flex container for buttons */
    gap: 10px; /* Space between buttons */
}


    </style>
</head>
<body>
    <header class="header">
        <section class="flex">
            <a href="home.php" class="logo">VERVERSE</a>
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
      <h3 class="name"><?php echo $_SESSION['name']; ?></h3>
      <p class="role"><?php echo $_SESSION['role']; ?></p>
      <a href="profile.php" class="btn">view profile</a>
   </div>
        <nav class="navbar">
            <a href="home.php"><i class="fas fa-home"></i><span>HOME</span></a>
            <a href="profile.php"><i class="fa-solid fa-user"></i><span>PROFILE</span></a>
            <a href="classes.php"><i class="fas fa-graduation-cap"></i><span>CLASSES</span></a>
            <a href="calendar.php"><i class="fa-regular fa-calendar-days"></i><span>CALENDAR</span></a>
            <a href="help.php"><i class="fas fa-headset"></i><span>HELP</span></a>
        </nav>
    </div>

    <section class="courses">
        <h1 class="heading">
            <?php 
            if ($category == 'A/L') {
                echo "Classes for Your A/L Stream: " . htmlspecialchars($student_stream);
            } elseif ($category == 'Course') {
                echo "Courses for You: " . htmlspecialchars($student_course);
            } else {
                echo "Available Classes/Courses";
            }
            ?>
        </h1>

        <div class="course-container">
            <?php
            if (!empty($courses)) {
                foreach ($courses as $course) {
                    echo '<div class="course-box" data-class-id="' . htmlspecialchars($course["id"]) . '">
                        <h2>' . htmlspecialchars($course["name"]) . '</h2>
                        <p class="info-item"><span class="label">Tutor Name:</span> ' . htmlspecialchars($course["tutor_name"]) . '</p>';
                    
                    // Conditional display based on category
                    if ($category == 'A/L' && !empty($course["stream"])) {
                        echo '<p class="info-item"><span class="label">Stream:</span> ' . htmlspecialchars($course["stream"]) . '</p>';
                    } elseif ($category == 'Course') {
                        echo '<p class="info-item"><span class="label">Course:</span> ' . htmlspecialchars($student_course) . '</p>';
                    }

                    echo '<p class="info-item"><span class="label">Time:</span> ' . htmlspecialchars($course["time"]) . '</p>
                        <p class="info-item"><span class="label">Date:</span> ' . htmlspecialchars($course["date"]) . '</p>
                        <i class="fas fa-book course-icon"></i>
                        <button class="register-btn">Register</button>
                        <button class="join-btn">Join</button>
                        <p class="response-message"></p>
                    </div>';
                }
            } else {
                echo '<h2>No classes/courses found for your selection.</h2>';
            }
            ?>
        </div>
    </section>

    <!-- Modal structure for registration messages -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close-message">&times;</span>
            <p id="message-text"></p>
            <button class="btn-ok" id="ok-btn">OK</button>
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

    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var registerButtons = document.querySelectorAll('.register-btn');
            var messageModal = document.getElementById('messageModal');
            var messageText = document.getElementById('message-text');
            var closeMessage = document.querySelector('.close-message');
            var okBtn = document.getElementById('ok-btn');

            registerButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var courseBox = this.closest('.course-box');
                    var classId = courseBox.getAttribute('data-class-id');

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'register_class.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            var response = JSON.parse(xhr.responseText);
                            messageText.textContent = response.message;
                            messageModal.style.display = "block";

                            if (response.message.includes('Success')) {
                                button.disabled = true;
                                button.textContent = 'Registered';
                            }
                        }
                    };
                    xhr.send('class_id=' + encodeURIComponent(classId));
                });
            });   

            var logoutModal = document.getElementById("logoutModal");
            var logoutBtn = document.getElementById("logout-btn");
            var closeBtn = document.getElementsByClassName("close")[0];
            var cancelBtn = document.getElementById("cancel-logout");
            var confirmLogoutBtn = document.getElementById("confirm-logout");

            logoutBtn.onclick = function(event) {
                event.preventDefault();
                logoutModal.style.display = "block";
            }

            closeBtn.onclick = function() {
                logoutModal.style.display = "none";
            }

            cancelBtn.onclick = function() {
                logoutModal.style.display = "none";
            }

            confirmLogoutBtn.onclick = function() {
                window.location.href = "/vv/otp/login.php";
            }

            window.onclick = function(event) {
                if (event.target == logoutModal) {
                    logoutModal.style.display = "none";
                }
            }

            closeMessage.onclick = function() {
                messageModal.style.display = "none";
            }

            okBtn.onclick = function() {
                messageModal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == messageModal) {
                    messageModal.style.display = "none";
                }
            } 
        });
        document.addEventListener('DOMContentLoaded', function() {
    // Existing code...

    var joinButtons = document.querySelectorAll('.join-btn');

    joinButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            // Redirect to the live chat page
            window.location.href = '/vv/live_chat/index.html';
        });
    });

    // Existing code...
});

    </script>
</body>
</html>
