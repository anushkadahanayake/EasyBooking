<?php
session_start();
include "config/db.php";
include "header.php";

// Check if the user is logged in and is a conductor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'conductor') {
    header("Location: login.php");
    exit();
}

$conductor_id = $_SESSION['user_id'];

$sql = "SELECT buses.id, buses.plate_number, timetable.bus_id, timetable.departure_time, timetable.arrival_time, 
               bookings.booking_id, users.name AS customer_name, bookings.seat_number 
        FROM bookings
        INNER JOIN timetable ON bookings.timetable_id = timetable.id
        INNER JOIN buses ON timetable.bus_id = buses.id
        INNER JOIN users ON bookings.user_id = users.id
        WHERE buses.conductor_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $conductor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Conductor Dashboard</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f8ff;
    color: #333;
}

.navbar {
    background: #3f51b5;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
}

.nav-left {
    font-size: 20px;
    font-weight: bold;
}

.nav-right {
    list-style: none;
    display: flex;
    gap: 15px;
    margin: 0;
    padding: 0;
}

.nav-right li a {
    color: white;
    text-decoration: none;
    font-weight: 500;
}

.nav-right li a:hover {
    text-decoration: underline;
}

.dashboard-container {
    padding: 20px;
    max-width: 1000px;
    margin: auto;
}

.welcome-card {
    background: #fff;
    padding: 20px;
    border-left: 6px solid #3f51b5;
    margin-bottom: 20px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
    border-radius: 8px;
}

h3 {
    color: #3f51b5;
}

.card-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.booking-card {
    background: #ffffff;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 5px solid #2196f3;
    transition: transform 0.2s;
}

.booking-card:hover {
    transform: translateY(-3px);
}

.booking-card h4 {
    margin-top: 0;
    color: #2196f3;
}

.booking-card p {
    margin: 8px 0;
}

.btn {
    display: inline-block;
    padding: 8px 12px;
    background: #2196f3;
    color: white;
    border-radius: 4px;
    text-decoration: none;
    font-weight: bold;
}

.btn:hover {
    background: #1976d2;
}

.no-bookings {
    color: #777;
    background: #fff;
    padding: 20px;
    border-left: 5px solid #f44336;
    border-radius: 8px;
    max-width: 500px;
    margin-top: 20px;
}
</style>
</head>
<body>


<div class="dashboard-container">
    <div class="welcome-card">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> 👋</h2>
        <p>Today is <?php echo date("l, F j, Y"); ?> — <strong><?php echo date("h:i A"); ?></strong></p>
    </div>

    <h3>Your Assigned Bus Bookings</h3>

    <?php if ($result->num_rows > 0): ?>
        <div class="card-container">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="booking-card">
                    <h4>Bus Plate: <?php echo htmlspecialchars($row['plate_number']); ?></h4>
                    <p><strong>Departure:</strong> <?php echo htmlspecialchars($row['departure_time']); ?></p>
                    <p><strong>Arrival:</strong> <?php echo htmlspecialchars($row['arrival_time']); ?></p>
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($row['customer_name']); ?></p>
                    <p><strong>Seat No:</strong> <?php echo htmlspecialchars($row['seat_number']); ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="no-bookings">No bookings found for your assigned bus.</p>
    <?php endif; ?>
</div>
<?php include "footer.php"; ?>
</body>
</html>

