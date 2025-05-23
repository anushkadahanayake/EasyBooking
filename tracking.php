<?php
session_start();
include "config/db.php";
include "header.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow only customers
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];  // You must have saved user_id on login
$today_date = date('Y-m-d');      // Current date for subscription check
$today_day = date('l');            // Current day name for timetable query

// Check if user has active paid subscription
$sub_sql = "SELECT * FROM subscriptions 
            WHERE user_id = $user_id 
            AND plan = 'paid' 
            AND '$today_date' BETWEEN start_date AND renewal_date";
$sub_result = mysqli_query($conn, $sub_sql);
$is_subscribed = mysqli_num_rows($sub_result) > 0;

if (!$is_subscribed) {
    echo "<p style='
    color: white; 
    background: linear-gradient(90deg, #ff416c, #ff4b2b); 
    font-weight: bold; 
    font-size: 1.5em; 
    text-align: center; 
    padding: 20px; 
    border-radius: 10px; 
    width: 80%; 
    margin: 50px auto; 
    box-shadow: 0 0 15px rgba(255,75,43,0.7);
'>You must have an active paid subscription to track the bus.</p>";

    exit;
}

$routeRes = $conn->query("SELECT id, start_location, end_location FROM routes ORDER BY start_location, end_location");

$selectedRouteId = $_POST['route_id'] ?? null;
$buses = [];

if ($selectedRouteId) {
    $stmt = $conn->prepare("
        SELECT buses.id, buses.plate_number, timetable.departure_time
        FROM buses
        JOIN timetable ON buses.id = timetable.bus_id
        WHERE buses.route_id = ? AND timetable.day_of_week = ?
    ");
    $stmt->bind_param("is", $selectedRouteId, $today_day);
    $stmt->execute();
    $buses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Bus Tracking</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" crossorigin="" />
  <style>
    html, body {
      margin: 0;
      padding: 0;
      width: 100%;
      height: 100%;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f5f5;
      box-sizing: border-box;
    }
    *, *::before, *::after {
      box-sizing: inherit;
    }
    .container {
      margin: 30px 20px;
      width: calc(100% - 40px);
      color: #333;
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
      color: #2c3e50;
      font-weight: 700;
    }
    .flex-container {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }
    .left-content {
      flex: 1 1 45%;
      min-width: 300px;
    }
    .right-map {
      flex: 1 1 50%;
      min-width: 300px;
    }
    form {
      display: flex;
      gap: 10px;
      margin-bottom: 25px;
      flex-wrap: wrap;
    }
    label {
      font-weight: 600;
      font-size: 1.1rem;
    }
    select {
      padding: 8px 12px;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 1rem;
      cursor: pointer;
      min-width: 250px;
      transition: border-color 0.3s;
    }
    select:focus {
      outline: none;
      border-color: #2980b9;
      box-shadow: 0 0 5px rgba(41, 128, 185, 0.5);
    }
    h2 {
      color: #2980b9;
      margin-bottom: 15px;
      font-weight: 600;
      border-bottom: 2px solid #2980b9;
      padding-bottom: 5px;
    }
    ul.bus-list {
      list-style: none;
      padding-left: 0;
      max-width: 100%;
    }
    ul.bus-list li {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #f9f9f9;
      margin-bottom: 12px;
      padding: 12px 20px;
      border-radius: 6px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      font-size: 1.05rem;
    }
    ul.bus-list li button {
      background-color: #2980b9;
      border: none;
      color: white;
      padding: 8px 14px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.3s;
    }
    ul.bus-list li button:hover {
      background-color: #1f6391;
    }
    p.no-buses {
      color: #888;
      font-style: italic;
      font-size: 1.1rem;
    }
    #map {
      height: 650px;
      width: 100%;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      margin-top: 15px;
    }

    #currentDateTime {
  background-color: #2980b9;      /* nice blue background */
  color: white;                   /* white text */
  padding: 10px 20px;             /* some padding */
  border-radius: 8px;             /* rounded corners */
  font-size: 1.2rem;              /* bigger font */
  font-weight: 600;               /* semi-bold */
  text-align: center;             /* center text */
  box-shadow: 0 4px 10px rgba(41, 128, 185, 0.5);  /* subtle shadow */
  max-width: 400px;               /* max width for better look */
  margin: 0 auto 20px;            /* center horizontally & spacing below */
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  user-select: none;              /* prevent accidental text selection */
}

  </style>
  <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" crossorigin=""></script>
</head>
<body>

<div class="container">
  <h1>Bus Tracking</h1>
  <div id="currentDateTime" style="margin-bottom: 15px; font-weight: bold;"></div>


  <div class="flex-container">

    <!-- LEFT -->
    <div class="left-content">
      <form method="POST" action="tracking.php" id="routeForm">
        <label for="route">Select Route:</label>
        <select name="route_id" id="route" required onchange="document.getElementById('routeForm').submit()">
          <option value="">-- Select Route --</option>
          <?php while ($row = $routeRes->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>" <?= ($row['id'] == $selectedRouteId) ? 'selected' : '' ?>>
              <?= htmlspecialchars($row['start_location'] . ' → ' . $row['end_location']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </form>

      <?php if ($selectedRouteId): ?>
        <h2>Available Buses Today (<?= $today ?>)</h2>
        <?php if (count($buses) > 0): ?>
          <ul class="bus-list">
            <?php foreach ($buses as $bus): ?>
              <li>
                <span>
                  Bus: <?= htmlspecialchars($bus['plate_number']) ?><br>
                  Departure Time: <?= htmlspecialchars($bus['departure_time']) ?>
                </span>
                <button type="button" onclick="showLocation(<?= $bus['id'] ?>, '<?= htmlspecialchars($bus['plate_number']) ?>')">Show Live Location</button>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="no-buses">No buses available for this route today.</p>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- RIGHT -->
    <div class="right-map">
      <div id="map"></div>
    </div>

  </div>
</div>

<script>
  let map = L.map('map').setView([7.8731, 80.7718], 7);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  let marker;

  function showLocation(busId, busName) {
    fetch('get_location.php?bus_id=' + busId)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          let lat = data.latitude;
          let lng = data.longitude;

          if (marker) {
            map.removeLayer(marker);
          }
          map.setView([lat, lng], 15);

          marker = L.marker([lat, lng]).addTo(map)
            .bindPopup(`<b>${busName}</b><br>Live Location`)
            .openPopup();
        } else {
          alert('Live location not available for this bus right now.');
        }
      })
      .catch(err => {
        alert('Error fetching location.');
        console.error(err);
      });
  }
</script>
<script>
function updateDateTime() {
  const now = new Date();
  // Format date and time nicely
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  const dateStr = now.toLocaleDateString(undefined, options);
  const timeStr = now.toLocaleTimeString();

  document.getElementById('currentDateTime').textContent = `Current Date & Time: ${dateStr}, ${timeStr}`;
}

updateDateTime();              // initial call
setInterval(updateDateTime, 1000);  // update every second

</script>
<?php include "footer.php"; ?>
</body>
</html>
