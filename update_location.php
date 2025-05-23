<?php
session_start();
include "config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['role'] === 'driver') {
    $driver_id = $_SESSION['user_id'];
    $lat = $_POST['lat'];
    $lon = $_POST['lon'];

    // Store the latest location (create or update row)
    $sql = "INSERT INTO driver_locations (driver_id, latitude, longitude, updated_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE latitude = VALUES(latitude), longitude = VALUES(longitude), updated_at = NOW()";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idd", $driver_id, $lat, $lon);
    $stmt->execute();

    echo "Location updated";
} else {
    echo "Unauthorized or invalid request";
}
?>
