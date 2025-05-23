<?php
session_start();
include "config/db.php";
include "header.php";

$route_id = $_GET['route_id'];
$from = $_GET['from'];
$to = $_GET['to'];

$min_date = date('Y-m-d', strtotime('+1 day'));
$max_date = date('Y-m-d', strtotime('+14 days'));
?>

<div class="container">
  <h2>Select Date</h2>
  <form method="GET" action="available_buses.php">
    <input type="hidden" name="route_id" value="<?= $route_id ?>">
    <input type="hidden" name="from" value="<?= $from ?>">
    <input type="hidden" name="to" value="<?= $to ?>">

    <label>Date:</label>
    <input type="date" name="booking_date" min="<?= $min_date ?>" max="<?= $max_date ?>" required>

    <input type="submit" value="Find Buses">
  </form>
</div>

<?php include "footer.php"; ?>
