<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "config/db.php";

// Check if user is authorized
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'timetable_updater') {
    header("Location: login.php");
    exit();
}

// Check if timetable ID is provided
if (!isset($_GET['timetable_id'])) {
    die("Timetable ID not provided.");
}

$timetable_id = $_GET['timetable_id'];

// Fetch current timetable details
$sql = "SELECT * FROM timetable WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $timetable_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Timetable entry not found.");
}

$timetable = $result->fetch_assoc();

// Fetch all buses to populate dropdown
$bus_sql = "SELECT id, plate_number FROM buses";
$bus_result = $conn->query($bus_sql);

$success_msg = "";
$error_msg = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bus_id = $_POST['bus_id'];
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];

    $update_sql = "UPDATE timetable SET bus_id = ?, departure_time = ?, arrival_time = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("issi", $bus_id, $departure_time, $arrival_time, $timetable_id);

    if ($update_stmt->execute()) {
        $success_msg = "Timetable updated successfully!";
        // Refresh timetable data after update to show new values
        $stmt->execute();
        $result = $stmt->get_result();
        $timetable = $result->fetch_assoc();
        // Also reload buses list to ensure it is fresh
        $bus_result = $conn->query($bus_sql);
    } else {
        $error_msg = "Error updating timetable: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Timetable</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 20px;
        }

        .form-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            color: #2c3e50;
            text-align: center;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="time"],
        select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            margin-top: 20px;
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #1e8449;
        }

        a {
            display: inline-block;
            margin-top: 15px;
            color: #2980b9;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .success-msg {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
            margin-bottom: 15px;
            text-align: center;
        }

        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Update Timetable</h2>

    <?php if ($success_msg): ?>
        <div class="success-msg"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="bus_id">Bus Plate Number:</label>
        <select name="bus_id" id="bus_id" required>
            <?php while ($bus = $bus_result->fetch_assoc()): ?>
                <option value="<?php echo $bus['id']; ?>"
                    <?php if ($bus['id'] == $timetable['bus_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($bus['plate_number']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="departure_time">Departure Time:</label>
        <input type="time" name="departure_time" id="departure_time" value="<?php echo date('H:i', strtotime($timetable['departure_time'])); ?>" required>

        <label for="arrival_time">Arrival Time:</label>
        <input type="time" name="arrival_time" id="arrival_time" value="<?php echo date('H:i', strtotime($timetable['arrival_time'])); ?>" required>

        <button type="submit">Update Timetable</button>
    </form>

    <a href="timetable_update_dashboard.php">← Back to Dashboard</a>
</div>

</body>
</html>
