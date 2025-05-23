<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - AC Bus Booking</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<?php include "header.php"; ?>

<div class="container">
    <h2>Contact Us</h2>
    <p>If you have any questions or need help, feel free to contact us using the details below:</p>

    <div class="contact-details">
        <p><strong>Email:</strong> support@acbusbooking.com</p>
        <p><strong>Phone:</strong> +94 71 234 5678</p>
        <p><strong>Office Hours:</strong> Monday - Friday, 9:00 AM - 5:00 PM</p>
    </div>

    <h3>Send us a message</h3>
    <form action="send_message.php" method="POST" class="contact-form">
        <label for="name">Your Name:</label>
        <input type="text" name="name" id="name" required>

        <label for="email">Your Email:</label>
        <input type="email" name="email" id="email" required>

        <label for="message">Message:</label>
        <textarea name="message" id="message" rows="5" required></textarea>

        <button type="submit">Send Message</button>
    </form>
</div>

<?php include "footer.php"; ?>

</body>
</html>
