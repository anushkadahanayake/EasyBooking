<?php
session_start();

include "header.php";
include "config/db.php"; // Make sure your DB connection is included

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = "";
$success_message = "";

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST['name'];
    $user_email = $_POST['email'];
    $user_phone = $_POST['phone'];
    $user_address = $_POST['address'];

    if (empty($user_name) || empty($user_email) || empty($user_phone) || empty($user_address)) {
        $error_message = "All fields are required!";
    } else {
        $sql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $user_name, $user_email, $user_phone, $user_address, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Your profile has been updated!";
        } else {
            $error_message = "Failed to update profile.";
        }
        $stmt->close();
    }
}

// Fetch user data
$sql = "SELECT name, email, phone, address FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name, $user_email, $user_phone, $user_address);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style> 
        .profile-container {
    max-width: 500px;
    margin: 30px auto;
    padding: 25px;
    background-color: #f9f9f9;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.profile-container form {
    display: flex;
    flex-direction: column;
}

.profile-container label {
    margin-top: 15px;
    font-weight: 600;
    color: #444;
}

.profile-container input[type="text"],
.profile-container input[type="email"] {
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    margin-top: 5px;
    font-size: 16px;
    outline: none;
    transition: border 0.3s;
}

.profile-container input:focus {
    border-color: #007bff;
}

.profile-container button {
    margin-top: 20px;
    padding: 12px;
    background-color: #007bff;
    color: white;
    border: none;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.profile-container button:hover {
    background-color: #0056b3;
}

.error-message {
    color: #dc3545;
    background-color: #f8d7da;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    text-align: center;
}

.success-message {
    color: #28a745;
    background-color: #d4edda;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    text-align: center;
}
    </style>
</head>
<body>

<h2 class="center-title">Your Profile</h2>

<div class="profile-container">
    <!-- Display any error or success message -->
    <?php if (!empty($error_message)): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
        <p class="success-message"><?php echo $success_message; ?></p>
    <?php endif; ?>

    <!-- Profile Form -->
    <form action="" method="post">
        <label for="name">Full Name:</label>
        <input type="text" name="name" id="name" value="<?php echo $user_name; ?>" required>
        
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo $user_email; ?>" required>
        
        <label for="phone">Phone Number:</label>
        <input type="text" name="phone" id="phone" value="<?php echo $user_phone; ?>" required>
        
        <label for="address">Address:</label>
        <input type="text" name="address" id="address" value="<?php echo $user_address; ?>" required>
        
        <button type="submit">Update Profile</button>
    </form>
</div>

</body>
</html>

<?php include "footer.php"; ?>