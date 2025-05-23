<?php
session_start();
include "config/db.php";

// Admin access check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: manage_users.php?deleted=1");
    exit();
}

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    $stmt->execute();
    header("Location: manage_users.php?created=1");
    exit();
}

// Search logic
$searchQuery = "";
if (isset($_GET['search'])) {
    $term = $conn->real_escape_string($_GET['search']);
    $searchQuery = "WHERE id LIKE '%$term%' OR name LIKE '%$term%' OR email LIKE '%$term%'";
}

$users = $conn->query("SELECT * FROM users $searchQuery ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
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
        input[type="email"],
        input[type="password"],
        select {
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
                <form method="post" action="update_user.php" style="display: contents;">
                    <td>
                        <input type="hidden" name="id" value="${data.id}">
                        ${data.id}
                    </td>
                    <td><input type="text" name="name" value="${data.name}" required></td>
                    <td><input type="email" name="email" value="${data.email}" required></td>
                    <td>
                        <select name="role" required>
                            <option value="customer" ${data.role === 'customer' ? 'selected' : ''}>Customer</option>
                            <option value="owner" ${data.role === 'owner' ? 'selected' : ''}>Owner</option>
                            <option value="driver" ${data.role === 'driver' ? 'selected' : ''}>Driver</option>
                            <option value="conductor" ${data.role === 'conductor' ? 'selected' : ''}>Conductor</option>
                            <option value="admin" ${data.role === 'admin' ? 'selected' : ''}>Admin</option>
                        </select>
                    </td>
                    <td>
                        ${data.created_at}
                        <input type="hidden" name="created_at" value="${data.created_at}">
                    </td>
                    <td>
                        <button type="submit">Save</button>
                        <a class="action-link delete" href="manage_users.php">Cancel</a>
                    </td>
                </form>
            `;
        }
    </script>
</head>
<body>
<div class="container">

    <!-- Success/Error Messages -->
    <?php if (isset($_GET['created'])): ?>
        <div class="message success">✅ User created successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="message success">✅ User deleted successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] == 'update_failed'): ?>
        <div class="message error">❌ Failed to update the user. Please try again.</div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid'): ?>
        <div class="message warning">⚠️ Invalid input. Please fill all fields correctly.</div>
    <?php endif; ?>

    <!-- Header -->
    <div class="header">
        <h1>Manage Users</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="bus_managment.php">Manage Buses</a></li>
                <li><a href="manage_route.php">Manage Routes</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>

    <!-- Search -->
    <div class="section">
        <h2>Search Users</h2>
        <form method="get">
            <input type="text" name="search" placeholder="Search by ID, name or email" value="<?php echo $_GET['search'] ?? ''; ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- Add User -->
    <div class="section">
        <h2>Add New User</h2>
        <form method="post" action="">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required>
                <option value="customer">Customer</option>
                <option value="owner">Owner</option>
                <option value="driver">Driver</option>
                <option value="conductor">Conductor</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" name="create_user">Add User</button>
        </form>
    </div>

    <!-- List Users -->
    <div class="section">
        <h2>Existing Users</h2>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($user = $users->fetch_assoc()): ?>
                <tr id="row-<?= $user['id'] ?>">
                    <td data-field="id"><?= $user['id'] ?></td>
                    <td data-field="name"><?= htmlspecialchars($user['name']) ?></td>
                    <td data-field="email"><?= htmlspecialchars($user['email']) ?></td>
                    <td data-field="role"><?= ucfirst($user['role']) ?></td>
                    <td data-field="created_at"><?= $user['created_at'] ?></td>
                    <td>
                        <span class="action-link" onclick="enableEdit(<?= $user['id'] ?>)">Edit</span>
                        <a class="action-link delete" href="manage_users.php?delete_id=<?= $user['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>