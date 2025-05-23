<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "config/db.php";
include "header.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'timetable_updater') {
    header("Location: login.php");
    exit();
}

// Set selected day from form submission or default to 'Monday'
$selected_day = $_GET['day'] ?? 'Monday';

// Fetch timetable data for the selected day
$sql = "SELECT buses.plate_number, timetable.id, timetable.departure_time, timetable.arrival_time, timetable.day_of_week, timetable.bus_id 
        FROM timetable
        INNER JOIN buses ON timetable.bus_id = buses.id
        WHERE timetable.day_of_week = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query error: " . $conn->error);
}
$stmt->bind_param("s", $selected_day);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable Updater Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f4f8;
            margin: 0;
            padding: 0;
        }

        nav {
            background-color: #2c3e50;
            padding: 10px 20px;
        }

        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        nav ul li {
            margin-right: 20px;
        }

        nav ul li a {
            color: #ecf0f1;
            text-decoration: none;
            font-weight: bold;
        }

        .dashboard-container {
            max-width: 1000px;
            margin: 30px auto;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        h2, h3 {
            color: #34495e;
        }

        form {
            margin-bottom: 20px;
        }

        label, select {
            font-size: 16px;
        }

        select {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-left: 10px;
        }

        button {
            padding: 6px 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            margin-left: 10px;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        table th {
            background-color: #2980b9;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #ecf0f1;
        }

        .button-link {
            background-color: #27ae60;
            color: white;
            padding: 6px 10px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .button-link:hover {
            background-color: #1e8449;
        }
    </style>
</head>
<body>

<!-- Navigation Menu -->
<nav>
    <ul>
        <li><a href="timetable_update_dashboard.php">Dashboard</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="dashboard-container">
    <h2>Welcome, <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'User'; ?>!</h2>
    <h3>Manage Bus Timetable</h3>

    <form method="GET" action="timetable_update_dashboard.php">
        <label for="day">Select Day of the Week:</label>
        <select name="day" id="day">
            <?php
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($days as $day) {
                $selected = ($selected_day === $day) ? 'selected' : '';
                echo "<option value=\"$day\" $selected>$day</option>";
            }
            ?>
        </select>
        <button type="submit">Filter</button>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Bus Plate Number</th>
                <th>Departure Date</th>
                <th>Departure Time</th>
                <th>Arrival Time</th>
                <th>Day of Week</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                $departureDateTime = new DateTime($row['departure_time']);
                $arrivalDateTime = new DateTime($row['arrival_time']);
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['plate_number']); ?></td>
                    <td><?php echo $departureDateTime->format('Y-m-d'); ?></td>
                    <td><?php echo $departureDateTime->format('H:i'); ?></td>
                    <td><?php echo $arrivalDateTime->format('H:i'); ?></td>
                    <td><?php echo htmlspecialchars($row['day_of_week']); ?></td>
                    <td>
                        <a class="button-link" href="update_timetable.php?timetable_id=<?php echo $row['id']; ?>">Update</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No timetables found for <?php echo htmlspecialchars($selected_day); ?>.</p>
    <?php endif; ?>
</div>

</body>
</html>

