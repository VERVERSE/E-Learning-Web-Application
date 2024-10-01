<?php
include 'db.php';
session_start();

// Initialize variables
$username = '';
$email = '';
$phone = '';
$message = '';
$error = '';
$success = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract data from the form
    $username = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);

    // Validate the input
    if (empty($username) || empty($email) || empty($phone) || empty($message)) {
        $error = "Fields must not be empty";
    } else {
        // Prepare the SQL statement to prevent SQL injection
        $query = "INSERT INTO contact (`name`, `email`, `phone`, `message`) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            $error = "Error preparing statement: " . $conn->error;
        } else {
            // Bind parameters: "ssss" means string, string, string, string
            $stmt->bind_param("ssss", $username, $email, $phone, $message);
            // Execute the query
            if ($stmt->execute()) {
                $success = "Registration Success";
                // Clear the input values
                $username = '';
                $email = '';
                $phone = '';
                $message = '';
            } else {
                $error = "Registration Failed: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form</title>
    <link rel="stylesheet" href="style.css"/>
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <span class="big-circle"></span>
        <img src="img/shape.png" class="square" alt=""/>
        <div class="form">
            <div class="contact-info">
                <h3 class="title">Let's get in touch</h3>
                <p class="text">Send your inquiries to us</p>
                <div class="info">
                    <div class="information">
                        <img src="img/location.png" class="icon" alt="">
                        <p>No.65/2 Havelock Rd , Colombo 06</p>
                    </div>
                    <div class="information">
                        <img src="img/email.png" class="icon" alt="">
                        <p>verveverse@hotmail.com</p>
                    </div>
                    <div class="information">
                        <img src="img/phone.png" class="icon" alt="">
                        <p>0715858915</p>
                    </div>
                </div>
                <div class="social-media">
                    <p>Connect With Us :</p>
                    <div class="social-icon">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <div class="contact-form">
                <span class="circle one"></span>
                <span class="circle two"></span>

                <form action="/vv/contact/index.php" method="POST" autocomplete="off">
                    <h3 class="title">Contact us</h3>
                    <div class="input-container">
                        <input type="text" name="name" class="input" value="<?php echo htmlspecialchars($username); ?>" required>
                        <label for="">Username</label>
                        <span>Username</span>
                    </div>

                    <div class="input-container">
                        <input type="email" name="email" class="input" value="<?php echo htmlspecialchars($email); ?>" required>
                        <label for="">Email</label>
                        <span>Email</span>
                    </div>

                    <div class="input-container">
                        <input type="tel" name="phone" class="input" value="<?php echo htmlspecialchars($phone); ?>" required>
                        <label for="">Phone</label>
                        <span>Phone</span>
                    </div>

                    <div class="input-container textarea">
                        <textarea name="message" class="input" required><?php echo htmlspecialchars($message); ?></textarea>
                        <label for="">Message</label>
                        <span>Message</span>
                    </div>
                    <input type="submit" value="Send" class="btn btn-success"/>
                </form>

                <?php if (isset($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php elseif (isset($success)): ?>
                    <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="app.js"></script>
</body>
</html>
