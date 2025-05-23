<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', 1);
session_start();
include "config/db.php";

// Admin access check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Add new route if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_location = trim($_POST['start_location']);
    $end_location = trim($_POST['end_location']);
    $route_number = trim($_POST['route_number']);

    if (!empty($start_location) && !empty($end_location) && !empty($route_number)) {
        $stmt = $conn->prepare("INSERT INTO routes (start_location, end_location, route_number) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $start_location, $end_location, $route_number);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all routes
$routes_result = $conn->query("SELECT id, start_location, end_location, route_number FROM routes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Routes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 15px;
            padding: 0;
            margin: 0;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 3px;
            transition: background-color 0.3s;
        }

        nav a:hover {
            background-color: #34495e;
        }

        .section {
            background: white;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        h2 {
            margin-top: 0;
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            color: #7f8c8d;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            width: 150px;
            background-color: #2c3e50;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #34495e;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            nav ul {
                flex-direction: column;
                gap: 8px;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Manage Routes</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="manage_users.php">Manage users</a></li>
                <li><a href="bus_managment.php">Manage Buses</a></li>
                <li><a href="manage_route.php">Manage Routes</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>

    <div class="section">
        <h2>Add New Route</h2>
        <form method="POST">
            <input type="text" name="start_location" placeholder="Start Location" required>
            <input type="text" name="end_location" placeholder="End Location" required>
            <input type="text" name="route_number" placeholder="Route Number" required>
            <input type="submit" value="Add Route">
        </form>
    </div>

    <div class="section">
        <h2>Existing Routes</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Start Location</th>
                    <th>End Location</th>
                    <th>Route Number</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($route = $routes_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $route['id']; ?></td>
                        <td><?php echo htmlspecialchars($route['start_location']); ?></td>
                        <td><?php echo htmlspecialchars($route['end_location']); ?></td>
                        <td><?php echo htmlspecialchars($route['route_number']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
