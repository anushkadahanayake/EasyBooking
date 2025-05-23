<!-- header.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Booking System</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<!-- Navigation Bar -->
<nav>
<a href="index.php" class="logo">CoolBooking</a>
    <ul>
        
        <?php if (isset($_SESSION['role'])): ?>
            <!-- Logic for different roles -->
            <?php if ($_SESSION['role'] == 'bus_owner'): ?>
                <!-- Bus Owner menu options -->
                <li><a href="bus_owner_dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php elseif ($_SESSION['role'] == 'customer'): ?>
                <!-- Customer menu options -->
                <li><a href="customer_dashboard.php">Home</a></li>

                <li><a href="timetable.php">Bus Timetable</a></li>
                <li><a href="booking.php">Book a Seat</a></li>
                <li><a href="tracking.php"> Track </a></li>

                <?php if (!isset($_SESSION['is_paid']) || $_SESSION['is_paid'] == false): ?>
                <li><a href="subscription.php">Subscribe</a></li>
                <?php endif; ?>

                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php elseif ($_SESSION['role'] == 'conductor'): ?>
                <!-- Conductor menu options -->
                <li><a href="conductor_dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php elseif ($_SESSION['role'] == 'driver'): ?>
                <!-- Driver menu options -->
                <li><a href="driver_dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php elseif ($_SESSION['role'] == 'admin'): ?>
                <!-- Admin menu options -->
                <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        <?php else: ?>
            <!-- Always show Home and Bus Timetable -->
            <li><a href="index.php">Home</a></li>
            <li><a href="timetable.php">Bus Timetable</a></li>
            <!-- If not logged in, show Login and Register options -->
            <li><a href="login.php">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>

</body>
</html>