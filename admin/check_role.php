<?php 

session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Admin") {
    header('Location: /vv/otp/login.php');
    die();
} 

function check_role($role) {
    if (!isset($_SESSION["role"]) || $_SESSION["role"] !== $role) {
    header('Location: /vv/otp/login.php');
    die();
} 
}

?>