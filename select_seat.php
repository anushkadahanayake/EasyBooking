<?php
session_start();
include "config/db.php";
include "header.php";

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
  echo "Login required.";
  exit;
}

$bus_id = $_GET['bus_id'];
$booking_date = $_GET['date'];

$bus = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM buses WHERE id = $bus_id"));
$total_seats = $bus['seat_count'];

$booked = mysqli_query($conn, "SELECT seat_number FROM bookings WHERE bus_id = $bus_id AND booking_date = '$booking_date'");
$booked_seats = [];
while ($row = mysqli_fetch_assoc($booked)) {
  $booked_seats[] = $row['seat_number'];
}
?>

<div class="container">
  <h2>Select Seat (<?= $bus['plate_number'] ?>)</h2>
  <form method="POST" action="confirm_booking.php">
    <input type="hidden" name="bus_id" value="<?= $bus_id ?>">
    <input type="hidden" name="booking_date" value="<?= $booking_date ?>">

    <label>Available Seats:</label><br>
    <select name="seat_number" required>
      <?php
      for ($i = 1; $i <= $total_seats; $i++) {
        if (!in_array($i, $booked_seats)) {
          echo "<option value='$i'>Seat $i</option>";
        }
      }
      ?>
    </select>

    <input type="submit" name="book" value="Confirm Booking">
  </form>
</div>

<?php include "footer.php"; ?>
