<?php
session_start();
include "config/db.php";

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
  echo "Login required.";
  exit;
}

if (isset($_POST['book'])) {
  $bus_id = $_POST['bus_id'];
  $booking_date = $_POST['booking_date'];
  $seat_number = $_POST['seat_number'];

  $check = mysqli_query($conn, "SELECT * FROM bookings 
    WHERE bus_id = $bus_id AND booking_date = '$booking_date' AND seat_number = $seat_number");

  if (mysqli_num_rows($check) > 0) {
    echo "Seat already booked.";
  } else {
    $insert = mysqli_query($conn, "INSERT INTO bookings (user_id, bus_id, booking_date, seat_number, status) 
      VALUES ($user_id, $bus_id, '$booking_date', $seat_number, 'pending')");
    echo $insert ? "Booking Confirmed!" : "Booking Failed.";
  }
}
?>
