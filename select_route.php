<?php
session_start();
include "config/db.php";
include "header.php";
?>

<div class="container">
  <h2>Select Your Route</h2>
  <form method="GET" action="select_date.php">
    <label>Route:</label>
    <select name="route_id" required>
      <option value="">-- Select Route --</option>
      <?php
      $routes = mysqli_query($conn, "SELECT * FROM routes");
      while ($route = mysqli_fetch_assoc($routes)) {
        echo "<option value='{$route['id']}'>{$route['route_name']}</option>";
      }
      ?>
    </select>

    <label>From:</label>
    <input type="text" name="from" required>

    <label>To:</label>
    <input type="text" name="to" required>

    <input type="submit" value="Next">
  </form>
</div>

<?php include "footer.php"; ?>
