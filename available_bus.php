<?php
session_start();
include "config/db.php";
include "header.php";

$route_id = $_GET['route_id'];
$from = $_GET['from'];
$to = $_GET['to'];
$booking_date = $_GET['booking_date'];

$buses = mysqli_query($conn, "SELECT * FROM buses 
  WHERE route_id = $route_id AND from_location = '$from' AND to_location = '$to'");
?>

<div class="container">
  <h2>Available Buses on <?= $booking_date ?></h2>
  <?php while ($bus = mysqli_fetch_assoc($buses)) { ?>
    <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
      <p>Bus: <?= $bus['plate_number'] ?> | Departure: <?= $bus['departure_time'] ?></p>
      <a href="select_seat.php?bus_id=<?= $bus['id'] ?>&date=<?= $booking_date ?>">View & Book Seats</a>
    </div>
  <?php } ?>
</div>

<?php include "footer.php"; ?>
