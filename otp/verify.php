<?php
    // Start the session
    session_start();
    include("php/db.php");

    // Check if the unique_id is set in the session
    if (!isset($_SESSION["nic"])) {
        header("Location: login.php");
        exit();
    }

    $nic = $_SESSION["nic"];

    // Prepare and execute the query securely to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM user_reg WHERE nic = ?");
    $stmt->bind_param("s", $nic);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION["verification_status"] = $row["verification_status"];

        // Redirect to index if the user is verified
        if ($row["verification_status"] == 'verified') {
            header("Location: verify.php");
            exit();
        }
    } else {
        // Handle the case where the user is not found
        header("Location: register.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify</title>
    <link rel="stylesheet" href="css/verify.css">
    <link rel="stylesheet" href="css/form.css">
</head>
<body>
    <div class="form" style="text-align: center;">
        <h2>Verify Your Account</h2>
        <p>Enter OTP to verify your Email address</p>
        <form action="php/verify.php" method="POST" autocomplete="off">
           
            <div class="field-input">
                <input type="number" name="otp1" class="otp_field" placeholder="0" min="0" max="9" required onpaste="return false">
                <input type="number" name="otp2" class="otp_field" placeholder="0" min="0" max="9" required onpaste="return false">
                <input type="number" name="otp3" class="otp_field" placeholder="0" min="0" max="9" required onpaste="return false">
                <input type="number" name="otp4" class="otp_field" placeholder="0" min="0" max="9" required onpaste="return false">
            </div>
            <div class="submit">
                <input type="submit" value="Verify Now" class="button">
            </div>
        </form>
    </div>
    <script src="js/verify.js"></script>
</body>
</html>
