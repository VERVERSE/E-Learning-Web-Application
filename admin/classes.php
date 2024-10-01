<?php
include 'db.php'; // Include the database connection
include 'check_role.php';

// Check if 'id' is set in POST request
$id = isset($_POST["id"]) ? $_POST["id"] : null;

// Handle class details retrieval
if ($id) {
    $class_sql = mysqli_query($conn, "SELECT name, tutor_name, stream, time, date FROM classes WHERE id = '{$id}'");

    if (mysqli_num_rows($class_sql) > 0) {
        // Get details from the classes table
        $profile = mysqli_fetch_assoc($class_sql);
    } else {
        // No matching class found
        $profile = null;
    }
}

// Fetch all classes for display with search functionality
$search_query = isset($_GET['search_box']) ? mysqli_real_escape_string($conn, $_GET['search_box']) : '';
$query = "SELECT id, name, tutor_name, stream, time, date FROM classes WHERE 1=1";

// Add search condition for class name, tutor name, or stream
if (!empty($search_query)) {
    $query .= " AND (name LIKE '%{$search_query}%' OR tutor_name LIKE '%{$search_query}%' OR stream LIKE '%{$search_query}%')";
}

$result = mysqli_query($conn, $query);

// Check for query error
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Deletion logic for classes
if (isset($_GET['id'])) {
    // Sanitize the class ID
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Delete class if exists
    $sql = "DELETE FROM classes WHERE id = '{$id}'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        // Redirect back to the classes page with a success message
        header("Location: classes.php?status=deleted");
        exit();
    } else {
        // Handle deletion failure
        echo "Error deleting record: " . mysqli_error($conn);
    }
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
            .btn-delete {
      
      color: #ff4d4d; /* White text */
      padding: 8px 12px; /* Some padding for better button size */
      border: none; /* No border */
      border-radius: 5px; /* Rounded corners */
      text-decoration: none; /* Remove underline from link */
      font-size: 14px;
      cursor: pointer;
      transition: background-color 0.3s ease;
   }

   .btn-delete:hover {
      color: #e60000; /* Darker red on hover */
   }
   .btn-view {
      color: #4d79ff; /* Blue background */
      
      padding: 8px 12px; /* Some padding for better button size */
      border: none; /* No border */
      border-radius: 5px; /* Rounded corners */
      text-decoration: none; /* Remove underline from link */
      font-size: 14px;
      cursor: pointer;
      transition: background-color 0.3s ease;
   }

   .btn-view:hover {
      color: #1a53ff; /* Darker blue on hover */
   }
   </style>

</head>
<body>

<header class="header">
   <section class="flex">
      <a href="home.html" class="logo">VERVERSE</a>
      <form action="classes.php" method="GET" class="search-form">
         <input type="text" name="search_box" required placeholder="Search classes, tutors, or streams..." maxlength="100" value="<?php echo htmlspecialchars($search_query); ?>">
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
   <h1 class="heading">Classes</h1>
   <div class="box-container">
      <section class="attendance">
         <div class="attendance-list">
            <table class="table">
               <thead>
                  <tr>
                     <th>Name</th>
                     <th>Tutor Name</th>
                     <th>Stream / Course</th>
                     <th>Time</th>
                     <th>Date</th>
                     <th>Action</th>
                  </tr>
               </thead>
               <tbody>
                  <?php
                  if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                          $id = $row['id'];
                          $stream_or_course = !empty($row['stream']) ? $row['stream'] : 'N/A';
                          echo "<tr>
                              <td>{$row['name']}</td>
                              <td>{$row['tutor_name']}</td>
                              <td>{$stream_or_course}</td>
                              <td>{$row['time']}</td>
                              <td>{$row['date']}</td>
                              <td>
                                  <a href='view_classes.php?id={$id}' class='btn-view'>View</a>
                                  <a href='classes.php?id={$id}' class='btn-delete' onclick='return confirmDelete();'>Delete</a>
                              </td>
                          </tr>";
                      }
                  } else {
                      echo "<tr><td colspan='6'>No records found</td></tr>";
                  }
                  ?>
               </tbody>
            </table>
         </div>
      </section>
</section>

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

   function confirmDelete() {
      return confirm("Are you sure you want to delete this class?");
   }
</script>
</body>
</html>
