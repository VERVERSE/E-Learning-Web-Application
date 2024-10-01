<?php 
include 'db.php';
session_start();

// Retrieve the NIC from the session
$nic = $_SESSION["nic"];

// Check if NIC is available
if (empty($nic)) {
    echo "NIC not found in session.";
    exit;
}

// Retrieve existing profile data
$sql = mysqli_query($conn, "SELECT * FROM update_profile WHERE nic ='{$nic}'");

if (mysqli_num_rows($sql) > 0) {
    $profile = mysqli_fetch_assoc($sql);
} else {
    $profile = null; // No profile data found
}
// Handle verification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $nic = $_POST['nic'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = mysqli_prepare($conn, "UPDATE verification SET action = 'approved' WHERE nic = ?");
        mysqli_stmt_bind_param($stmt, "s", $nic);
        mysqli_stmt_execute($stmt);
    } elseif ($action === 'reject') {
        $stmt = mysqli_prepare($conn, "UPDATE verification SET action = 'rejected' WHERE nic = ?");
        mysqli_stmt_bind_param($stmt, "s", $nic);
        mysqli_stmt_execute($stmt);
    }
    
    // Optionally: you can set a session message to show the user
    $_SESSION['verification_message'] = "Verification has been $action.";
    header("Location: {$_SERVER['PHP_SELF']}"); // Redirect to the same page
    exit;
}

// Handle PDF upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['verification_pdf']) && $_FILES['verification_pdf']['error'] == UPLOAD_ERR_OK) {
    $pdf = $_FILES['verification_pdf'];
    $pdf_target_file = dirname(__FILE__) . "/verification_pdfs/{$nic}_{$pdf['name']}";
    $pdf_uploaded = move_uploaded_file($pdf["tmp_name"], $pdf_target_file);

    if ($pdf_uploaded) {
        $pdfUrl = "/vv/std/verification_pdfs/{$nic}_{$pdf['name']}";

        // Insert into verification table with action as pending
        $stmt = mysqli_prepare($conn, "INSERT INTO verification (nic, role, pdf, action) VALUES (?, ?, ?, 'pending')");
        $role = htmlspecialchars($_SESSION['role']);
        mysqli_stmt_bind_param($stmt, "sss", $nic, $role, $pdfUrl);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "PDF uploaded successfully and status set to pending.";
        } else {
            $error_message = "Database error: Could not update verification.";
        }
    } else {
        $error_message = "Failed to upload verification PDF.";
    }
}

// Retrieve verification records
$verifications = mysqli_query($conn, "SELECT * FROM verification WHERE nic = '{$nic}'");
$verificationStatus = "pending"; // Default status

if (mysqli_num_rows($verifications) > 0) {
    $verification = mysqli_fetch_assoc($verifications);
    $verificationStatus = $verification['action']; // Get the action status
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Student Dashboard</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

   <style>
     .verification-section {
        float: right;
        width: 300px;
        margin-top: -45px; /* Moved down a bit */
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 10px;
        background-color: #f9f9f9; /* Light background */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    }

    .verification-section h2 {
        text-align: center;
        color: #333;
        font-size: 18px;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .upload-form {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 10px;
    }

    .upload-label {
        background-color: #4CAF50; /* Green color */
        color: white;
        padding: 15px;
        border-radius: 5px;
        text-align: center;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.3s;
    }

    .upload-label:hover {
        background-color: #45a049; /* Darker green on hover */
    }

    input[type="file"] {
        display: none; /* Hide default file input */
    }

    .upload-button {
        margin-top: 10px;
        background-color: #007BFF; /* Bootstrap blue */
        color: white;
        padding: 15px 20px; /* Increased height */
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .upload-button:hover {
        background-color: #0056b3; /* Darker shade of blue on hover */
    }

    .verification-section p {
        font-size: 14px;
        margin-top: 10px;
        color: #666;
    }

    .verification-instruction {
        font-size: 12px; /* Smaller font size */
        color: #555; /* Slightly darker gray for better readability */
        margin-top: 8px; /* Space above the text */
        text-align: center; /* Center the text */
        padding: 5px; /* Padding for a cleaner look */
        background-color: #e9f7ef; /* Light green background */
        border-radius: 5px; /* Rounded corners */
        border: 1px solid #b2ebf2; /* Light blue border */
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
      <h3 class="name"><?php echo htmlspecialchars($_SESSION['name']); ?></h3>
      <p class="role"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
      <a href="profile.php" class="btn">View Profile</a>
   </div>
   <nav class="navbar">
      <a href="home.php"><i class="fas fa-home"></i><span>HOME</span></a>
      <a href="profile.php"><i class="fa-solid fa-user"></i><span>PROFILE</span></a>
      <a href="classes.php"><i class="fas fa-graduation-cap"></i><span>CLASSES</span></a>
      <a href="calendar.php"><i class="fa-regular fa-calendar-days"></i><span>CALENDAR</span></a>
      <a href="help.php"><i class="fas fa-headset"></i><span>HELP</span></a>
   </nav>
</div>

<section class="user-profile">
    <h1 class="heading">Your Profile</h1>
    <div class="info">
        <div class="user">
            <?php if ($profile && !empty($profile['pic'])): ?>
                <img src="<?php echo htmlspecialchars($profile['pic']); ?>" alt="Profile Picture">
            <?php else: ?>
                <img src="images/pic-1.jpg" alt="Default Profile Picture">
            <?php endif; ?>
            <h3><?php echo htmlspecialchars($_SESSION['name']); ?></h3>
            <p><?php echo htmlspecialchars($_SESSION['role']); ?></p>
            <?php if ($verificationStatus === 'approved'): ?>
                <a href="update.php" class="inline-btn">Update Profile</a>
            <?php endif; ?>
        </div>

        <div class="profile-card">
            <div class="profile-info">
                <p class="info-item"><span class="label">Email:</span> <?php echo $profile && !empty($profile['email']) ? htmlspecialchars($profile['email']) : 'Not entered'; ?></p>
                <p class="info-item"><span class="label">School:</span> <?php echo $profile && !empty($profile['school']) ? htmlspecialchars($profile['school']) : 'Not entered'; ?></p>
                <p class="info-item"><span class="label">Mobile:</span> <?php echo $profile && !empty($profile['mobile']) ? htmlspecialchars($profile['mobile']) : 'Not entered'; ?></p>
                <p class="info-item"><span class="label">Category:</span> 
                    <?php 
                    if ($profile && !empty($profile['category'])) {
                        echo htmlspecialchars($profile['category']);
                    } else {
                        echo 'Not entered';
                    }
                    ?>
                </p>

                <?php if ($profile && $profile['category'] === 'A/L'): ?>
                    <p class="info-item"><span class="label">Subject Stream:</span> 
                        <?php echo $profile && !empty($profile['stream']) ? htmlspecialchars($profile['stream']) : 'Not entered'; ?>
                    </p>
                <?php elseif ($profile && $profile['category'] === 'Course'): ?>
                    <p class="info-item"><span class="label">Course:</span> 
                        <?php echo $profile && !empty($profile['course']) ? htmlspecialchars($profile['course']) : 'Not entered'; ?>
                    </p>
                <?php endif; ?>

                <p class="info-item"><span class="label">Address:</span> <?php echo $profile && !empty($profile['address']) ? htmlspecialchars($profile['address']) : 'Not entered'; ?></p>
            </div>
        </div>

        <?php if ($verificationStatus !== 'approved'): ?>
        <div class="verification-section">
            <h2><?php echo $verificationStatus === 'rejected' ? 'Re-upload Verification' : 'Upload Verification'; ?></h2>
            <p class="verification-status" style="color: <?php echo $verificationStatus === 'approved' ? 'green' : 'red'; ?>; text-align: right; position: absolute; top: 10px; right: 10px; font-size: 12px;">
                Status: <?php echo htmlspecialchars(ucfirst($verificationStatus)); ?>
            </p>
            <form action="" method="post" enctype="multipart/form-data" class="upload-form">
                <label for="verification_pdf" class="upload-label">Click here to select PDF</label>
                <input type="file" name="verification_pdf" id="verification_pdf" accept="application/pdf" required>
                <button type="submit" class="upload-button">Upload PDF</button>
                <p class="verification-instruction">Make sure to upload a valid PDF of your NIC for verification.</p>
            </form>
            <?php if ($verificationStatus === 'rejected'): ?>
                <p class="error-message" style="color: red;">Your previous upload was rejected. Please ensure the document is clear and valid before re-uploading.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>


<!-- Pop-up structure -->
<div id="success-popup" class="popup">
    <div class="popup-content">
        <span class="close-popup">&times;</span>
        <p id="success-message">PDF uploaded successfully!</p>
    </div>
</div>

<div id="error-popup" class="popup">
    <div class="popup-content">
        <span class="close-popup">&times;</span>
        <p id="error-message">Failed to upload PDF. Please try again.</p>
    </div>
</div>

<script>
// Pop-up handling script
document.addEventListener("DOMContentLoaded", function() {
    var successPopup = document.getElementById('success-popup');
    var errorPopup = document.getElementById('error-popup');
    
    <?php if (!empty($success_message)): ?>
        successPopup.style.display = "block";
    <?php elseif (!empty($error_message)): ?>
        errorPopup.style.display = "block";
    <?php endif; ?>
    
    var closeButtons = document.getElementsByClassName('close-popup');
    Array.from(closeButtons).forEach(function(button) {
        button.onclick = function() {
            this.parentElement.parentElement.style.display = "none";
        }
    });

    window.onclick = function(event) {
        if (event.target == successPopup || event.target == errorPopup) {
            successPopup.style.display = "none";
            errorPopup.style.display = "none";
        }
    }
});
</script>

<style>
/* Popup styling */
.popup {
    display: none;
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.popup-content {
    background-color: white;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 400px;
    text-align: center;
    border-radius: 10px;
}

.close-popup {
    float: right;
    font-size: 25px;
    cursor: pointer;
}
</style>


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
   // Modal functionality
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
