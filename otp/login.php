<?php
include 'php/db.php';

if (isset($_POST["submit"])) {
    $nic = $_POST["nic"];
    $password = $_POST["password"];
    $encrypted_password = md5($password);
    $error_message = "";

    if (!empty($nic) && !empty($encrypted_password)) {
        session_start();

        $sql = mysqli_query($conn, "SELECT * FROM user_reg WHERE verification_status = 'Verified' AND nic ='{$nic}' AND password = '{$encrypted_password}'");

        if (mysqli_num_rows($sql) > 0) {
            $row = mysqli_fetch_assoc($sql);
            if ($row) {
                $_SESSION['nic'] = $row['nic'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['email'] = $row['email'];

                switch ($row['role']) {
                    case 'Student':
                        header('Location: /vv/std/home.php');
                        return;
                    case 'Tutor':
                        header('Location: /vv/tutor/home.php');
                        return;
                    case 'Admin':
                        header('Location: /vv/admin/home.php');
                        return;
                    default:
                        throw new Error("Invalid role");
                }
            } else {
                $error_message = "Something went wrong.";
            }
        } else {
            $error_message = "NIC or Password is incorrect.";
        }
    } else {
        $error_message = "All Fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/form.css">
    <style>
        /* Style for the eye icon */
        .password-container {
            position: relative;
            width: 100%;
        }

        .password-container input {
            width: 100%;
            padding-right: 40px; /* Space for the eye icon */
        }

        .eye-icon {
            position: absolute;
            right: 10px; /* Positioning from the right */
            top: 50%; /* Center vertically */
            transform: translateY(-50%); /* Adjust for exact centering */
            cursor: pointer;
        }
    </style>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('show-password');

            // Toggle password visibility
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.src = "https://img.icons8.com/material-outlined/24/000000/visible.png"; // Change to visible icon
            } else {
                passwordInput.type = 'password';
                passwordToggle.src = "https://img.icons8.com/material-outlined/24/000000/invisible.png"; // Change to invisible icon
            }
        }
    </script>
</head>

<body>

    <div class="form">
        <h2>Login Form</h2>
        <form action="/vv/otp/login.php" autocomplete="off" method="post">
            <?php
            if (!empty($error_message)) {
                echo '<div class="error-text">' . $error_message . '</div>';
            }
            ?>

            <div class="input">
                <label>NIC</label>
                <input type="text" name="nic" placeholder="Enter Your NIC" value="<?php echo isset($_POST['nic']) ? $_POST['nic'] : "" ?>" required>
            </div>

            <div class="input password-container">
                <label>Password</label>
                <input type="password" name="password" id="password" placeholder="Enter Your Password" value="<?php echo isset($_POST['password']) ? $_POST['password'] : "" ?>" required>
                <img id="show-password" class="eye-icon" src="https://img.icons8.com/material-outlined/24/000000/invisible.png" 
                     alt="Show Password" onclick="togglePassword()">
            </div>

            <div class="submit">
                <input type="submit" name="submit" value="Login Now" class="button">
            </div>
        </form>
        <div class="link">Not signed up? <a href="register.php">SignUp Now</a></div>
    </div>

</body>

</html>
