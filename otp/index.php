<?php
session_start();
include('php/db.php');

// Check if 'nic' session variable is set
$nic = $_SESSION['nic'] ?? '';
if (empty($nic)) {
    header("Location: index.php");
    exit(); // Ensure script stops after redirection
}

// Query the database for the user's information
$qry = mysqli_query($conn, "SELECT * FROM user_reg WHERE nic = '{$nic}'");
if (mysqli_num_rows($qry) > 0) {
    $row = mysqli_fetch_assoc($qry);
    if ($row) {
        $_SESSION["verification_status"] = $row["verification_status"];
        if ($row["verification_status"] != 'verified') {
            header("Location: verify.php");
            exit(); // Ensure script stops after redirection
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
    <title>Welcome to Verve Verse</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Welcome To Verve Verse ONLINE EDUCATION PLATFORM</h1>
</body>
</html>
