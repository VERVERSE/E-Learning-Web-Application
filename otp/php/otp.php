<?php 
include_once("db.php");
session_start();

// Check if the necessary POST parameters and session variables are set
if (isset($_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4'], $_SESSION['nic'], $_SESSION['otp'])) {
    $otp1 = $_POST['otp1'];
    $otp2 = $_POST['otp2'];
    $otp3 = $_POST['otp3'];
    $otp4 = $_POST['otp4'];
    $nic = $_SESSION['nic'];
    $session_otp = $_SESSION['otp'];
    $otp = $otp1 . $otp2 . $otp3 . $otp4;

    if (!empty($otp)) {
        if ($otp == $session_otp) {
            // Prepare the SQL query to prevent SQL injection
            $stmt = $conn->prepare("SELECT * FROM user_reg WHERE nic = ? AND otp = ?");
            $stmt->bind_param("ss", $nic, $otp);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) { // if unique id and session otp match
                // Update the user's verification status
                $null_otp = 0; // setting otp to 0 means verified user
                $stmt2 = $conn->prepare("UPDATE user_reg SET verification_status = 'Verified', otp = ? WHERE nic = ?");
                $stmt2->bind_param("is", $null_otp, $nic);
                $update_result = $stmt2->execute();

                if ($update_result) {
                    $row = $result->fetch_assoc();
                    if ($row) {
                        $_SESSION["nic"] = $row["nic"];
                        $_SESSION["verification_status"] = $row["verification_status"];
                        echo "Success";
                    } else {
                        echo "Error fetching user details.";
                    }
                } else {
                    echo "Error updating verification status.";
                }
                $stmt2->close();
            } else {
                echo "No matching user found.";
            }
            $stmt->close();
        } else {
            echo "Wrong OTP";
        }
    } else {
        echo "Enter OTP!";
    }
} else {
    echo "Required parameters not set.";
}

// Close the database connection
$conn->close();
?>
