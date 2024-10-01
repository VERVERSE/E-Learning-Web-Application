<?php session_start(); 

include 'db.php';

// Retrieve the NIC from the session
$nic = $_SESSION["nic"];

// Retrieve profile data
$sql = mysqli_query($conn, "SELECT * FROM tutor WHERE nic ='{$nic}'");

if (mysqli_num_rows($sql) > 0) {
    $profile = mysqli_fetch_assoc($sql);
} else {
    $profile = null;
}

// Retrieve messages from the help_message table where NIC matches the logged-in user
$messageSql = mysqli_query($conn, "SELECT * FROM help_message WHERE nic ='{$nic}'");
$messages = [];
if (mysqli_num_rows($messageSql) > 0) {
    while ($row = mysqli_fetch_assoc($messageSql)) {
        $messages[] = $row;
    }
}

// Determine if profile is incomplete
$profileIncomplete = false;
if ($profile) {
    // Check if essential fields are missing
    if (empty($profile['address']) || empty($profile['email']) || empty($profile['mobile']) || empty($profile['workplace']) || empty($profile['qualification']) || empty($profile['about'])) {
        $profileIncomplete = true;
    }
}

// Retrieve notices specifically for students and tutors
$noticeSql = mysqli_query($conn, "SELECT * FROM notice WHERE notice_to IN ('Tutor', 'All', 'All Students and Tutors') ORDER BY created_at DESC");

if (!$noticeSql) {
    die("Query failed: " . mysqli_error($conn));
}

$notices = [];
if (mysqli_num_rows($noticeSql) > 0) {
    while ($row = mysqli_fetch_assoc($noticeSql)) {
        $notices[] = $row;
    }
}

// Handle notice submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_notice'])) {
    $notice_from = 'Tutor'; // Since it's from the tutor
    $notice_to = mysqli_real_escape_string($conn, $_POST['notice_to']);
    $notice_content = mysqli_real_escape_string($conn, $_POST['notice_content']);
    $notice_title = mysqli_real_escape_string($conn, $_POST['notice_title']); // Get the title

    // Insert notice into the notice table
    $notice_query = "INSERT INTO notice (notice_from, notice_to, notice, notice_title) VALUES ('$notice_from', '$notice_to', '$notice_content', '$notice_title')";
    mysqli_query($conn, $notice_query);

    // Redirect to avoid resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Retrieve upcoming classes or courses based on tutor category
$category = $profile['category']; // Assuming the 'category' column indicates A/L or Course

$upcomingEvents = [];
if ($category === 'A/L') {
    // Retrieve A/L classes from the classes table
    $classesSql = mysqli_query($conn, "SELECT * FROM classes WHERE tutor_id = '{$nic}' AND date >= NOW() ORDER BY date ASC");
    if (mysqli_num_rows($classesSql) > 0) {
        while ($row = mysqli_fetch_assoc($classesSql)) {
            $upcomingEvents[] = $row;
        }
    }
} elseif ($category === 'Course') {
    // Retrieve courses from the courses table
    $coursesSql = mysqli_query($conn, "SELECT * FROM courses WHERE tutor_id = '{$nic}' AND date >= NOW() ORDER BY date ASC");
    if (mysqli_num_rows($coursesSql) > 0) {
        while ($row = mysqli_fetch_assoc($coursesSql)) {
            $upcomingEvents[] = $row;
        }
    }
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
   <link rel="stylesheet" href="css/home.css">

   <style>
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
       .btn-update {
           background-color: #4CAF50; /* Green */
           color: white;
           padding: 12px 24px; /* Increased padding for better aesthetics */
           border: none;
           border-radius: 5px;
           cursor: pointer;
           text-decoration: none;
           margin-right: 10px;
           font-size: 14px; /* Increased font size */
           font-weight: bold; /* Optional: Makes the text bold */
       }

       .btn-update:hover {
           background-color: #45a049;
       }

       /* Close Button */
       .close-update:hover,
       .close-update:focus {
           color: black;
           text-decoration: none;
           cursor: pointer;
       }
       
       .btn-close-update {
           background-color: #f44336; /* Red */
           color: white;
           padding: 10px 20px;
           border: none;
           border-radius: 5px;
           cursor: pointer;
       }

       .btn-close-update:hover {
           background-color: #e53935;
       }

       /* Fade-in Animation */
       @keyframes fadeIn {
           from { opacity: 0; }
           to { opacity: 1; }
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
      <h3 class="name"><?php echo $_SESSION['name']; ?></h3>
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

<section class="home-grid">
   <div class="container">
      <div class="welcome-back">
          <img src="images/pencile.png" alt="Welcome Image">
          <h2>Welcome Back, <?php echo $_SESSION['name']; ?>!</h2>
      </div>
      
      <div class="row">
          <div class="box notice-board">
              <h3>Notice Board</h3>
              <ul>
                  <?php if (!empty($notices)): ?>
                      <?php foreach ($notices as $notice): ?>
                          <li>
                              <strong><?php echo htmlspecialchars($notice['notice_title']); ?>:</strong>
                              <strong>From: <?php echo htmlspecialchars($notice['notice_from']); ?><br></strong>
                              <?php echo htmlspecialchars($notice['notice']); ?><br>
                              <small>Posted on: <?php echo date('F j, Y, g:i a', strtotime($notice['created_at'])); ?></small>
                          </li>
                      <?php endforeach; ?>
                  <?php else: ?>
                      <li>No notices found.</li>
                  <?php endif; ?>
              </ul>
          </div>

          <div class="box post-notice">
              <h3>Post a Notice</h3>
              
              <form action="" method="post" class="notice-form">
            <label for="notice_to">To:</label>
            <input type="text" id="notice_to" name="notice_to" placeholder="Enter recipient (e.g., All, Students, Tutors)" required>
            
            <label for="notice_title">Notice Title:</label>
            <input type="text" id="notice_title" name="notice_title" placeholder="Enter notice title" required>
            
            <label for="notice_content">Notice:</label>
            <textarea id="notice_content" name="notice_content" rows="4" placeholder="Enter your notice" required></textarea>
            
            <button type="submit" name="submit_notice">Add Notice</button>
          </form>
              
          </div>

          <div class="box upcoming-events">
              <h3>Upcoming Classes</h3>
              <ul>
                  <?php if (!empty($upcomingEvents)): ?>
                      <?php foreach ($upcomingEvents as $event): ?>
                          <li>
                              <strong><?php echo ($category === 'A/L') ? "Class" : "Course"; ?>:</strong> <?php echo htmlspecialchars($event['name']); ?><br>
                              <strong>Date:</strong> <?php echo htmlspecialchars($event['date'] ?? $event['date']); ?><br>
                              <strong>Time:</strong> <?php echo htmlspecialchars($event['time']); ?>
                          </li>
                      <?php endforeach; ?>
                  <?php else: ?>
                      <li>No upcoming events available</li>
                  <?php endif; ?>
              </ul>
          </div>
      

          <div class="box messages">
               <h3>Messages</h3>
               <ul>
                  <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $message): ?>
                           <li>
                              <?php
                              // Check the action of the message
                              if (isset($message['action'])) {
                                    if ($message['action'] === 'Question'): ?>
                                       <strong>Question:</strong> <?php echo htmlspecialchars($message['message'] ?? ''); ?>
                                    <?php elseif ($message['action'] === 'reply'): ?>
                                       <strong>From Admin:</strong> <?php echo htmlspecialchars($message['message'] ?? ''); ?>
                                    <?php else: ?>
                                       <strong>Unknown Message:</strong> No content available.
                                    <?php endif;
                              } else { ?>
                                    <strong>No action specified:</strong> No content available.
                              <?php } ?>
                           </li>
                        <?php endforeach; ?>
                  <?php else: ?>
                        <li>No messages found.</li>
                  <?php endif; ?>
               </ul>
            </div>
      </div>
   </div>
</section>

<!-- Modal for logout confirmation -->
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

<!-- Profile Update Alert Modal -->
<?php if ($profileIncomplete): ?>
<div id="updateProfileModal" class="modal">
   <div class="modal-content">
      <span class="close-update">&times;</span>
      <p>Please update your profile with the latest information to help us serve you better.</p>
      <div class="modal-buttons">
         <a href="profile.php" class="btn-update">Update Profile</a>
         <button class="btn-close-update">Close</button>
      </div>
   </div>
</div>
<?php endif; ?>

<script src="js/script.js"></script>
<script>
   // Modal functionality for logout
   var logoutModal = document.getElementById("logoutModal");
   var logoutBtn = document.getElementById("logout-btn");
   var closeLogoutBtn = document.getElementsByClassName("close")[0];
   var cancelLogoutBtn = document.getElementById("cancel-logout");
   var confirmLogoutBtn = document.getElementById("confirm-logout");

   logoutBtn.onclick = function(event) {
      event.preventDefault();
      logoutModal.style.display = "block";
   }

   closeLogoutBtn.onclick = function() {
      logoutModal.style.display = "none";
   }

   cancelLogoutBtn.onclick = function() {
      logoutModal.style.display = "none";
   }

   confirmLogoutBtn.onclick = function() {
      window.location.href = "/vv/otp/login.php";
   }

   window.onclick = function(event) {
      if (event.target == logoutModal) {
         logoutModal.style.display = "none";
      }
   }

   // Modal functionality for profile update alert
   <?php if ($profileIncomplete): ?>
   var updateProfileModal = document.getElementById("updateProfileModal");
   var closeUpdateBtn = document.getElementsByClassName("close-update")[0];
   var closeUpdateModalBtn = document.getElementsByClassName("btn-close-update")[0];

   updateProfileModal.style.display = "block";

   closeUpdateBtn.onclick = function() {
      updateProfileModal.style.display = "none";
   }

   closeUpdateModalBtn.onclick = function() {
      updateProfileModal.style.display = "none";
   }

   window.onclick = function(event) {
      if (event.target == updateProfileModal) {
         updateProfileModal.style.display = "none";
      }
   }
   <?php endif; ?>
</script>
</body>
</html>
