<?php
include 'db.php'; // Include the database connection
include 'check_role.php';

$tutor_nic = $_SESSION["nic"]; // Use session NIC for the tutor

// Check if course_id is set in the URL
if (isset($_GET['id'])) {
    $course_id = $_GET['id']; // Get course_id from the URL

    // Use prepared statements to prevent SQL injection
    // Query to get student NICs from the selected course
    $studentNICQuery = $conn->prepare("SELECT nic FROM student_courses WHERE course_id = ?");
    $studentNICQuery->bind_param("i", $course_id);
    $studentNICQuery->execute();
    $studentNICResult = $studentNICQuery->get_result();

    if ($studentNICResult->num_rows > 0) {
        $students = [];
        while ($row = $studentNICResult->fetch_assoc()) {
            $nic = $row['student_nic'];

            // Fetch the student name using NIC from user_reg table
            $studentNameQuery = $conn->prepare("SELECT name FROM user_reg WHERE nic = ?");
            $studentNameQuery->bind_param("s", $nic);
            $studentNameQuery->execute();
            $studentNameResult = $studentNameQuery->get_result();

            if ($studentNameResult->num_rows > 0) {
                $studentNameRow = $studentNameResult->fetch_assoc();
                $students[] = [
                    'nic' => $nic,
                    'name' => $studentNameRow['name']
                ];
            }
        }
        $studentCount = count($students); // Total count of students
    } else {
        // No students found
        $students = [];
        $studentCount = 0;
    }
} else {
    // If no course ID is provided in the URL
    $students = [];
    $studentCount = 0;
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
   <style>
      h2.centered-title {
         text-align: center;
         font-size: 2.2rem;
         margin-bottom: 20px;
         color: #333;
         font-weight: bold;
         text-transform: uppercase;
      }

      .box {
         border: 1px solid #ddd;
         border-radius: 8px;
         padding: 20px;
         margin-bottom: 20px;
         box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
         background-color: #fff;
      }

      .box table {
         width: 100%;
         border-collapse: collapse;
      }

      .box th, .box td {
         padding: 10px;
         text-align: left;
         border-bottom: 1px solid #ddd;
      }

      .box th {
         font-weight: bold;
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

   <!-- Student Count Box -->
   <div class="box student-count-box">
       <p style="text-align: center; font-size: 2rem; font-weight: bold; color: #333;">
           Total students registered under the class: <strong style="font-size: 2.5rem; color: #007bff;"><?php echo $studentCount; ?></strong>
       </p>
   </div>

   <!-- Students Details Box -->
   <div class="box student-details-box">
       <h2 class="centered-title">Student Details</h2>
       <table class="table">
           <thead>
               <tr>
                   <th>NIC</th>
                   <th>Name</th>
               </tr>
           </thead>
           <tbody>
               <?php if (!empty($students)): ?>
                   <?php foreach ($students as $student): ?>
                       <tr>
                           <td><?php echo htmlspecialchars($student['nic']); ?></td>
                           <td><?php echo htmlspecialchars($student['name']); ?></td>
                       </tr>
                   <?php endforeach; ?>
               <?php else: ?>
                   <tr>
                       <td colspan="2">No students found</td> 
                   </tr>
               <?php endif; ?>
           </tbody>
       </table>
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

</section>
</body>
</html>
