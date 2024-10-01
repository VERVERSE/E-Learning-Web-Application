<?php 
include 'db.php';

session_start();

$nic = $_SESSION["nic"];
// Retrieve profile data
$sql = mysqli_query($conn, "SELECT * FROM tutor WHERE nic ='{$nic}'");

if (mysqli_num_rows($sql) > 0) {
    $profile = mysqli_fetch_assoc($sql);
} else {
    $profile = null;
}


// Fetch email from user_reg based on NIC
$email_query = mysqli_query($conn, "SELECT email FROM user_reg WHERE nic ='{$nic}'");
$user = mysqli_fetch_assoc($email_query);
$user_email = $user['email'] ?? '';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST["submit"])) {
    $email = $_POST["email"];
    // (rest of your code)
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST["submit"])) {
   $address = $_POST["address"];
   $email = $_POST["email"];
   $mobile = $_POST["mobile"];
    $category = $_POST["al_or_course"]; // Updated to match the form field name
    $stream = $_POST["subject"];
    $course = $_POST["course"]; // Added to handle the course selection
    $workplace = $_POST["workplace"];
   $qualification = $_POST["qualification"];
   $about = $_POST["about"];
   $profile_pic = $_FILES["profile_pic"];

    if (!empty($address) && !empty($email) && !empty($mobile)  && !empty($category) && !empty($workplace) && !empty($qualification) && !empty($about) && !empty($nic)) {

        // Check if course selection is required
        if ($category == 'Course' && empty($course)) {
            $error_message = "Course selection is required when category is Course.";
        } else {

            if ($profile_pic && $profile_pic['error'] == UPLOAD_ERR_OK) {
                $target_file = dirname(__FILE__)."/profile_pictures/{$nic}_{$profile_pic["name"]}";
                $uploaded = move_uploaded_file($profile_pic["tmp_name"], $target_file);
            }

            // Prepare the SQL statement
            $stmt = mysqli_prepare($conn, "UPDATE tutor SET address = ?, email = ?, mobile = ?, category = ?, subject = ?, course = ?, workplace = ?, qualification = ?, about = ?, pic = ? WHERE nic = ?");

            // Bind parameters
            $imageUrl = "/vv/tutor/profile_pictures/"."{$nic}_{$profile_pic["name"]}";
            $pic = isset($target_file) && isset($uploaded) && $uploaded ? $imageUrl : "";

            // Clear stream or course based on category
            if ($category == 'A/L') {
                $stream = $stream;
                $course = NULL; // Clear course if category is A/L
            } else {
                $stream = NULL; // Clear stream if category is Course
                $course = $course;
            }

            mysqli_stmt_bind_param($stmt, "sssssssssss", $address, $email, $mobile, $category, $stream, $course, $workplace,$qualification,$about, $pic, $nic);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Close the statement
                mysqli_stmt_close($stmt);

                // Redirect to profile.php
                header("Location: profile.php");
                exit;
            } else {
                $error_message = "Error: " . mysqli_stmt_error($stmt);
            }
        }

    } else {
        $error_message = "All fields are required.";
    }
} 

// Retrieve existing profile data
$sql = mysqli_query($conn, "SELECT * FROM update_profile WHERE nic ='{$nic}'");

if (mysqli_num_rows($sql) > 0) {
    $profile = mysqli_fetch_assoc($sql);
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Dashboard</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

    <style>
        /* Radio button container to align items in a line */
        .radio-container {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        /* Style for radio button input */
        .radio-container input[type="radio"] {
            margin-right: 10px;
            transform: scale(1.5); /* Increase the size of the radio button */
        }

        /* Style for the label text */
        .radio-container label {
            font-size: 1.7em; /* Increase the font size */
            margin-right: 25px; /* Space between the labels */
        }

        /* Hidden class to hide elements */
        .hidden {
            display: none;
        }
    </style>
</head>
<body>

<header class="header">
    <section class="flex">
        <a href="home.php" class="logo">VERVERSE</a>

        <form action="search.html" method="post" class="search-form">
            <input type="text" name="search_box" required placeholder="search courses..." maxlength="100">
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
        <h3 class="name"><?php echo $_SESSION['name']; ?> </h3>
        <p class="role"><?php echo $_SESSION['role']; ?></p>
        <a href="profile.php" class="btn">view profile</a>
    </div>

    <nav class="navbar">
      <a href="home.php"><i class="fas fa-home"></i><span>HOME</span></a>
      <a href="profile.php"><i class="fa-solid fa-user"></i><span>PROFILE</span></a>
      <a href="classes.php"><i class="fas fa-graduation-cap"></i><span>CLASS</span></a>
      <a href="calendar.php"><i class="fa-regular fa-calendar-days"></i><span>CALENDAR</span></a>
       
      <a href="help.php"><i class="fas fa-headset"></i><span>HELP</span></a>
   </nav>
</div>

<section class="form-container">
    <form action="update.php" method="post" enctype="multipart/form-data">
        <h3>Update Profile</h3>

        <p>Address</p>
       <input type="text" name="address" placeholder="Enter Your Address" value="<?php echo isset($profile["address"]) ? htmlspecialchars($profile["address"]) : "" ?>" maxlength="100" class="box" required>
      <p>Email</p>
      <input type="email" name="email" placeholder="Enter Your Email" class="box" value="<?php echo isset($user_email) ? htmlspecialchars($user_email) : ''; ?>" required>
      <p>Mobile</p>
      <input type="text" name="mobile" placeholder="Enter your Mobile Number" value="<?php echo isset($profile["mobile"]) ? htmlspecialchars($profile["mobile"]) : "" ?>" maxlength="20" class="box" required>
        <div class="radio-container">
            <input type="radio" id="al" name="al_or_course" value="A/L" <?php echo (isset($profile['category']) && $profile['category'] == 'A/L') ? 'checked' : ''; ?> required>
            <label for="al">A/L</label>

            <input type="radio" id="course" name="al_or_course" value="Course" <?php echo (isset($profile['category']) && $profile['category'] == 'Course') ? 'checked' : ''; ?> required>
            <label for="course">Course</label>
        </div>

            <!-- Stream options -->
         <div id="streams-section" class="<?php echo (isset($profile['category']) && $profile['category'] == 'A/L') ? '' : 'hidden'; ?>">
            <p>Subject Stream</p>
            <select name="subject" class="box">
               <option value="">Select Stream</option>
               <option value="Science" <?php echo isset($profile['subject']) && $profile['subject'] == 'Science' ? 'selected' : ''; ?>>Science</option>
               <option value="Maths" <?php echo isset($profile['subject']) && $profile['subject'] == 'Maths' ? 'selected' : ''; ?>>Maths</option>
               <option value="Commerce" <?php echo isset($profile['subject']) && $profile['subject'] == 'Commerce' ? 'selected' : ''; ?>>Commerce</option>
               <option value="Arts" <?php echo isset($profile['subject']) && $profile['subject'] == 'Arts' ? 'selected' : ''; ?>>Arts</option>
               <option value="Technology" <?php echo isset($profile['subject']) && $profile['subject'] == 'Technology' ? 'selected' : ''; ?>>Technology</option>
            </select>
         </div>

         <!-- Course options -->
         <div id="courses-section" class="<?php echo (isset($profile['category']) && $profile['category'] == 'Course') ? '' : 'hidden'; ?>">
            <p>Course</p>
            <select name="course" class="box">
               <option value="">Select Course</option>
               <option value="Course1" <?php echo isset($profile['course']) && $profile['course'] == ' Law College Entrance Exam' ? 'selected' : ''; ?>> Law College Entrance Exam               </option>
               <option value="Course2" <?php echo isset($profile['course']) && $profile['course'] == ' Open Competitive Examination for recruitment of Customs⁠' ? 'selected' : ''; ?>> Open Competitive Examination for recruitment of Customs⁠               </option>
               <option value="Course3" <?php echo isset($profile['course']) && $profile['course'] == '⁠Virtual Entry Assessment (VEA)' ? 'selected' : ''; ?>> ⁠Virtual Entry Assessment (VEA)</option>
            </select>
         </div>



         <p>Workplace</p>
      <input type="text" name="workplace" placeholder="Enter Your workplace" value="<?php echo isset($profile["workplace"]) ? htmlspecialchars($profile["workplace"]) : "" ?>" maxlength="100" class="box" required>
      <p>Qualification</p>
      <input type="text" name="qualification" placeholder="Enter Your qualification" value="<?php echo isset($profile["qualification"]) ? htmlspecialchars($profile["qualification"]) : "" ?>" maxlength="100" class="box" required>
      <p>About</p>
      <input type="text" name="about" placeholder="Enter Your about" value="<?php echo isset($profile["about"]) ? htmlspecialchars($profile["about"]) : "" ?>" maxlength="100" class="box" required>
      <p>Update Profile Pic</p>
        <input type="file" name="profile_pic" accept="image/*" class="box">

        <button type="submit" name="submit" class="btn">Update Profile</button>
    </form>
</section>

<!-- Modal structure -->
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

<!-- custom js file link  -->
<script src="js/script.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var alRadio = document.getElementById('al');
        var courseRadio = document.getElementById('course');
        var streamsSection = document.getElementById('streams-section');
        var coursesSection = document.getElementById('courses-section');

        // Event listeners to toggle visibility
        alRadio.addEventListener('change', function() {
            if (alRadio.checked) {
                streamsSection.style.display = 'block';
                coursesSection.style.display = 'none';
            }
        });

        courseRadio.addEventListener('change', function() {
            if (courseRadio.checked) {
                streamsSection.style.display = 'none';
                coursesSection.style.display = 'block';
            }
        });

        // Initial state based on the selected radio button
        if (alRadio.checked) {
            streamsSection.style.display = 'block';
            coursesSection.style.display = 'none';
        } else if (courseRadio.checked) {
            streamsSection.style.display = 'none';
            coursesSection.style.display = 'block';
        }
    });

    // Get modal element
    var modal = document.getElementById("logoutModal");
    var logoutBtn = document.getElementById("logout-btn");
    var closeBtn = document.getElementsByClassName("close")[0];
    var cancelBtn = document.getElementById("cancel-logout");
    var confirmLogoutBtn = document.getElementById("confirm-logout");

    // Show modal when logout button is clicked
    logoutBtn.onclick = function(event) {
        event.preventDefault(); // Prevent default action (navigation)
        modal.style.display = "block";
    }

    // Hide modal when close button is clicked
    closeBtn.onclick = function() {
        modal.style.display = "none";
    }

    // Hide modal when cancel button is clicked
    cancelBtn.onclick = function() {
        modal.style.display = "none";
    }

    // Handle logout confirmation
    confirmLogoutBtn.onclick = function() {
        window.location.href = "/vv/otp/login.php"; // Redirect to the logout script
    }

    // Hide modal when clicking outside of the modal content
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
    var alRadio = document.getElementById('al');
    var courseRadio = document.getElementById('course');
    var streamsSection = document.getElementById('streams-section');
    var coursesSection = document.getElementById('courses-section');

    // Event listeners to toggle visibility
    alRadio.addEventListener('change', function() {
        if (alRadio.checked) {
            streamsSection.style.display = 'block';
            coursesSection.style.display = 'none';
        }
    });

    courseRadio.addEventListener('change', function() {
        if (courseRadio.checked) {
            streamsSection.style.display = 'none';
            coursesSection.style.display = 'block';
        }
    });

    // Initial state based on the selected radio button
    if (alRadio.checked) {
        streamsSection.style.display = 'block';
        coursesSection.style.display = 'none';
    } else if (courseRadio.checked) {
        streamsSection.style.display = 'none';
        coursesSection.style.display = 'block';
    }
});

</script>

</body>
</html>