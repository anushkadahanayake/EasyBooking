<?php
session_start();
include "config/db.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'driver') {
    header("Location: login.php");
    exit();
}

$driver_id = $_SESSION['user_id'];
$today = date("Y-m-d");
$nextWeek = date("Y-m-d", strtotime("+7 days"));

$sql = "SELECT 
            buses.plate_number, 
            timetable.departure_time,
            routes.start_location,
            routes.end_location
        FROM timetable
        INNER JOIN buses ON timetable.bus_id = buses.id
        INNER JOIN routes ON buses.route_id = routes.id
        WHERE buses.driver_id = ?
        AND DATE(timetable.departure_time) BETWEEN ? AND ?
        ORDER BY timetable.departure_time ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $driver_id, $today, $nextWeek);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Driver Dashboard</title>
    <link rel="stylesheet" href="public/css/style.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        html, body {
            font-family: 'Segoe UI', sans-serif;
            background: #eef3f7;
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
        }

        *, *::before, *::after {
            box-sizing: inherit;
        }

        /* Added horizontal margin here */
        .dashboard-wrapper {
            display: flex;
            gap: 20px;
            padding: 30px 0;         /* vertical padding only */
            width: calc(100% - 40px); /* full width minus 20px left & right margin */
            margin: 20px 20px;          /* margin left and right */
            box-sizing: border-box;
        }

        .main-content {
            flex: 0 0 60%;
            overflow-y: auto;
            text-align: center;
        }

        .sidebar {
            flex: 0 0 40%;
            display: flex;
            flex-direction: column;
            gap: 20px;
            overflow-y: auto;
            margin-right: 30px;
        }
        #timeCard {
    background-color: white;
    border-left: 6px solidrgb(20, 113, 243);
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin-bottom: 20px;
    margin-right: 30px;
}

#timeCard h3 {
    margin-top: 0;
    color:rgb(31, 85, 235);
}

#timeCard p {
    font-size: 1.1em;
    color: #333;
}


.btn-container {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    align-items: center;         /* vertical centering */
    justify-content: center;     /* horizontal centering */
}

button {
    display: flex;
    align-items: center;   /* Vertically center content inside button */
    justify-content: center;
    height: 40px;           /* Make all buttons the same height */
    padding: 0 20px;        /* Only left-right padding */
    border: none;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.2s ease;
}


        #startTrackingBtn {
            background-color: #2ecc71;
            color: white;
        }

        #stopTrackingBtn {
            background-color: #e74c3c;
            color: white;
        }

        #startTrackingBtn:hover {
            background-color: #27ae60;
        }

        #stopTrackingBtn:hover {
            background-color: #c0392b;
        }

        #trackingStatus {
            font-weight: bold;
            color: #555;
            margin-top: 10px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 20px;
            border-left: 6px solid #1b73ca;
        }

        .card h3 {
            margin: 0 0 10px;
            color: #1b73ca;
        }

        .card p {
            margin: 5px 0;
        }

        #map {
            height: 450px;
            border: 2px solid #1b73ca;
            border-radius: 8px;
            width: 90%;
            margin-right: 30px;
            align-items: center;
        }
    </style>
</head>
<body>
<?php include "header.php"; ?>

<div class="dashboard-wrapper">
    <!-- Left: Main Content -->
    <div class="main-content">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>

        <!-- Buttons -->
        <div class="btn-container">
            <button id="startTrackingBtn">Start Sharing Location</button>
            <button id="stopTrackingBtn" disabled>Stop Sharing Location</button>
        </div>
        <p id="trackingStatus">Tracking is currently off.</p>

        <h3>Your Bus Schedule (Next 7 Days)</h3>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $day = date('l, d M Y - h:i A', strtotime($row['departure_time']));
            ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($row['plate_number']); ?></h3>
                    <p><strong>Route:</strong> <?php echo htmlspecialchars($row['start_location']); ?> → <?php echo htmlspecialchars($row['end_location']); ?></p>
                    <p><strong>Departure Time:</strong> <?php echo $day; ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No scheduled trips in the next 7 days.</p>
        <?php endif; ?>
    </div>

    <!-- Right: Sidebar -->
    <div class="sidebar">
        <div class="card" id="timeCard">
            <h3>Current Time</h3>
            <p id="currentTime"></p>
        </div>

        <div id="map"></div>
    </div>
</div>

<!-- Geolocation and Tracking -->
<script>
let watchId = null;

function sendLocation(lat, lon) {
    updateMap(lat, lon);
    fetch('update_location.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `lat=${lat}&lon=${lon}`
    }).then(res => res.text()).then(data => {
        console.log("Server response:", data);
    }).catch(err => {
        console.error("Failed to send location:", err);
    });
}

document.getElementById("startTrackingBtn").addEventListener("click", () => {
    if (!navigator.geolocation) {
        alert("Geolocation is not supported by your browser.");
        return;
    }

    document.getElementById("trackingStatus").innerText = "Tracking started...";
    document.getElementById("startTrackingBtn").disabled = true;
    document.getElementById("stopTrackingBtn").disabled = false;

    watchId = navigator.geolocation.watchPosition(
        position => {
            sendLocation(position.coords.latitude, position.coords.longitude);
        },
        error => {
            console.error("Location error:", error);
            alert("Unable to get location.");
        },
        {enableHighAccuracy: true, maximumAge: 0, timeout: 5000}
    );
});

document.getElementById("stopTrackingBtn").addEventListener("click", () => {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
        document.getElementById("trackingStatus").innerText = "Tracking stopped.";
        document.getElementById("startTrackingBtn").disabled = false;
        document.getElementById("stopTrackingBtn").disabled = true;
    }
});
</script>

<!-- Leaflet Map -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
let map = L.map('map').setView([7.8731, 80.7718], 7);
let marker;

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

function updateMap(lat, lon) {
    if (!marker) {
        marker = L.marker([lat, lon]).addTo(map).bindPopup("Your Location").openPopup();
    } else {
        marker.setLatLng([lat, lon]);
    }
    map.setView([lat, lon], 15);
}
</script>

<!-- Current Time Script -->
<script>
function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleString();
    document.getElementById("currentTime").innerText = timeString;
}
setInterval(updateTime, 1000);
updateTime();
</script>


</body>
<?php include "footer.php"; ?>
</html>
