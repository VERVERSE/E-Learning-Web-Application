<?php
include 'db.php';
session_start();


$nic = $_SESSION["nic"];

// Retrieve profile data
$sql = mysqli_query($conn, "SELECT * FROM update_profile WHERE nic ='{$nic}'");

if (mysqli_num_rows($sql) > 0) {
    $profile = mysqli_fetch_assoc($sql);
} else {
    $profile = null;
}

if (isset($_SESSION['nic'])) {

   $courses = array();

   $sql = "SELECT * FROM classes";
   $result = $conn->query($sql);
   
   if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
         $courses[] = array(
            "id" => $row["id"],
            "name" => $row["name"],
            "tutor_name" => $row["tutor_name"],
            "time" => $row["time"],
            "date" => $row["date"],
            "image" => $row["image"],
         );
      }
    }

    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Student Dashboard</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">
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
         <h3 class="name"><?php echo $_SESSION['name'];?></h3>
         <p class="role"><?php echo $_SESSION['role'];?></p>
         <a href="profile.php" class="btn">View Profile</a>
      </div>
      <nav class="navbar">
      <a href="home.php"><i class="fas fa-home"></i><span>HOME</span></a>
      <a href="profile.php"><i class="fa-solid fa-user"></i><span>PROFILE</span></a>
      <a href="classes.php"><i class="fas fa-graduation-cap"></i><span>CLASSES</span></a>
      < <a href="calendar.php"><i class="fa-regular fa-calendar-days"></i><span>CALENDAR</span></a>
      <a href="help.php"><i class="fas fa-headset"></i><span>HELP</span></a>
   </nav>
   </div>

   <section class="courses">
      <h1 class="heading">Pursuing Courses</h1>
      <div class="course-container">
         <?php
            if (!empty($courses)) {
               foreach ($courses as $course) {
                  echo '<div class="course-box">
                        <h2>'.$course["name"].'</h2>
                        <p class="info-item"><span class="label">ID:</span> '.$course["id"].'</p>
                        <p class="info-item"><span class="label">Tutor Name:</span> '.$course["tutor_name"].'</p>
                        <p class="info-item"><span class="label">Time:</span> '.$course["time"].'</p>
                        <p class="info-item"><span class="label">Date:</span> '.$course["date"].'</p>
                        <i class="fas fa-book course-icon"></i>
                        <a href="join.html" class="join-btn">Join</a>
                     </div>';
               }
            } else {
               echo '<h2>No courses found<h2>';
            }

         ?>



      </div>
      <div class="notification-box">
         <h2>Notifications</h2>
         <ul>
            <li>Notification 1</li>
            <li>Notification 2</li>
            <li>Notification 3</li>
            <!-- Add more notifications as needed -->
         </ul>
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

   <!-- Custom JS file link -->
   <script src="js/script.js"></script>

   <script>
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
   </script>

</body>

</html>