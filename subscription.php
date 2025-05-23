<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

include "header.php";
include "config/db.php";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details
$user_query = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user_data = $user_result->fetch_assoc();
$user_name = $user_data['name'] ?? "User";
$user_email = $user_data['email'] ?? "user@example.com";
$user_phone = $user_data['phone'] ?? "Not provided";

// Check subscription
$subscription_query = $conn->prepare("SELECT plan, start_date, renewal_date FROM subscriptions WHERE user_id = ? AND renewal_date >= CURDATE()");
$subscription_query->bind_param("i", $user_id);
$subscription_query->execute();
$subscription_result = $subscription_query->get_result();

$subscription_active = false;
$current_plan = "free";
$subscription_start = date("Y-m-d");
$renewal_date = date("Y-m-d", strtotime("+1 month"));

if ($subscription_result->num_rows > 0) {
    $data = $subscription_result->fetch_assoc();
    $subscription_active = true;
    $current_plan = $data['plan'];
    $subscription_start = $data['start_date'];
    $renewal_date = $data['renewal_date'];
}

// Handle subscription
if (isset($_POST['subscribe'])) {
    $selected_plan = $_POST['subscribe'];
    $start_date = date("Y-m-d");
    $renewal_date = date("Y-m-d", strtotime("+1 month"));

    $check_sub = $conn->prepare("SELECT id FROM subscriptions WHERE user_id = ?");
    $check_sub->bind_param("i", $user_id);
    $check_sub->execute();
    $check_result = $check_sub->get_result();

    if ($check_result->num_rows > 0) {
        $update = $conn->prepare("UPDATE subscriptions SET plan = ?, start_date = ?, renewal_date = ? WHERE user_id = ?");
        $update->bind_param("sssi", $selected_plan, $start_date, $renewal_date, $user_id);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO subscriptions (user_id, plan, start_date, renewal_date) VALUES (?, ?, ?, ?)");
        $insert->bind_param("isss", $user_id, $selected_plan, $start_date, $renewal_date);
        $insert->execute();
    }

    $current_plan = $selected_plan;
    $_SESSION['message'] = $selected_plan === 'paid' ? "Subscription activated successfully!" : "Plan changed to Free.";
    header("Location: subscription.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Subscription Plans</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"/>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }
    .subscription-container {
      max-width: 800px;
      margin: 50px auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .subscription-header {
      text-align: center;
      margin-bottom: 30px;
    }
    .plans-container {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 20px;
    }
    .plan-card {
      width: 48%;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 8px;
      background-color: #fdfdfd;
    }
    .plan-title {
      font-size: 24px;
      margin-bottom: 10px;
    }
    .plan-price {
      font-size: 20px;
      margin-bottom: 20px;
    }
    .plan-features ul {
      list-style: none;
      padding: 0;
    }
    .plan-features li {
      margin-bottom: 10px;
    }
    .plan-features .cross {
      text-decoration: line-through;
      color: #999;
    }
    .subscribe-btn {
      padding: 10px 20px;
      background-color: #007bff;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .subscribe-btn[disabled] {
      background-color: #ccc;
      cursor: not-allowed;
    }
    .current-plan-tag {
      margin-top: 10px;
      font-weight: bold;
      color: green;
    }
    .thankyou-container {
      margin-top: 40px;
      text-align: center;
    }
    .thankyou-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    .thankyou-table th, .thankyou-table td {
      border: 1px solid #ddd;
      padding: 10px;
    }
    .message {
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 4px;
    }
    .message.success {
      background-color: #d4edda;
      color: #155724;
    }
    .message.error {
      background-color: #f8d7da;
      color: #721c24;
    }
  </style>
</head>

<body>

<div class="subscription-container">
  <?php if (isset($_SESSION['message'])): ?>
    <div class="message success">
      <?php echo $_SESSION['message']; ?>
    </div>
    <?php unset($_SESSION['message']); ?>
  <?php endif; ?>

  <div class="subscription-header">
    <h1>Choose Your Plan</h1>
    <p>Select the subscription plan that works best for you. Upgrade, downgrade, or cancel anytime.</p>
  </div>

  <form action="" method="post">
    <div class="plans-container">
      <!-- Free Plan Card -->
      <div class="plan-card free">
        <h2 class="plan-title">Free Plan</h2>
        <div class="plan-price">Rs 0 <span>/ month</span></div>
        <div class="plan-features">
          <ul>
            <li>View Bus Timetable</li>
            <li class="cross">Real-Time Bus Tracking</li>
            <li class="cross">Seat Booking</li>
            <li>Basic Customer Support</li>
            <li>Email Notifications</li>
          </ul>
        </div>
        <!-- Free Plan Button -->
        <button type="submit" name="subscribe" class="subscribe-btn" value="free" <?php echo $current_plan == "free" ? 'disabled' : ''; ?>>
          <?php echo $current_plan == "free" ? 'Current Plan' : 'Select Free Plan'; ?>
      </button>
        <?php if ($current_plan == "free"): ?>
          <div class="current-plan-tag">Your Current Plan</div>
        <?php endif; ?>
      </div>

      <!-- Premium Plan Card -->
      <div class="plan-card premium">
        <h2 class="plan-title">Premium Plan</h2>
        <div class="plan-price">Rs 599.00 <span>/ month</span></div>
        <div class="plan-features">
          <ul>
            <li>View Bus Timetable</li>
            <li>Real-Time Bus Tracking</li>
            <li>Seat Booking</li>
            <li>Priority Customer Support</li>
            <li>Email & SMS Notifications</li>
            <li>Exclusive Discounts</li>
          </ul>
        </div>
        <button type="submit" name="subscribe" class="subscribe-btn" value="paid" <?php echo $current_plan == "paid" ? 'disabled' : ''; ?>>
          <?php echo $current_plan == "paid" ? 'Current Plan' : 'Get Premium'; ?>
        </button>
        <?php if ($current_plan == "paid"): ?>
          <div class="current-plan-tag">Your Current Plan</div>
        <?php endif; ?>
        <input type="hidden" name="plan" value="paid">
      </div>
    </div>
  </form>

  <div class="thankyou-container">
    <h2>Your Subscription Details</h2>
    <table class="thankyou-table">
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Plan</th>
        <th>Start Date</th>
        <th>Renewal Date</th>
      </tr>
      <tr>
        <td><?php echo htmlspecialchars($user_name); ?></td>
        <td><?php echo htmlspecialchars($user_email); ?></td>
        <td><?php echo ucfirst($current_plan); ?> Plan</td>
        <td><?php echo $subscription_start; ?></td>
        <td><?php echo $renewal_date; ?></td>
      </tr>
    </table>
  </div>
</div>

<?php include "footer.php"; ?>
</body>
</html>
