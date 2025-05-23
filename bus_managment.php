<?php
session_start();
include "config/db.php";

// Admin access check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Search logic
$searchQuery = "";
if (isset($_GET['search'])) {
    $term = $conn->real_escape_string($_GET['search']);
    $searchQuery = "WHERE id LIKE '%$term%' OR plate_number LIKE '%$term%'";
}

$buses = $conn->query("SELECT * FROM buses $searchQuery ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Buses</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1100px;
            margin: auto;
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
        nav ul {
            list-style: none;
            display: flex;
            gap: 15px;
            margin: 0;
            padding: 0;
        }
        nav a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 3px;
        }
        nav a:hover {
            background-color: #34495e;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"],
        input[type="number"] {
            padding: 8px;
            margin-right: 10px;
            width: 200px;
        }
        button {
            padding: 8px 12px;
            background-color: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
        }
        button:hover {
            background-color: #34495e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .action-link {
            text-decoration: none;
            margin-right: 10px;
            color: #2980b9;
            cursor: pointer;
        }
        .action-link.delete {
            color: #e74c3c;
        }
        .section {
            margin-top: 40px;
            background: white;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .message {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    </style>

    <script>
        function enableEdit(rowId) {
            const row = document.getElementById('row-' + rowId);
            const cells = row.querySelectorAll('[data-field]');
            const data = {};

            cells.forEach(cell => {
                const field = cell.getAttribute('data-field');
                data[field] = cell.textContent.trim();
            });

            row.innerHTML = `
                <form method="post" action="update_bus.php" style="display: contents;">
                    <td>
                        <input type="hidden" name="id" value="${data.id}">
                        ${data.id}
                    </td>
                    <td><input type="text" name="plate_number" value="${data.plate_number}" required></td>
                    <td><input type="number" name="seat_count" value="${data.seat_count}" required></td>
                    <td><input type="number" name="owner_id" value="${data.owner_id}" required></td>
                    <td><input type="number" name="route_id" value="${data.route_id}" required></td>
                    <td><input type="number" name="driver_id" value="${data.driver_id}" required></td>
                    <td><input type="number" name="conductor_id" value="${data.conductor_id}" required></td>
                    <td>
                        ${data.created_at}
                        <input type="hidden" name="created_at" value="${data.created_at}">
                    </td>
                    <td>
                        <button type="submit">Save</button>
                        <a class="action-link delete" href="bus_managment.php">Cancel</a>
                    </td>
                </form>
            `;
        }
    </script>
</head>
<body>
<div class="container">

    <!-- ✅ Success/Error Messages -->
  <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="message success">✅ Bus updated successfully!</div>
<?php elseif (isset($_GET['error']) && $_GET['error'] == 'update_failed'): ?>
    <div class="message error">❌ Failed to update the bus. Please try again.</div>
<?php elseif (isset($_GET['error']) && $_GET['error'] == 'invalid'): ?>
    <div class="message warning">⚠️ Invalid input. Please fill all fields correctly.</div>
<?php endif; ?>

    <!-- Header -->
    <div class="header">
        <h1>Manage Buses</h1>
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

    <!-- Search -->
    <div class="section">
        <h2>Search Buses</h2>
        <form method="get">
            <input type="text" name="search" placeholder="Search by ID or Plate No" value="<?php echo $_GET['search'] ?? ''; ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- Add Bus -->
    <div class="section">
        <h2>Add New Bus</h2>
        <form method="post" action="add_bus.php">
            <input type="text" name="plate_number" placeholder="Plate Number" required>
            <input type="number" name="seat_count" placeholder="Seat Count" required>
            <input type="number" name="owner_id" placeholder="Owner ID" required>
            <input type="number" name="route_id" placeholder="Route ID" required>
            <input type="number" name="driver_id" placeholder="Driver ID" required>
            <input type="number" name="conductor_id" placeholder="Conductor ID" required>
            <button type="submit">Add Bus</button>
        </form>
    </div>

    <!-- List Buses -->
    <div class="section">
        <h2>Existing Buses</h2>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Plate Number</th>
                <th>Seats</th>
                <th>Owner</th>
                <th>Route</th>
                <th>Driver</th>
                <th>Conductor</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($bus = $buses->fetch_assoc()): ?>
                <tr id="row-<?= $bus['id'] ?>">
                    <td data-field="id"><?= $bus['id'] ?></td>
                    <td data-field="plate_number"><?= htmlspecialchars($bus['plate_number']) ?></td>
                    <td data-field="seat_count"><?= $bus['seat_count'] ?></td>
                    <td data-field="owner_id"><?= $bus['owner_id'] ?></td>
                    <td data-field="route_id"><?= $bus['route_id'] ?></td>
                    <td data-field="driver_id"><?= $bus['driver_id'] ?></td>
                    <td data-field="conductor_id"><?= $bus['conductor_id'] ?></td>
                    <td data-field="created_at"><?= $bus['created_at'] ?></td>
                    <td>
                        <span class="action-link" onclick="enableEdit(<?= $bus['id'] ?>)">Edit</span>
                        <a class="action-link delete" href="delete_bus.php?id=<?= $bus['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
