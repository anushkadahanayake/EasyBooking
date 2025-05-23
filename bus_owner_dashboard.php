<?php
session_start();
include "config/db.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'bus_owner') {
    header("Location: login.php");
    exit;
}

$owner_id = $_SESSION['user_id'];

// Handle date filter inputs
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$date_filter_sql = '';
$types = 'i';
$params = [$owner_id];

if (!empty($from_date)) {
    $date_filter_sql .= " AND payments.paid_at >= ? ";
    $types .= 's';
    $params[] = $from_date . " 00:00:00";
}
if (!empty($to_date)) {
    $date_filter_sql .= " AND payments.paid_at <= ? ";
    $types .= 's';
    $params[] = $to_date . " 23:59:59";
}

$sql = "
    SELECT 
        buses.id AS bus_id,
        buses.plate_number,
        COALESCE(SUM(payments.amount), 0) AS total_revenue
    FROM buses
    LEFT JOIN bookings ON buses.id = bookings.bus_id
    LEFT JOIN payments ON bookings.booking_id = payments.booking_id 
        AND payments.payment_status = 'paid'
        $date_filter_sql
    WHERE buses.owner_id = ?
    GROUP BY buses.id, buses.plate_number
    ORDER BY buses.plate_number ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$total_revenue = 0;
$buses = [];

while ($row = $result->fetch_assoc()) {
    $buses[] = $row;
    $total_revenue += $row['total_revenue'];
}

$stmt->close();
$conn->close();

// CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=revenue_report.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Plate Number', 'Total Revenue ($)']);
    foreach ($buses as $bus) {
        fputcsv($output, [$bus['plate_number'], number_format($bus['total_revenue'], 2)]);
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Bus Owner Dashboard - Revenue</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 40px;
        background: #f4f6f8;
        color: #333;
    }
    .dashboard-container {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgb(0 0 0 / 0.1);
    }
    h2, h3 {
        color: #222;
        margin-bottom: 0.4em;
    }
    .filter-form {
        margin-bottom: 20px;
        background: #f9fafb;
        padding: 15px 20px;
        border-radius: 6px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }
    .filter-form label {
        font-weight: 600;
    }
    .filter-form input[type="date"] {
        padding: 6px 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .filter-form button {
        padding: 8px 16px;
        background-color: #007bff;
        border: none;
        color: white;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }
    .filter-form button:hover {
        background-color: #0056b3;
    }
    .filter-form a {
        color: #555;
        text-decoration: none;
        margin-left: 10px;
        font-weight: 600;
    }
    .filter-form a:hover {
        text-decoration: underline;
    }
    .revenue-summary {
        font-size: 1.1rem;
        margin-bottom: 20px;
        font-weight: 700;
    }
    table.revenue-table {
        width: 100%;
        border-collapse: collapse;
    }
    table.revenue-table th, table.revenue-table td {
        border: 1px solid #ddd;
        padding: 12px 15px;
        text-align: left;
    }
    table.revenue-table th {
        background-color: #f0f0f0;
        font-weight: 700;
    }
    table.revenue-table tr:nth-child(even) {
        background-color: #fafafa;
    }
    table.revenue-table tr:hover {
        background-color: #f1f5fb;
    }
    @media (max-width: 600px) {
        .filter-form {
            flex-direction: column;
            align-items: flex-start;
        }
        .filter-form button, .filter-form a {
            margin-left: 0;
        }
    }
</style>
</head>
<body>

<?php include "header.php"; ?>

<div class="dashboard-container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>

    <h3>Revenue Report</h3>

    <form class="filter-form" method="get">
        <label for="from_date">From:</label>
        <input type="date" id="from_date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>" />

        <label for="to_date">To:</label>
        <input type="date" id="to_date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>" />

        <button type="submit">Filter</button>
        <button type="submit" name="export" value="csv">Download CSV</button>
        <a href="bus_owner_dashboard.php">Clear</a>
    </form>

    <p class="revenue-summary">
        Total Revenue<?php if ($from_date || $to_date) echo " (Filtered)"; ?>:
        $<?php echo number_format($total_revenue, 2); ?>
    </p>

    <table class="revenue-table">
        <thead>
            <tr>
                <th>Plate Number</th>
                <th>Revenue ($)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($buses)): ?>
                <?php foreach ($buses as $bus): ?>
                <tr>
                    <td><?php echo htmlspecialchars($bus['plate_number']); ?></td>
                    <td><?php echo number_format($bus['total_revenue'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="2">No revenue found for the selected period.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include "footer.php"; ?>

</body>
</html>

