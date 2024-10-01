<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            z-index: 1; /* Ensure it stays above the input field */
        }
    </style>
</head>

<body>
    <div class="form">
        <!-- Back button -->
        <a href="http://localhost/vv/index.html" class="back-button">
            <img src="https://img.icons8.com/?size=100&id=80689&format=png&color=000000" alt="Back Arrow" style="width: 25px; height: 25px;">
        </a>

        <h2>Signup Form</h2>
        <p>It's free and always will be</p>
        <form action="" enctype="multipart/form-data" autocomplete="off" onsubmit="return validateForm()">
            <div class="input role-input">
                <label>Role:</label>
                <div class="radio-buttons">
                    <label>
                        <input type="radio" name="role" value="Student" required>
                        Student
                    </label>
                    <label>
                        <input type="radio" name="role" value="Tutor" required>
                        Tutor
                    </label>
                </div>
            </div>

            <div class="input">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" placeholder="Enter Your Full Name" required minlength="2" pattern="[A-Za-z\s]+">
            </div>

            <div class="input">
                <label for="nic">NIC</label>
                <input type="text" name="nic" id="nic" placeholder="Enter Your NIC" required pattern="[A-Za-z0-9]{10,12}">
            </div>

            <div class="input">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter Your Email" required>
            </div>

            <div class="grid-detail">
                <div class="input password-container">
                    <label for="pass">Password</label>
                    <input type="password" name="pass" id="pass" placeholder="Enter Your Password" required minlength="4">
                    <img id="show-password" class="eye-icon" src="https://img.icons8.com/material-outlined/24/000000/invisible.png" 
                         alt="Show Password" onclick="togglePassword('pass', 'show-password')">
                </div>
                <div class="input password-container">
                    <label for="cpass">Confirm Password</label>
                    <input type="password" name="cpass" id="cpass" placeholder="Confirm Your Password" required minlength="4" oninput="checkPasswordMatch()">
                    <img id="show-cpassword" class="eye-icon" src="https://img.icons8.com/material-outlined/24/000000/invisible.png" 
                         alt="Show Password" onclick="togglePassword('cpass', 'show-cpassword')">
                </div>
            </div>

            <script>
                function checkPasswordMatch() {
                    const password = document.getElementById('pass').value;
                    const confirmPassword = document.getElementById('cpass').value;
                    if (password !== confirmPassword) {
                        document.getElementById('cpass').setCustomValidity("Passwords do not match");
                    } else {
                        document.getElementById('cpass').setCustomValidity("");
                    }
                }

                function togglePassword(fieldId, iconId) {
                    const passwordInput = document.getElementById(fieldId);
                    const passwordToggle = document.getElementById(iconId);
                    
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

            <div class="submit">
                <input type="submit" value="Register" class="button">
            </div>
        </form>
        <div class="link">Already signed up? <a href="login.php">Login Now</a></div>
    </div>
    <script src="js/register.js"></script>

</body>

</html>
