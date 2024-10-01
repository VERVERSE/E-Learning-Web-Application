<?php
session_start();
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once("db.php");

$role = $_POST["role"];
$name = $_POST["name"];
$nic = $_POST["nic"];
$email = $_POST["email"];
$password = md5($_POST["pass"]);
$cpassword = md5($_POST["cpass"]);
$verification_status = '0';

// Check fields are not empty
if (!empty($role) && !empty($name) && !empty($nic) && !empty($email) && !empty($password) && !empty($cpassword)) {
    // If email is valid
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Checking if email already exists
        $sql = mysqli_query($conn, "SELECT email FROM user_reg WHERE email = '{$email}'");
        if (mysqli_num_rows($sql) > 0) {
            echo "$email ~ Already Exist";
        } else {
            // Checking if password and confirm password match
            if ($password == $cpassword) {
                $otp = mt_rand(1111, 9999);

                // Insert data into user_reg table
                $sql2 = mysqli_query($conn, "INSERT INTO user_reg (nic, name, email, password, otp, verification_status, role) 
                                             VALUES ('{$nic}', '{$name}', '{$email}', '{$password}', '{$otp}', '{$verification_status}', '{$role}')");

                // Check the role and insert into the corresponding table
                if ($role == 'Student') {
                    $sql3 = mysqli_query($conn, "INSERT INTO update_profile (nic) VALUES ('{$nic}')");
                } elseif ($role == 'Tutor') {
                    $sql3 = mysqli_query($conn, "INSERT INTO tutor (nic) VALUES ('{$nic}')");
                }

                if ($sql2 && $sql3) {
                    $sql4 = mysqli_query($conn, "SELECT * FROM user_reg WHERE email = '{$email}'");
                    if (mysqli_num_rows($sql4) > 0) {
                        $row = mysqli_fetch_assoc($sql4);
                        $_SESSION['nic'] = $row['nic'];
                        $_SESSION['email'] = $row['email'];
                        $_SESSION['otp'] = $row['otp'];

                        // Start mail function with PHPMailer
                        $mail = new PHPMailer(true); // Properly instantiate PHPMailer
                        try {
                            // Server settings
                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com';
                            $mail->SMTPAuth = true;
                            $mail->Username = 'hasalakithmina2000@gmail.com';
                            $mail->Password = 'looy eqgv rjkz rjkc';
                            $mail->SMTPSecure = 'tls';
                            $mail->Port = 587;

                            // Recipients
                            $mail->setFrom('hasalakithmina@gmail.com', 'Mailer');
                            $mail->addAddress($email, $name);

                            // Content
                            $mail->isHTML(true);
                            $mail->Subject = 'Verification Code';
                            $mail->Body = "Name: $name <br> Email: $email <br> OTP: $otp";

                            $mail->send();
                            echo 'Success';
                        } catch (Exception $e) {
                            echo "Email Problem! {$mail->ErrorInfo}";
                        }
                        // End mail function
                    }
                } else {
                    echo "Something went wrong!";
                }
            } else {
                echo "Check both passwords are same";
            }
        }
    } else {
        echo "$email ~ This is not a Valid E-mail";
    }
} else {
    echo "All input fields are required!";
}
?>
