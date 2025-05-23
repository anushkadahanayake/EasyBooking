<?php
session_start();

// Destroy all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect the user to the homepage (or login page)
header("Location: index.php");  // You can change this to "login.php" if you want to redirect to login page
exit;
?>
