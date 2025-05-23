<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit;
}

include "config/db.php";  // Your DB connection

$user_id = $_SESSION['user_id'];

// Get user name from users table
$sql_user = "SELECT name FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user && $result_user->num_rows > 0) {
    $row_user = $result_user->fetch_assoc();
    $name = $row_user['name'];
} else {
    $name = "User";
}

// Get subscription info from subscriptions table
$sql_sub = "SELECT plan, renewal_date FROM subscriptions WHERE user_id = ? ORDER BY renewal_date DESC LIMIT 1";
$stmt_sub = $conn->prepare($sql_sub);
$stmt_sub->bind_param("i", $user_id);
$stmt_sub->execute();
$result_sub = $stmt_sub->get_result();

if ($result_sub && $result_sub->num_rows > 0) {
    $row_sub = $result_sub->fetch_assoc();
    $subscription_status = $row_sub['plan'];           // 'free' or 'paid'
    $renewal_date = $row_sub['renewal_date'];
} else {
    // Default if no subscription found
    $subscription_status = 'free';
    $renewal_date = date('Y-m-d', strtotime('+30 days'));
}

include "header.php";
?>

<style>
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background: #f0f2f5;
    }

    .dashboard-container {
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
        padding: 40px 30px;
        box-sizing: border-box;
    }

    .welcome {
        font-size: 28px;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
        text-align: center;
    }

    .datetime-container {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        justify-content: center;
        margin-bottom: 40px;
    }

    .card {
        background: #fff;
        padding: 30px 25px;
        flex: 1 1 300px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        min-width: 280px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-align: center;
    }

    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0, 123, 255, 0.3);
    }

    .card h3 {
        margin-bottom: 15px;
        color: #007bff;
    }

    .card p {
        font-size: 16px;
        margin-bottom: 15px;
    }

    .highlight {
        background: #e6f7ff;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        color: #007bff;
    }

    .link-button {
        display: inline-block;
        margin-top: 10px;
        padding: 12px 24px;
        background: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
        transition: background 0.3s ease, box-shadow 0.3s ease;
    }

    .link-button:hover {
        background: #0056b3;
        box-shadow: 0 6px 15px rgba(0, 86, 179, 0.5);
    }

    .clock {
        font-size: 36px;
        font-weight: bold;
        color: #28a745;
    }

    .calendar {
        font-size: 20px;
        font-weight: bold;
        color: #ff6347;
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 20px 15px;
        }
        .datetime-container {
            flex-direction: column;
            gap: 20px;
        }
    }
</style>

<div class="dashboard-container">
    <div class="welcome">Welcome, <?php echo htmlspecialchars($name); ?> 👋</div>

    <div class="datetime-container">
        <div class="card">
            <h3>🕒 Current Time</h3>
            <div class="clock" id="clock">--:--:--</div>
        </div>
        <div class="card">
            <h3>📅 Today’s Date</h3>
            <div class="calendar" id="calendar"><?php echo date('l, F j, Y'); ?></div>
        </div>
    </div>

    <div class="datetime-container">
        <div class="card">
            <h3>💼 Subscription</h3>
            <p>Current Plan: <span class="highlight"><?php echo ucfirst(htmlspecialchars($subscription_status)); ?> Plan</span></p>
            <p>Next Renewal Date: <strong><?php echo htmlspecialchars($renewal_date); ?></strong></p>
            <?php if ($subscription_status !== 'paid'): ?>
                <a href="subscription.php" class="link-button">Upgrade to Premium</a>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>🚌 Timetable</h3>
            <p>Check available routes and departure times.</p>
            <a href="timetable.php" class="link-button">View Timetable</a>
        </div>
    </div>

    <div class="datetime-container">
        <div class="card">
            <h3>📖 Booking History</h3>
            <p>View your previous seat bookings.</p>
            <a href="booking_history.php" class="link-button">View History</a>
        </div>

        <div class="card">
            <h3>🎟️ Book a Seat</h3>
            <?php if ($subscription_status === 'paid'): ?>
                <p>You are eligible to book a seat.</p>
                <a href="booking.php" class="link-button">Book Now</a>
            <?php else: ?>
                <p>You need to subscribe to book seats.</p>
                <a href="subscription.php" class="link-button">Subscribe Now</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();
        const time = now.toLocaleTimeString();
        document.getElementById('clock').textContent = time;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

<?php include "footer.php"; ?>
