<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config/db.php";
include "header.php";

date_default_timezone_set('Asia/Colombo');

// Calculate date boundaries
$current_date = date("Y-m-d");
$min_date = date("Y-m-d", strtotime("+1 day"));  // Tomorrow
$max_date = date("Y-m-d", strtotime("+14 days")); // 2 weeks from now

$timetable_rows = [];
$selected_date = $route_number = $start_location = $end_location = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_date = $_POST['date'] ?? '';
    $route_number = $_POST['route'] ?? '';
    $start_location = $_POST['from'] ?? '';
    $end_location = $_POST['to'] ?? '';

    // Validate date range
    if ($selected_date < $min_date || $selected_date > $max_date) {
        $_SESSION['message'] = "Please select a date between tomorrow and two weeks from today.";
        header("Location: timetable.php");
        exit();
    }

    $selected_day_of_week = date("l", strtotime($selected_date)); 

    // Prepare and execute query
    $stmt = $conn->prepare("
        SELECT buses.plate_number, timetable.departure_time, timetable.arrival_time
        FROM timetable
        JOIN routes ON timetable.route_id = routes.id
        JOIN buses ON timetable.bus_id = buses.id
        WHERE timetable.day_of_week = ?
        AND routes.route_number = ?
        AND routes.start_location = ?
        AND routes.end_location = ?
        ORDER BY timetable.departure_time ASC
    ");
    $stmt->bind_param("ssss", $selected_day_of_week, $route_number, $start_location, $end_location);
    $stmt->execute();
    $result = $stmt->get_result();
    $timetable_rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Timetable</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
            --warning: #f72585;
            --info: #7209b7;
            --text: #2b2d42;
            --bg: #f8f9fa;
        }
        
        html, body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
            width: 100%;
            min-height: 100vh;
            overflow-x: hidden;
            height: 100%;
        }

        .page-container {
            display: flex;
            flex-direction: row;
            width: 100%;
            min-height: 100vh;

        }

        .main-content {
            flex: 0 0 70%;
            padding: 30px;
            max-width: 1200px;
        }

        .sidebar {
            flex: 0 0 30%;
            width: 350px;
            background: white;
            padding: 30px;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.05);
            overflow-y: auto;
            height: 100vh;
            position: sticky;
            top: 0;
            right: 0; /* Aligns the sidebar to the right */
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.3);
        }

        .header h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 600;
        }

        .clock-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .clock {
            position: relative;
            width: 220px;
            height: 220px;
            background: linear-gradient(145deg,rgb(48, 58, 197), #e6e6e6);
            border-radius: 50%;
            margin: 0 auto 20px auto;
            box-shadow: 0 0 25px rgba(67, 97, 238, 0.4);
            border: 6px solid var(--primary);
        }

        .number {
            position: absolute;
            width: 30px;
            height: 30px;
            text-align: center;
            line-height: 30px;
            color: var(--text);
            font-weight: bold;
            font-size: 14px;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
        }

        .hand {
            position: absolute;
            top: 50%;
            left: 50%;
            transform-origin: 0% 50%;
            transform: rotate(90deg);
            border-radius: 5px;
        }

        .hand.hour {
            background-color: var(--primary);
            width: 35%;
            height: 6px;
            z-index: 3;
        }

        .hand.minute {
            background-color: var(--accent);
            width: 45%;
            height: 4px;
            z-index: 2;
        }

        .hand.second {
            background: var(--warning);
            width: 50%;
            height: 2px;
            z-index: 1;
        }

        #live-clock {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--primary);
            background: white;
            padding: 8px 15px;
            border-radius: 30px;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .calendar-box {
            background: white;
            border-radius: 15px;
            padding: 0;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .calendar-box h3 {
            text-align: center;
            color: var(--primary);
            margin-top: 0;
            font-size: 1.3rem;
        }

        .calendar-box table {
            width: 100%;
            text-align: center;
            border-collapse: separate;
            border-spacing: 5px;
        }

        .calendar-box th {
            color: white;
            font-weight: 500;
            padding: 8px;
        }

        .calendar-box td {
            padding: 10px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .calendar-box td:hover {
            background: #f0f4ff;
        }

        .calendar-box .today {
            background-color: var(--primary);
            color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(67, 97, 238, 0.3);
        }

        form {
            background: white;
            padding: 25px;
            border-radius: 15px;
            max-width: 600px;
            margin: 0 auto 40px auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
        }

        form label {
            display: block;
            margin-top: 15px;
            font-weight: 500;
            color: var(--text);
        }

        input, select {
            width: 100%;
            padding: 12px 15px;
            margin-top: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        input:focus, select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(72, 149, 239, 0.2);
            outline: none;
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 15px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 30px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        th {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: 500;
        }

        td {
            border: 1px solid #f0f0f0;
            padding: 12px 15px;
            text-align: center;
            transition: all 0.2s;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover td {
            background-color: #f0f4ff;
        }

        .summary {
            margin-top: 30px;
            background: white;
            padding: 20px;
            border-radius: 15px;
            max-width: 600px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            border-left: 5px solid var(--primary);
        }

        .summary h3 {
            color: var(--primary);
            margin-top: 0;
            font-size: 1.3rem;
        }

        .summary p {
            margin: 10px 0;
            font-size: 1rem;
        }

        .summary strong {
            color: var(--secondary);
        }

        .no-records {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 15px;
            max-width: 600px;
            margin: 30px auto;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            color: var(--warning);
            font-weight: 500;
        }

        @media (max-width: 1200px) {
            .page-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 20px;
                display: flex;
                flex-wrap: wrap;
                justify-content: space-around;
            }
            
            .clock-container, .calendar-box {
                flex: 1;
                min-width: 300px;
                margin: 10px;
            }
            
            .main-content {
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }
            
            form {
                padding: 20px;
            }
            
            .sidebar {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
<div class="page-container">
    <div class="main-content">
        <div class="header">
            <h1>Bus Timetable</h1>
        </div>

        <form method="POST">
            <label for="date">Select Date:</label>
            <input type="date" name="date" min="<?php echo $current_date; ?>" required>

            <label for="route">Select Route:</label>
            <select name="route" required>
                <option value="06">06 </option>
                <option value="120">120 </option>
            </select>

            <label>From:</label>
            <select name="from" required>
                <option value="Kurunegala">Kurunegala</option>
                <option value="Colombo">Colombo</option>
                <option value="Horana">Horana</option>
            </select>

            <label>To:</label>
            <select name="to" required>
                <option value="Colombo">Colombo</option>
                <option value="Kurunegala">Kurunegala</option>
                <option value="Horana">Horana</option>
            </select>

            <button type="submit">View Timetable</button>
        </form>

        <?php if (!empty($timetable_rows)): ?>
            <div class="summary">
                <h3>Selected Summary</h3>
                <p>Date: <strong><?php echo htmlspecialchars($selected_date); ?></strong></p>
                <p>Route: <strong><?php echo htmlspecialchars($route_number); ?></strong></p>
                <p>From: <strong><?php echo htmlspecialchars($start_location); ?></strong> → To: <strong><?php echo htmlspecialchars($end_location); ?></strong></p>
            </div>

            <table>
                <tr>
                    <th>Bus Plate Number</th>
                    <th>Departure Time</th>
                    <th>Arrival Time</th>
                </tr>
                <?php foreach ($timetable_rows as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['plate_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['departure_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['arrival_time']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <p class="no-records">No records found for selected criteria.</p>
        <?php endif; ?>
    </div>

    <div class="sidebar">
        <div class="clock-container">
            <div class="clock" id="analog-clock">
                <div class="hand hour" id="hour-hand"></div>
                <div class="hand minute" id="minute-hand"></div>
                <div class="hand second" id="second-hand"></div>
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    $angle = deg2rad(($i - 3) * 30); // position around clock
                    $x = 100 + cos($angle) * 80;
                    $y = 100 + sin($angle) * 80;
                    echo "<div class='number' style='left:{$x}px;top:{$y}px;'>{$i}</div>";
                }
                ?>
            </div>
            <div id="live-clock">--:--:--</div>
        </div>

        <div class="calendar-box">
            <h3>📅 <?php echo date('F Y'); ?></h3>
            <table>
                <tr>
                    <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day): ?>
                        <th><?php echo $day; ?></th>
                    <?php endforeach; ?>
                </tr>
                <?php
                $today = date('j');
                $firstDay = date('w', strtotime(date('Y-m-01')));
                $daysInMonth = date('t');
                $dayCount = 1;

                for ($row = 0; $dayCount <= $daysInMonth; $row++) {
                    echo "<tr>";
                    for ($col = 0; $col < 7; $col++) {
                        if ($row == 0 && $col < $firstDay) {
                            echo "<td></td>";
                        } elseif ($dayCount <= $daysInMonth) {
                            $class = ($dayCount == $today) ? "today" : "";
                            echo "<td class='$class'>$dayCount</td>";
                            $dayCount++;
                        } else {
                            echo "<td></td>";
                        }
                    }
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>
</div>

<script>
function updateAnalogClock() {
    const now = new Date();

    const seconds = now.getSeconds();
    const minutes = now.getMinutes();
    const hours = now.getHours();

    const secondsAngle = seconds * 6;
    const minutesAngle = minutes * 6 + seconds * 0.1;
    const hoursAngle = ((hours % 12) / 12) * 360 + (minutes / 60) * 30;

    document.getElementById('second-hand').style.transform = `rotate(${secondsAngle}deg)`;
    document.getElementById('minute-hand').style.transform = `rotate(${minutesAngle}deg)`;
    document.getElementById('hour-hand').style.transform = `rotate(${hoursAngle}deg)`;

    document.getElementById("live-clock").textContent = now.toLocaleTimeString();
}

setInterval(updateAnalogClock, 1000);
updateAnalogClock();
</script>
</body>
</html>