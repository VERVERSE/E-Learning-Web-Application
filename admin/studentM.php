<?php
include 'db.php'; // Include the database connection
include 'check_role.php';

$nic = $_SESSION["nic"];

// Check for NIC deletion request
if (isset($_GET['nic'])) {
    // Sanitize the NIC input  
    $nic = mysqli_real_escape_string($conn, $_GET['nic']);
    
    // Delete related records in the update_profile table
    mysqli_query($conn, "DELETE FROM update_profile WHERE nic = '{$nic}'");
    
    // Delete the record in the user_reg table
    $delete = mysqli_query($conn, "DELETE FROM user_reg WHERE nic = '{$nic}'");

    if ($delete) {
        // Redirect to the same page to reflect changes
        header("Location: studentM.php");
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}

// Fetch user profile data
$sql = mysqli_query($conn, "SELECT nic, name, email FROM user_reg WHERE nic ='{$nic}'");

if (mysqli_num_rows($sql) > 0) {
    $profile = mysqli_fetch_assoc($sql);
} else {
    $profile = null;
}

// Fetch all users for display with search functionality
$search_query = isset($_GET['search_nic']) ? mysqli_real_escape_string($conn, $_GET['search_nic']) : '';
$query = "SELECT * FROM user_reg WHERE role = 'student'";

// If search query is present, add to the SQL query for both NIC and name
if (!empty($search_query)) {
    $query .= " AND (nic LIKE '%{$search_query}%' OR name LIKE '%{$search_query}%')";
}

$result = mysqli_query($conn, $query);

// Check for query error
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
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

      <form action="studentM.php" method="GET" class="search-form">
         <input type="text" name="search_nic" required placeholder="Search by NIC or Name..." maxlength="50" value="<?php echo htmlspecialchars($search_query); ?>">
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
   <section class="attendance">
      <div class="attendance-list">
         <table class="table">
         <thead>
             <tr>
                 <th>Name</th>
                 <th>NIC</th>
                 <th>Email</th>
                 <th>Action</th>
             </tr>
         </thead>
         <tbody>
             <?php
             if ($result->num_rows > 0) {
                 while ($row = $result->fetch_assoc()) {
                     $nic = $row['nic'];
                     echo "<tr>
                         <td>{$row['name']}</td>
                         <td>{$row['nic']}</td>
                         <td>{$row['email']}</td>
                         <td>
                             <a href='view_student.php?nic={$nic}' class='btn-view'>View</a>
                             <a href='studentM.php?nic={$nic}' class='btn-delete' onclick='return confirm(\"Are you sure you want to delete this student?\");'>Delete</a>
                         </td>
                     </tr>";
                 }
             } else {
                 echo "<tr><td colspan='4'>No records found</td></tr>";
             }
             ?>
         </tbody>
         </table>
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
