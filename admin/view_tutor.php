<?php
include 'db.php'; // Include the database connection
include 'check_role.php';

$nic = $_SESSION["nic"]; // Use session NIC

if (isset($_GET['nic'])) {
    // Sanitize NIC from the URL
    $nic = mysqli_real_escape_string($conn, $_GET['nic']);

    // Fetch user details for the selected NIC
    $studentResult = mysqli_query($conn, "SELECT nic, address, email, mobile, subject, workplace, qualification, about FROM tutor WHERE nic = '{$nic}'");

    if (!$studentResult) {
        die("Query failed: " . mysqli_error($conn));
    }

    $studentRow = mysqli_fetch_assoc($studentResult); // Fetch user details as an associative array

    // Fetch the count of students registered for classes of the selected tutor
    $studentCountResult = mysqli_query($conn, "
        SELECT COUNT(s.nic) AS student_count
        FROM student_classes s
        LEFT JOIN classes c ON s.class_id = c.id
        WHERE c.tutor_id = '{$nic}'
    ");

    if (!$studentCountResult) {
        die("Query failed: " . mysqli_error($conn));
    }

    $studentCountRow = mysqli_fetch_assoc($studentCountResult); // Fetch student count
    $studentCount = $studentCountRow['student_count'];

} else {
    $studentRow = null; // No data if NIC is not set
    $studentCount = 0; // Default student count if NIC is not set
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Dashboard</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      /* Increase font size for section titles */
      h2.centered-title {
         text-align: center;
         font-size: 2.2rem; /* Increased font size */
         margin-bottom: 20px;
         color: #333; /* Title color */
         font-weight: bold; /* Make text bold */
         text-transform: uppercase; /* Make text uppercase */
      }

      /* Box styling */
      .box {
         border: 1px solid #ddd; /* Light gray border */
         border-radius: 8px; /* Rounded corners */
         padding: 20px; /* Padding inside the box */
         margin-bottom: 20px; /* Space below each box */
         box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Light shadow for depth */
         background-color: #fff; /* White background */
      }

      .box table {
         width: 100%; /* Full width tables */
         border-collapse: collapse; /* Collapse borders for cleaner look */
      }

      .box th, .box td {
         padding: 10px; /* Padding inside table cells */
         text-align: left; /* Left-align text */
         border-bottom: 1px solid #ddd; /* Border between rows */
      }

      .box th {
         font-weight: bold; /* Bold text for headers */
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

<section class="user-profile">
   <h1 class="heading">Tutor Management</h1>
   
   <!-- Tutor Details Box -->
   <div class="box student-details-box">
       <h2 class="centered-title">Tutor Details</h2>
       <table class="table">
           <thead>
               <tr>
                   <th>NIC</th>
                   <th>Address</th>
                   <th>Email</th>
                   <th>Mobile</th>
                   <th>Subject</th>
                   <th>Workplace</th>
                   <th>Qualification</th>
                   <th>About</th>
               </tr>
           </thead>
           <tbody>
               <?php if ($studentRow): ?>
               <tr>
                   <td><?php echo $studentRow['nic']; ?></td>
                   <td><?php echo $studentRow['address']; ?></td>
                   <td><?php echo $studentRow['email']; ?></td>
                   <td><?php echo $studentRow['mobile']; ?></td>
                   <td><?php echo $studentRow['subject']; ?></td>
                   <td><?php echo $studentRow['workplace']; ?></td>
                   <td><?php echo $studentRow['qualification']; ?></td>
                   <td><?php echo $studentRow['about']; ?></td>
               </tr>
               <?php else: ?>
               <tr>
                   <td colspan="8">No tutor details found</td>
               </tr>
               <?php endif; ?>
           </tbody>
       </table>
   </div>

   <!-- Display Student Count -->
   <div class="box student-count-box">
       <p style="text-align: center; font-size: 2rem; font-weight: bold; color: #333;">
           Total students registered under the tutor: <strong style="font-size: 2.5rem; color: #007bff;"><?php echo $studentCount; ?></strong>
       </p>
   </div>

   <!-- Modal structure for logout -->
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
