<?php
include "header.php";

// Set subscription start date to current date
$subscription_start = date("Y-m-d");

// Set renewal date (1 month later)
$renewal_date = date("Y-m-d", strtotime("+1 month"));

// Sample user details
$user_name = "John Doe";  // You can fetch these details from session or database
$user_email = "johndoe@example.com";
$user_phone = "+123456789";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<h2 class="center-title">Thank You for Subscribing!</h2>

<div class="thankyou-container">
    <p>Your subscription is now active. Below are your subscription details:</p>

    <table class="thankyou-table">
        <tr>
            <th>Subscription Start Date</th>
            <td><?php echo $subscription_start; ?></td>
        </tr>
        <tr>
            <th>Renewal Date</th>
            <td><?php echo $renewal_date; ?></td>
        </tr>
        <tr>
            <th>Name</th>
            <td><?php echo $user_name; ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo $user_email; ?></td>
        </tr>
        <tr>
            <th>Phone</th>
            <td><?php echo $user_phone; ?></td>
        </tr>
    </table>

    <p class="center-text">Enjoy your premium access!</p>
</div>

</body>
</html>

<?php include "footer.php"; ?>