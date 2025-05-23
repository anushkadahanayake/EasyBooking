<?php
session_start();
include "config/db.php"; // Include the database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from the form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Get the current date and time for created_at
    $created_at = date('Y-m-d H:i:s');

    // Prepare SQL query to insert the new user into the 'users' table
    $sql = "INSERT INTO users (name, email, password, role, phone, address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $name, $email, $hashed_password, $role, $phone, $address, $created_at);

    if ($stmt->execute()) {
        // Redirect to the admin dashboard after successful user creation
        header("Location: admin_dashboard.php");
    } else {
        // Handle error if the user could not be created
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create User</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <!-- Navigation Menu -->
<nav>
    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="create_user.php">Create User</a></li>
        <li><a href="add_bus.php">Add Bus</a></li>
        <li><a href="view_bookings.php">Manage Bookings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>
    <h2>Create a New User</h2>
    <form action="create_user.php" method="POST">
        <label for="name">Name:</label>
        <input type="text" name="name" required>

        <label for="email">Email:</label>
        <input type="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" name="password" required>

        <label for="role">Role:</label>
        <select name="role" required>
            <option value="bus_owner">Bus Owner</option>
            <option value="driver">Driver</option>
            <option value="conductor">Conductor</option>
            <option value="timetable_updater">Timetable Updater</option>
            <option value="admin">Admin</option>
        </select>

        <label for="phone">Phone:</label>
        <input type="text" name="phone" required>

        <label for="address">Address:</label>
        <input type="text" name="address" required>

        <button type="submit">Create User</button>
    </form>
    <a href="edit_user.php">Edit User</a>
    <a href="delete_user.php">Delete User</a>
</body>
</html>

<?php include "footer.php"; ?>
