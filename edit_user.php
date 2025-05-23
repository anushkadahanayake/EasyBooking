<?php
session_start();
include "config/db.php"; // Database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_GET['user_id'];

// Fetch user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Prepare the SQL query to update user data
    $sql_update = "UPDATE users SET name = ?, email = ?, role = ?, phone = ?, address = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssssi", $name, $email, $role, $phone, $address, $user_id);

    if ($stmt_update->execute()) {
        $_SESSION['message'] = "User updated successfully!";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $_SESSION['message'] = "Error updating user!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<nav>
    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="create_user.php">Manage users</a></li>
        <li><a href="bus_managment.php">Manage Buses</a></li>
        <li><a href="view_bookings.php">Manage Bookings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>
    <h2>Edit User Details</h2>
    <form action="edit_user.php?user_id=<?php echo $user_id; ?>" method="POST">
        <label for="name">Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label for="role">Role:</label>
        <select name="role" required>
            <option value="bus_owner" <?php echo ($user['role'] == 'bus_owner') ? 'selected' : ''; ?>>Bus Owner</option>
            <option value="driver" <?php echo ($user['role'] == 'driver') ? 'selected' : ''; ?>>Driver</option>
            <option value="conductor" <?php echo ($user['role'] == 'conductor') ? 'selected' : ''; ?>>Conductor</option>
            <option value="timetable_updater" <?php echo ($user['role'] == 'timetable_updater') ? 'selected' : ''; ?>>Timetable Updater</option>
            <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
        </select>

        <label for="phone">Phone:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>

        <label for="address">Address:</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>

        <button type="submit">Update User</button>
    </form>
</body>
</html>

<?php include "footer.php"; ?>