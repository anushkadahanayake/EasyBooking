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

// Get all buses data
$buses_result = $conn->query("SELECT * FROM buses ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Buses - Admin Dashboard</title>
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
        }
        
        .back-btn {
            background-color: #2c3e50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .back-btn:hover {
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
            nav ul {
                flex-direction: column;
                gap: 5px;
                margin-top: 10px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>All Buses</h1>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="bus_managment.php">Manage Buses</a></li>
                    <li><a href="manage_users.php">Manage Users</a></li>
                    <li><a href="manage_route.php">Manage Routes</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
        
        <div class="section">
            <h2>All Buses</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Plate Number</th>
                        <th>Seat Count</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($bus = $buses_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $bus['id']; ?></td>
                        <td><?php echo htmlspecialchars($bus['plate_number']); ?></td>
                        <td><?php echo $bus['seat_count']; ?></td>
                        <td><?php echo isset($bus['status']) ? ucfirst($bus['status']) : 'Active'; ?></td>
                        <td>
                            <a href="bus_managment.php?bus_id=<?php echo $bus['id']; ?>" class="action-link">Manage</a>
                            <a href="delete_bus.php?id=<?php echo $bus['id']; ?>" class="action-link delete" onclick="return confirm('Are you sure you want to delete this bus?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>