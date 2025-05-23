<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "config/db.php"; // Include the database connection
include "header.php";

// Check if the user is already logged in
if (isset($_SESSION['role'])) {
    // Redirect based on the logged-in role
    switch ($_SESSION['role']) {
        case 'customer':
            header("Location: customer_dashboard.php");
            exit();
        case 'conductor':
            header("Location: conductor_dashboard.php");
            exit();
        case 'driver':
            header("Location: driver_dashboard.php");
            exit();
        case 'bus_owner':
            header("Location: bus_owner_dashboard.php");
            exit();
        case 'admin':
            header("Location: admin_dashboard.php");
            exit();
        case 'timetable_updater':
            header("Location: timetable_update_dashboard.php");
            exit();
        default:
            header("Location: index.php"); // Redirect to home page if the role is unknown
            exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get email and password from form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Sanitize email input to avoid any malicious code
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Prepare SQL query to fetch user data
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if the user exists and compare plain text passwords
    if ($user && $user['password'] === $password) {
        // Password matches, start session and redirect
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];

        // Redirect based on role
        switch ($user['role']) {
            case 'customer':
                header("Location: customer_dashboard.php");
                break;
            case 'conductor':
                header("Location: conductor_dashboard.php");
                break;
            case 'driver':
                header("Location: driver_dashboard.php");
                break;
            case 'bus_owner':
                header("Location: bus_owner_dashboard.php");
                break;
            case 'admin':
                header("Location: admin_dashboard.php");
                break;
            case 'timetable_updater':
                header("Location: timetable_update_dashboard.php");
                break;
            default:
                echo "Invalid role!";
                break;
        }
        exit();
    } else {
        // Show error message if password doesn't match or user doesn't exist
        echo "Invalid login details!";
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="public/css/style.css">
    
</head>
<body>

<div class="login-container container-box">
    <h2>Login</h2>
    <form action="login.php" method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

</body>
</html>

<?php include "footer.php"; ?>
