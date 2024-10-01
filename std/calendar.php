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

<section class="home-grid">

   <h1 class="heading">CALENDAR</h1>

   <div class="box-container">
   <iframe src="/vv/std/schedule" width="100%" height="500px" frameborder="0"></iframe>
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



<!-- <footer class="footer">

   &copy; copyright @ 2022 by <span>mr. web designer</span> | all rights reserved!

</footer> -->

<!-- custom js file link  -->
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