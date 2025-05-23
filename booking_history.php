<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "config/db.php"; // your DB connection file
include "header.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please log in to view your bookings.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Prepare SQL query to fetch bookings for the logged-in user
$sql = "SELECT b.booking_id, b.seat_number, b.travel_date, b.from_stop, b.to_stop, b.status, buses.plate_number, 
        CONCAT(routes.start_location, ' to ', routes.end_location) AS route_name
        FROM bookings b
        JOIN buses ON b.bus_id = buses.id
        JOIN routes ON b.route_id = routes.id
        WHERE b.user_id = ?
        ORDER BY b.travel_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo "<h2>My Bookings</h2>";
    echo "<table border='1' cellpadding='8' cellspacing='0'>";
    echo "<tr>
            <th>Booking ID</th>
            <th>Seat Number</th>
            <th>Bus Plate Number</th>
            <th>Route</th>
            <th>From</th>
            <th>To</th>
            <th>Travel Date</th>
            <th>Status</th>
          </tr>";
          while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>#".str_pad($row['booking_id'], 6, '0', STR_PAD_LEFT)."</td>
                    <td>".htmlspecialchars($row['seat_number'] ?? '')."</td>
                    <td>".htmlspecialchars($row['plate_number'] ?? '')."</td>
                    <td>".htmlspecialchars($row['route_name'] ?? '')."</td>
                    <td>".htmlspecialchars($row['from_stop'] ?? '')."</td>
                    <td>".htmlspecialchars($row['to_stop'] ?? '')."</td>
                    <td>".date('F j, Y', strtotime($row['travel_date'] ?? ''))."</td>
                    <td>".htmlspecialchars(ucfirst($row['status'] ?? ''))."</td>
                  </tr>";
        }
    echo "</table>";
} else {
    echo "<p>No bookings found.</p>";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<style>
body {
  font-family: Arial, sans-serif;
  background-color: #f4f7f8;
  margin: 20px;
  color: #333;
}

h2 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 20px;
}

table {
  width: 100%;
  max-width: 900px;
  margin: 0 auto 40px;
  border-collapse: collapse;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  background-color: #fff;
  border-radius: 8px;
  overflow: hidden;
}

th, td {
  padding: 12px 15px;
  text-align: center;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #3498db;
  color: white;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

tr:nth-child(even) {
  background-color: #f9f9f9;
}

tr:hover {
  background-color: #e1f0ff;
}

p {
  text-align: center;
  font-size: 1.1em;
  color: #555;
}

</style>