<?php
session_start();
include "config/db.php"; // Include the database connection
include "header.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from the form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Assign 'customer' role
    $role = 'customer';

    // Get the current date and time for created_at
    $created_at = date('Y-m-d H:i:s');

    // Prepare SQL query to insert the new user into the 'users' table
    $sql = "INSERT INTO users (name, email, password, role, phone, address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $name, $email, $hashed_password, $role, $phone, $address, $created_at);

    if ($stmt->execute()) {
        // Redirect to login page after successful registration
        header("Location: login.php");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration</title>
    <link rel="stylesheet" href="public/css/style.css">
    
</head>
<body>

<div class="register-container container-box">
    <h2>Register</h2>
    <form action="register.php" method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" required>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required>

        <button type="submit">Register</button>
    </form>
    <p>Do have an account? <a href="login.php">Login here</a></p>
</div>

</body>
</html>

<?php include "footer.php"; ?>