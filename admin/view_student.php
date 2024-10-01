<?php
include 'db.php'; // Include the database connection
include 'check_role.php';

$nic = $_SESSION["nic"]; // Use session NIC

if (isset($_GET['nic'])) {
    // Sanitize NIC from the URL
    $nic = mysqli_real_escape_string($conn, $_GET['nic']);

    // Fetch user details for the selected NIC
    $studentResult = mysqli_query($conn, "SELECT nic, email, school, mobile, stream, address FROM update_profile WHERE nic = '{$nic}'");

    if (!$studentResult) {
        die("Query failed: " . mysqli_error($conn));
    }

    $studentRow = mysqli_fetch_assoc($studentResult); // Fetch user details as an associative array

    // Fetch registered classes and tutors for the selected NIC, including the registered date
    $classTutorResult = mysqli_query($conn, "
        SELECT c.id AS class_id, c.name AS class_name, c.tutor_name, s.registration_date
        FROM student_classes s
        LEFT JOIN classes c ON s.class_id = c.id 
        WHERE s.nic = '{$nic}'
    ");

    if (!$classTutorResult) {
        die("Query failed: " . mysqli_error($conn));
    }

    // Fetch registered courses and tutors for the selected NIC, including the registered date
    $courseTutorResult = mysqli_query($conn, "
        SELECT cr.id AS course_id, cr.name AS course_name, cr.tutor_name, sc.registration_date
        FROM student_courses sc
        LEFT JOIN courses cr ON sc.course_id = cr.id
        WHERE sc.nic = '{$nic}'
    ");

    if (!$courseTutorResult) {
        die("Query failed: " . mysqli_error($conn));
    }

    // Check if there are any rows for classes or courses
    $hasClasses = mysqli_num_rows($classTutorResult) > 0;
    $hasCourses = mysqli_num_rows($courseTutorResult) > 0;

} else {
    $studentRow = null; // No data if NIC is not set
    $classTutorResult = null;
    $courseTutorResult = null;
    $hasClasses = false;
    $hasCourses = false;
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
   <h1 class="heading">Student Management</h1>
   
   <div class="box-container">
      <!-- Student Details Box -->
      <div class="box student-details-box">
         <h2 class="centered-title">Student Details</h2>
         <table class="table">
            <thead>
                <tr>
                    <th>NIC</th>
                    <th>Email</th>
                    <th>School</th>
                    <th>Mobile</th>
                    <th>Stream</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($studentRow): ?>
                <tr>
                    <td><?php echo $studentRow['nic']; ?></td>
                    <td><?php echo $studentRow['email']; ?></td>
                    <td><?php echo $studentRow['school']; ?></td>
                    <td><?php echo $studentRow['mobile']; ?></td>
                    <td><?php echo $studentRow['stream']; ?></td>
                    <td><?php echo $studentRow['address']; ?></td>
                </tr>
                <?php else: ?>
                <tr>
                    <td colspan="6">No student details found</td>
                </tr>
                <?php endif; ?>
            </tbody>
         </table>
      </div>

      <!-- Registered Classes Box (Only show if there are classes) -->
      <?php if ($hasClasses): ?>
      <div class="box registered-classes-box">
         <h2 class="centered-title">Registered Classes</h2>
         <table class="table">
            <thead>
                <tr>
                    <th>Class ID</th>
                    <th>Class Name</th>
                    <th>Tutor Name</th>
                    <th>Registered Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($classRow = mysqli_fetch_assoc($classTutorResult)): ?>
                <tr>
                    <td><?php echo $classRow['class_id']; ?></td>
                    <td><?php echo $classRow['class_name']; ?></td>
                    <td><?php echo $classRow['tutor_name']; ?></td>
                    <td><?php echo $classRow['registration_date']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
         </table>
      </div>
      <?php endif; ?>

      <!-- Registered Courses Box (Only show if there are courses) -->
      <?php if ($hasCourses): ?>
      <div class="box registered-courses-box">
         <h2 class="centered-title">Registered Courses</h2>
         <table class="table">
            <thead>
                <tr>
                    <th>Course ID</th>
                    <th>Course Name</th>
                    <th>Tutor Name</th>
                    <th>Registered Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($courseRow = mysqli_fetch_assoc($courseTutorResult)): ?>
                <tr>
                    <td><?php echo $courseRow['course_id']; ?></td>
                    <td><?php echo $courseRow['course_name']; ?></td>
                    <td><?php echo $courseRow['tutor_name']; ?></td>
                    <td><?php echo $courseRow['registration_date']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
         </table>
      </div>
      <?php endif; ?>

   </div>
</section>

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
