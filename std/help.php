<?php
session_start();
include 'db.php';

// Retrieve the NIC from the session
$nic = $_SESSION["nic"];

// Retrieve profile data
$sql = mysqli_query($conn, "SELECT * FROM update_profile WHERE nic ='{$nic}'");

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

// Retrieve name and nic from the user_reg table
$user_sql = mysqli_query($conn, "SELECT name, nic, role FROM user_reg WHERE nic = '{$nic}'");
if (mysqli_num_rows($user_sql) > 0) {
    $user_data = mysqli_fetch_assoc($user_sql);
    $name = $user_data['name'];
    $nic = $user_data['nic'];
    $role = $user_data['role'];
} else {
    $error_message = "User not found.";
}

if (isset($_POST["submit"])) {
   $email = isset($_POST["email"]) ? $_POST["email"] : '';
   $mobile = isset($_POST["mobile"]) ? $_POST["mobile"] : ''; 
   $message = isset($_POST["message"]) ? $_POST["message"] : ''; 
   $action = 'Question'; // Setting action as 'Question' for tutor messages
   if (!empty($nic) && !empty($name) && !empty($email) && !empty($mobile) && !empty($message)) {
      $action = 'Question'; // Ensure the value is correctly set here
  
      // Log the value before binding to SQL for debugging
      error_log("Action value: " . $action);
      
      // Prepare the SQL statement
      $stmt = mysqli_prepare($conn, "INSERT INTO help_message (nic, name, role, email, mobile, message, action) VALUES (?, ?, ?, ?, ?, ?, ?)");
  
      // Bind parameters
      mysqli_stmt_bind_param($stmt, "sssssss", $nic, $name, $role, $email, $mobile, $message, $action);
  
      // Execute the statement
      if (mysqli_stmt_execute($stmt)) {
          // Close the statement
          mysqli_stmt_close($stmt);
  
          // Redirect to help.php
          header("Location: help.php");
          exit;
      } else {
          $error_message = "Error: " . mysqli_stmt_error($stmt);
          error_log($error_message); // Log SQL errors
      }
  } else {
      $error_message = "All fields are required.";
      error_log($error_message); // Log validation errors
  }
  
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Student Dashboard</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
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
      <h3 class="name"><?php echo htmlspecialchars($_SESSION['name']); ?></h3>
      <p class="role"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
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

<section class="contact">
   <div class="row">
      <form action="" method="post">
         <h3>get in touch</h3>
        
         <input type="email" placeholder="enter your email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" required maxlength="50" class="box">
         <input type="text" placeholder="enter your mobile number" name="mobile" required maxlength="50" class="box">
         <textarea name="message" class="box" placeholder="enter your message" required maxlength="1000" cols="30" rows="10"></textarea>
         <input type="submit" value="send message" class="inline-btn" name="submit">
      </form>
   </div>
   <div class="box-container">
      <div class="box">
         <i class="fas fa-phone"></i>
         <h3>phone number</h3>
         <a href="tel:0715858915">0715858915</a>
      </div>
      <div class="box">
         <i class="fas fa-envelope"></i>
         <h3>email address</h3>
         <a href="mailto:verveverse@hotmail.com">verveverse@hotmail.com</a>
      </div>
      <div class="box">
         <i class="fas fa-map-marker-alt"></i>
         <h3>office address</h3>
         <a href="#">Maharagama</a>
      </div>
   </div>
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
