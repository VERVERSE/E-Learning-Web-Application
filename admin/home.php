<?php 
include 'db.php';
include 'check_role.php';

// Fetch the number of tutors
$tutors_result = mysqli_query($conn, "SELECT COUNT(*) AS total_tutors FROM tutor");
$tutors_count = mysqli_fetch_assoc($tutors_result)['total_tutors'];

// Fetch the number of students
$students_result = mysqli_query($conn, "SELECT COUNT(*) AS total_students FROM update_profile");
$students_count = mysqli_fetch_assoc($students_result)['total_students'];

// Fetch the number of classes
$classes_result = mysqli_query($conn, "SELECT COUNT(*) AS total_classes FROM classes");
$classes_count = mysqli_fetch_assoc($classes_result)['total_classes'];

// Fetch the number of courses
$courses_result = mysqli_query($conn, "SELECT COUNT(*) AS total_courses FROM courses");
$courses_count = mysqli_fetch_assoc($courses_result)['total_courses'];

// Fetch help messages from help_message table where action is 'Question'
$help_message_query = "SELECT * FROM help_message WHERE action = 'Question'";
$help_message_result = mysqli_query($conn, $help_message_query);

// Fetch notices from the notice_board table
$notice_query = "SELECT * FROM notice";
$notice_result = mysqli_query($conn, $notice_query);

// Check if the query was successful
if (!$notice_result) {
    die("Database query failed: " . mysqli_error($conn));
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_message'])) {
    $reply_message = mysqli_real_escape_string($conn, $_POST['reply']);
    $message_id = $_POST['message_id'];

    // Fetch the nic from the original help_message
    $nic_query = "SELECT nic FROM help_message WHERE help_sid = '$message_id'";
    $nic_result = mysqli_query($conn, $nic_query);
    $nic_row = mysqli_fetch_assoc($nic_result);
    $nic = $nic_row['nic'];

    // Insert reply into the help_message table
    $reply_query = "INSERT INTO help_message (nic, name, email, mobile, message, role, action) 
                    SELECT nic, name, email, mobile, '$reply_message', role, 'reply' FROM help_message WHERE help_sid = '$message_id'";
    mysqli_query($conn, $reply_query);

    // Redirect to avoid resubmission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Handle notice submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_notice'])) {
    $notice_from = 'Admin'; // Since it's from the admin
    $notice_to = mysqli_real_escape_string($conn, $_POST['notice_to']);
    $notice_content = mysqli_real_escape_string($conn, $_POST['notice_content']);
    $notice_title = mysqli_real_escape_string($conn, $_POST['notice_title']); // Get the title

    // Insert notice into the notice table
    $notice_query = "INSERT INTO notice (notice_from, notice_to, notice, notice_title) VALUES ('$notice_from', '$notice_to', '$notice_content', '$notice_title')";
    mysqli_query($conn, $notice_query);

    // Redirect to avoid resubmission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Dashboard</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/css.css">
   <style>
   .content-grid {
      display: flex;
      justify-content: space-between;
      gap: 20px;
      margin-top: 30px;
   }

   .left-section {
      flex: 0.5; /* Adjusted to make it larger */
   }

   .right-section {
      flex: 0.5; /* Adjusted to make it smaller */
   }

   .messages-box, .post-notice, .notice-board {
      background-color: #f9f9f9;
      border-radius: 10px;
      padding: 15px;
      border: 1px solid #ccc;
      margin-bottom: 20px;
   }

   .message-box {
      border: 1px solid #ccc;
      padding: 10px; /* Made padding smaller */
      border-radius: 5px;
      background-color: #ffffff;
      margin-bottom: 10px;
   }

   .reply-box.admin-reply-box {
      background-color: #e1f7d5; /* Light green background */
      border: 1px solid #a8e6cf; /* Border color */
      padding: 10px;
      border-radius: 5px;
      margin-top: 10px; /* Add some spacing from the previous element */
   }

   .message-box h3 {
      font-size: 1.1em;
      margin-bottom: 5px; /* Reduced margin */
   }

   .heading {
      font-size: 1.5em;
      margin-bottom: 15px;
   }

   .reply-form textarea {
      width: 100%;
      padding: 5px; /* Made padding smaller */
      border-radius: 5px;
      border: 1px solid #ccc;
      resize: none;
   }

   .reply-form button {
      margin-top: 5px; /* Reduced margin */
      padding: 5px 10px; /* Made padding smaller */
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
   }

   .reply-form button:hover {
      background-color: #0056b3;
   }
   .reply-box.admin-reply-box {
      background-color: #d1ecf1; /* Light blue background */
      border: 1px solid #bee5eb; /* Border color */
      padding: 10px;
      border-radius: 5px;
      margin-top: 10px; /* Add some spacing from the previous element */
   }

   .notice-form input, .notice-form textarea {
      width: 100%;
      padding: 8px;
      margin-bottom: 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
   }

   .notice-form button {
      background-color: #28a745;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
   }

   .notice-form button:hover {
      background-color: #218838;
   }
</style>

</head>
<body>
<header class="header">
   <section class="flex">
      <a href="home.html" class="logo">VERVERSE</a>
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
      <img src="images/pic-1.jpg" class="image" alt="">
      <h3 class="name"><?php echo $_SESSION['name']; ?></h3>
      <p class="role"><?php echo $_SESSION['role']; ?></p>
   </div>
   <nav class="navbar">
      <a href="home.php"><i class="fas fa-home"></i><span>HOME</span></a>
      <a href="studentM.php"><i class="fas fa-chalkboard-user"></i><span>STUDENT </span></a>
      <a href="tutorM.php"><i class="fas fa-chalkboard-user"></i><span>TUTOR </span></a>
      <a href="classes.php"><i class="fas fa-graduation-cap"></i><span>A/L CLASSES</span></a>
      <a href="courses.php"><i class="fas fa-graduation-cap"></i><span>OTHER COURSES</span></a>
      <a href="verify.php"><i class="fas fa-graduation-cap"></i><span>ACCOUNT VERIFICATION</span></a>
   </nav>
</div>

<section class="home-grid">
   <h1 class="heading">Dashboard</h1>
   <div class="stats">
      <div class="stat-item">
         <i class="fas fa-chalkboard-user"></i>
         <h3><?php echo $tutors_count; ?></h3>
         <p>Total Tutors</p>
      </div>
      <div class="stat-item">
         <i class="fas fa-user-graduate"></i>
         <h3><?php echo $students_count; ?></h3>
         <p>Total Students</p>
      </div>
      <div class="stat-item">
         <i class="fas fa-graduation-cap"></i>
         <h3><?php echo $classes_count; ?></h3>
         <p>Total Classes</p>
      </div>
      <div class="stat-item">
         <i class="fas fa-book"></i>
         <h3><?php echo $courses_count; ?></h3>
         <p>Total Courses</p>
      </div>
   </div>
</section>

<div class="content-grid">
   <div class="left-section">
      <div class="messages-box">
          <h2 class="heading">Messages</h2>

          <?php while ($help_message_row = mysqli_fetch_assoc($help_message_result)): ?>
              <div class="message-box">
                  <h3><?php echo htmlspecialchars($help_message_row['role']); ?>: <?php echo htmlspecialchars($help_message_row['name']); ?></h3>
                  <p>Email: <?php echo htmlspecialchars($help_message_row['email']); ?></p>
                  <p>Mobile: <?php echo htmlspecialchars($help_message_row['mobile']); ?></p>
                  <p>Message: <?php echo htmlspecialchars($help_message_row['message']); ?></p>

                  <!-- Fetch all replies for this specific question -->
                  <?php
                  $message_id = $help_message_row['help_sid'];
                  $reply_query = "SELECT * FROM help_message WHERE action = 'reply' AND nic = '{$help_message_row['nic']}'";
                  $reply_result = mysqli_query($conn, $reply_query);
                  ?>

                  <div class="reply-box admin-reply-box">
                     <h4>Admin Replies:</h4>
                     <?php if (mysqli_num_rows($reply_result) > 0): ?>
                        <?php while ($reply_row = mysqli_fetch_assoc($reply_result)): ?>
                              <p><?php echo htmlspecialchars($reply_row['message']); ?></p>
                        <?php endwhile; ?>
                     <?php else: ?>
                        <p>No replies yet.</p>
                     <?php endif; ?>
                  </div>

                  <!-- Reply form for the admin -->
                  <form class="reply-form" id="reply-form-<?php echo $help_message_row['help_sid']; ?>" action="" method="post">
                      <textarea name="reply" rows="2" placeholder="Reply..."></textarea>
                      <input type="hidden" name="message_id" value="<?php echo $help_message_row['help_sid']; ?>">
                      <button type="submit" name="reply_message">Send Reply</button>
                  </form>
              </div>
          <?php endwhile; ?>
      </div>
   </div>

   <div class="right-section">
      <section class="post-notice">
         <h2 class="heading">Post Notice</h2>
         <form action="" method="post" class="notice-form">
            <label for="notice_to">To:</label>
            <input type="text" id="notice_to" name="notice_to" placeholder="Enter recipient (e.g., All, Students, Tutors)" required>
            
            <label for="notice_title">Notice Title:</label>
            <input type="text" id="notice_title" name="notice_title" placeholder="Enter notice title" required>
            
            <label for="notice_content">Notice:</label>
            <textarea id="notice_content" name="notice_content" rows="4" placeholder="Enter your notice" required></textarea>
            
            <button type="submit" name="submit_notice">Add Notice</button>
         </form>
      </section>

      <section class="notice-board">
         <h2 class="heading">Notice Board</h2>
         <?php while ($notice_row = mysqli_fetch_assoc($notice_result)): ?>
            <div class="message-box">
               <h3>Title: <?php echo htmlspecialchars($notice_row['notice_title']); ?></h3> <!-- Display title -->
               <h3>From: <?php echo htmlspecialchars($notice_row['notice_from']); ?></h3>
               <p>To: <?php echo htmlspecialchars($notice_row['notice_to']); ?></p>
               <p><?php echo htmlspecialchars($notice_row['notice']); ?></p><br>
               <small>Posted on: <?php echo date('F j, Y, g:i a', strtotime($notice_row['created_at'])); ?></small>
            </div>
         <?php endwhile; ?>
      </section>
   </div>
</div>


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

<script src="js/script.js"></script>

<script>
   var modal = document.getElementById("logoutModal");
   var logoutBtn = document.getElementById("logout-btn");
   var closeBtn = document.getElementsByClassName("close")[0];
   var cancelBtn = document.getElementById("cancel-logout");
   var confirmLogoutBtn = document.getElementById("confirm-logout");

   logoutBtn.onclick = function(event) {
      event.preventDefault();
      modal.style.display = "block";
   }

   closeBtn.onclick = function() {
      modal.style.display = "none";
   }

   cancelBtn.onclick = function() {
      modal.style.display = "none";
   }

   confirmLogoutBtn.onclick = function() {
      window.location.href = "/vv/otp/login.php";
   }

   window.onclick = function(event) {
      if (event.target == modal) {
         modal.style.display = "none";
      }
   }
</script>
</body>
</html>
