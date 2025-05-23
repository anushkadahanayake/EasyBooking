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

// Get metrics data
$total_bookings = $conn->query("SELECT COUNT(*) AS total FROM bookings")->fetch_assoc()['total'];
$total_revenue = $conn->query("SELECT SUM(amount) AS total FROM payments")->fetch_assoc()['total'] ?? 0;
$users_result = $conn->query("SELECT * FROM users ORDER BY id DESC LIMIT 5");
$buses_result = $conn->query("SELECT * FROM buses ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            color: #333;
        }

        .container {
            max-width: 1200px;
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
            padding: 0;
            margin: 0;
            display: flex;
            gap: 15px;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 3px;
            transition: background-color 0.3s;
        }

        nav a:hover {
            background-color: #34495e;
        }

        .metrics {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .metric-card {
            flex: 1;
            min-width: 200px;
            background: white;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .metric-card h3 {
            margin-top: 0;
            color: #7f8c8d;
            font-size: 16px;
        }

        .metric-card p {
            font-size: 28px;
            margin: 10px 0 0;
            color: #2c3e50;
            font-weight: bold;
        }

        .section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .section h2 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .view-all-btn {
            background-color: #2c3e50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
        }

        .view-all-btn:hover {
            background-color: #34495e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #7f8c8d;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .action-link {
            color: #3498db;
            text-decoration: none;
            margin-right: 10px;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        .action-link.delete {
            color: #e74c3c;
        }

        @media (max-width: 768px) {
            .metrics {
                flex-direction: column;
            }

            .metric-card {
                min-width: 100%;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            nav ul {
                flex-direction: column;
                gap: 5px;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="manage_users.php">Manage users</a></li>
                    <li><a href="bus_managment.php">Manage Buses</a></li>
                    <li><a href="manage_route.php">Manage Route</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <div class="metrics">
            <div class="metric-card">
                <h3>Total Bookings</h3>
                <p><?php echo $total_bookings; ?></p>
            </div>
            <div class="metric-card">
                <h3>Total Revenue</h3>
                <p>Rs. <?php echo number_format($total_revenue, 2); ?></p>
            </div>
        </div>

        <div class="section">
            <h2>Recent Users <a href="all_users.php" class="view-all-btn">View All</a></h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo ucfirst($user['role']); ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="action-link">Edit</a>
                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="action-link delete" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Recent Buses <a href="all_buses.php" class="view-all-btn">View All</a></h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Plate No.</th>
                        <th>Seats</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($bus = $buses_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $bus['id']; ?></td>
                        <td><?php echo htmlspecialchars($bus['plate_number']); ?></td>
                        <td><?php echo $bus['seat_count']; ?></td>
                        <td>
                            <a href="edit_bus.php?id=<?php echo $bus['id']; ?>" class="action-link">Edit</a>
                            <a href="delete_bus.php?id=<?php echo $bus['id']; ?>" class="action-link delete" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
