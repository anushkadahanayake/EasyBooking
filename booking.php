<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config/db.php"; // your DB connection
include "header.php";

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "<p>Please log in to book a seat.</p>";
    exit;
}

$today = date('Y-m-d');
$min_date = date('Y-m-d', strtotime('+1 day'));
$max_date = date('Y-m-d', strtotime('+14 days'));

// Check if user has active paid subscription
$sub_sql = "SELECT * FROM subscriptions 
            WHERE user_id = $user_id 
            AND plan = 'paid' 
            AND '$today' BETWEEN start_date AND renewal_date";
$sub_result = mysqli_query($conn, $sub_sql);
$is_subscribed = mysqli_num_rows($sub_result) > 0;

if (!$is_subscribed) {
    echo "<p style='
    color: white; 
    background: linear-gradient(90deg, #ff416c, #ff4b2b); 
    font-weight: bold; 
    font-size: 1.5em; 
    text-align: center; 
    padding: 20px; 
    border-radius: 10px; 
    width: 80%; 
    margin: 50px auto; 
    box-shadow: 0 0 15px rgba(255,75,43,0.7);
'>You must have an active paid subscription to book seats.</p>";

    exit;
}

// Step 1: Select route
if (!isset($_POST['route'])) {
    $routes = mysqli_query($conn, "SELECT id, start_location, end_location, route_number FROM routes");
?>
    <div class="booking-container">
        <div class="booking-progress">
            <div class="progress-step active">1. Select Route</div>
            <div class="progress-step">2. Select Stops</div>
            <div class="progress-step">3. Select Date</div>
            <div class="progress-step">4. Select Bus</div>
            <div class="progress-step">5. Select Seat</div>
        </div>
        
        <div class="booking-card">
            <h2><i class="fas fa-route"></i> Select Your Route</h2>
            <form method='POST' class="booking-form">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="form-group">
                    <label for="route">Choose a Route:</label>
                    <select name='route' id="route" required class="form-control">
                        <option value=''>-- Select Route --</option>
                        <?php while ($route = mysqli_fetch_assoc($routes)): ?>
                            <option value='<?= htmlspecialchars($route['id']) ?>'>
                                Route <?= htmlspecialchars($route['route_number']) ?>: 
                                <?= htmlspecialchars($route['start_location']) ?> to <?= htmlspecialchars($route['end_location']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Next <i class="fas fa-arrow-right"></i></button>
            </form>
        </div>
    </div>
<?php
// Step 2: Select from and to locations based on the selected route
} elseif (!isset($_POST['from']) || !isset($_POST['to'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    $route_id = intval($_POST['route']);

    // Fetch route info using prepared statement
    $route_res = mysqli_prepare($conn, "SELECT start_location, end_location FROM routes WHERE id = ?");
    mysqli_stmt_bind_param($route_res, "i", $route_id);
    mysqli_stmt_execute($route_res);
    $route_result = mysqli_stmt_get_result($route_res);
    $route = mysqli_fetch_assoc($route_result);
    mysqli_stmt_close($route_res);
?>
    <div class="booking-container">
        <div class="booking-progress">
            <div class="progress-step completed">1. Select Route</div>
            <div class="progress-step active">2. Select Stops</div>
            <div class="progress-step">3. Select Date</div>
            <div class="progress-step">4. Select Bus</div>
            <div class="progress-step">5. Select Seat</div>
        </div>
        
        <div class="booking-card">
            <h2><i class="fas fa-map-marker-alt"></i> Select Your Stops</h2>
            <form method='POST' class="booking-form">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type='hidden' name='route' value='<?= htmlspecialchars($route_id) ?>'>
                
                <div class="form-group">
                    <label for="from">Boarding Point:</label>
                    <select name='from' id="from" required class="form-control">
                        <option value=''>-- Select Boarding Point --</option>
                        <option value='<?= htmlspecialchars($route['start_location']) ?>'><?= htmlspecialchars($route['start_location']) ?></option>
                        <option value='<?= htmlspecialchars($route['end_location']) ?>'><?= htmlspecialchars($route['end_location']) ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="to">Destination:</label>
                    <select name='to' id="to" required class="form-control">
                        <option value=''>-- Select Destination --</option>
                        <option value='<?= htmlspecialchars($route['start_location']) ?>'><?= htmlspecialchars($route['start_location']) ?></option>
                        <option value='<?= htmlspecialchars($route['end_location']) ?>'><?= htmlspecialchars($route['end_location']) ?></option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <a href="booking.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                    <button type="submit" class="btn btn-primary">Next <i class="fas fa-arrow-right"></i></button>
                </div>
            </form>
        </div>
    </div>
<?php
}
// Step 3: Select date
elseif (!isset($_POST['travel_date'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    $route_id = intval($_POST['route']);
    $from_stop = htmlspecialchars($_POST['from']);
    $to_stop = htmlspecialchars($_POST['to']);
?>
    <div class="booking-container">
        <div class="booking-progress">
            <div class="progress-step completed">1. Select Route</div>
            <div class="progress-step completed">2. Select Stops</div>
            <div class="progress-step active">3. Select Date</div>
            <div class="progress-step">4. Select Bus</div>
            <div class="progress-step">5. Select Seat</div>
        </div>
        
        <div class="booking-card">
            <h2><i class="far fa-calendar-alt"></i> Select Travel Date</h2>
            <form method='POST' class="booking-form">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type='hidden' name='route' value='<?= htmlspecialchars($route_id) ?>'>
                <input type='hidden' name='from' value='<?= htmlspecialchars($from_stop) ?>'>
                <input type='hidden' name='to' value='<?= htmlspecialchars($to_stop) ?>'>
                
                <div class="form-group">
                    <label for="travel_date">Travel Date (Available from <?= date('M j', strtotime($min_date)) ?> to <?= date('M j', strtotime($max_date)) ?>):</label>
                    <input type='date' 
                           name='travel_date' 
                           id="travel_date" 
                           min='<?= $min_date ?>' 
                           max='<?= $max_date ?>' 
                           required 
                           class="form-control"
                           value="<?= isset($_POST['travel_date']) ? htmlspecialchars($_POST['travel_date']) : '' ?>">
                    <small class="form-text text-muted">You can book up to 14 days in advance</small>
                </div>
                
                <div class="form-actions">
                    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                    <button type="submit" class="btn btn-primary">Next <i class="fas fa-arrow-right"></i></button>
                </div>
            </form>
        </div>
    </div>
<?php
}
// Step 4: Select bus
elseif (!isset($_POST['bus_id'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    // Validate travel date
    $travel_date = $_POST['travel_date'];
    if (!validateDate($travel_date, $min_date, $max_date)) {
        echo "<div class='container error-message'>
                <h2><i class='fas fa-exclamation-triangle'></i> Invalid Date</h2>
                <p>Please select a valid travel date between $min_date and $max_date.</p>
                <a href='javascript:history.back()' class='btn btn-primary'><i class='fas fa-arrow-left'></i> Go Back</a>
              </div>";
        include "footer.php";
        exit;
    }
    
    $route_id = intval($_POST['route']);
    $from_stop = htmlspecialchars($_POST['from']);
    $to_stop = htmlspecialchars($_POST['to']);
    $day_of_week = date('l', strtotime($travel_date));

    // Get available buses using prepared statement
    $query = "SELECT t.id AS timetable_id, t.bus_id, t.departure_time, t.arrival_time, b.plate_number
              FROM timetable t
              JOIN buses b ON t.bus_id = b.id
              WHERE t.route_id = ? AND t.day_of_week = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $route_id, $day_of_week);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
?>
    <div class="booking-container">
        <div class="booking-progress">
            <div class="progress-step completed">1. Select Route</div>
            <div class="progress-step completed">2. Select Stops</div>
            <div class="progress-step completed">3. Select Date</div>
            <div class="progress-step active">4. Select Bus</div>
            <div class="progress-step">5. Select Seat</div>
        </div>
        
        <div class="booking-card">
            <h2><i class="fas fa-bus"></i> Available Buses on <?= htmlspecialchars($day_of_week) ?></h2>
            <form method='POST' class="booking-form">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type='hidden' name='route' value='<?= htmlspecialchars($route_id) ?>'>
                <input type='hidden' name='from' value='<?= htmlspecialchars($from_stop) ?>'>
                <input type='hidden' name='to' value='<?= htmlspecialchars($to_stop) ?>'>
                <input type='hidden' name='travel_date' value='<?= htmlspecialchars($travel_date) ?>'>
                
                <div class="form-group">
                    <label for="bus_id">Select Bus:</label>
                    <select name='bus_id' id="bus_id" required class="form-control">
                        <option value=''>-- Select Bus --</option>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <option value='<?= htmlspecialchars($row['bus_id']) ?>'>
                                Bus <?= htmlspecialchars($row['plate_number']) ?> | Departure: <?= htmlspecialchars($row['departure_time']) ?> | Arrival: <?= htmlspecialchars($row['arrival_time']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                    <button type="submit" class="btn btn-primary">Next <i class="fas fa-arrow-right"></i></button>
                </div>
            </form>
        </div>
    </div>
<?php
    mysqli_stmt_close($stmt);
}
// Step 5: Select seat
elseif (!isset($_POST['seat_number'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    // Validate travel date again
    $travel_date = $_POST['travel_date'];
    if (!validateDate($travel_date, $min_date, $max_date)) {
        echo "<div class='container error-message'>
                <h2><i class='fas fa-exclamation-triangle'></i> Invalid Date</h2>
                <p>Please select a valid travel date between $min_date and $max_date.</p>
                <a href='javascript:history.back()' class='btn btn-primary'><i class='fas fa-arrow-left'></i> Go Back</a>
              </div>";
        include "footer.php";
        exit;
    }
    
    $route_id = intval($_POST['route']);
    $from_stop = htmlspecialchars($_POST['from']);
    $to_stop = htmlspecialchars($_POST['to']);
    $bus_id = intval($_POST['bus_id']);

    // Get bus details using prepared statement
    $bus_query = "SELECT seat_count, plate_number FROM buses WHERE id = ?";
    $bus_stmt = mysqli_prepare($conn, $bus_query);
    mysqli_stmt_bind_param($bus_stmt, "i", $bus_id);
    mysqli_stmt_execute($bus_stmt);
    $bus_result = mysqli_stmt_get_result($bus_stmt);
    $bus = mysqli_fetch_assoc($bus_result);
    mysqli_stmt_close($bus_stmt);
    
    $total_seats = $bus['seat_count'];

    // Get booked seats using prepared statement
    $booked_query = "SELECT seat_number FROM bookings 
                    WHERE bus_id = ? AND travel_date = ?";
    $booked_stmt = mysqli_prepare($conn, $booked_query);
    mysqli_stmt_bind_param($booked_stmt, "is", $bus_id, $travel_date);
    mysqli_stmt_execute($booked_stmt);
    $booked_result = mysqli_stmt_get_result($booked_stmt);
    
    $booked_seats = [];
    while ($b = mysqli_fetch_assoc($booked_result)) {
        $booked_seats[] = $b['seat_number'];
    }
    mysqli_stmt_close($booked_stmt);
?>
    <div class="booking-container">
        <div class="booking-progress">
            <div class="progress-step completed">1. Select Route</div>
            <div class="progress-step completed">2. Select Stops</div>
            <div class="progress-step completed">3. Select Date</div>
            <div class="progress-step completed">4. Select Bus</div>
            <div class="progress-step active">5. Select Seat</div>
        </div>
        
        <div class="booking-card">
            <h2><i class="fas fa-couch"></i> Select Your Seat</h2>
            
            <div class="bus-info">
                <p><strong>Bus:</strong> <?= htmlspecialchars($bus['plate_number']) ?></p>
                <p><strong>Date:</strong> <?= date('l, F j, Y', strtotime($travel_date)) ?></p>
                <p><strong>Route:</strong> <?= htmlspecialchars($from_stop) ?> to <?= htmlspecialchars($to_stop) ?></p>
            </div>
            
            <form method='POST' class="booking-form">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type='hidden' name='route' value='<?= htmlspecialchars($route_id) ?>'>
                <input type='hidden' name='from' value='<?= htmlspecialchars($from_stop) ?>'>
                <input type='hidden' name='to' value='<?= htmlspecialchars($to_stop) ?>'>
                <input type='hidden' name='travel_date' value='<?= htmlspecialchars($travel_date) ?>'>
                <input type='hidden' name='bus_id' value='<?= htmlspecialchars($bus_id) ?>'>
                
                <div class="seat-selection">
                    <h3>Available Seats</h3>
                    <p>Green seats are available, gray seats are already booked.</p>
                    
                    <div class="seats-grid">
                        <?php for ($i = 1; $i <= $total_seats; $i++): ?>
                            <?php $is_booked = in_array($i, $booked_seats); ?>
                            <div class="seat-option">
                                <input 
                                    type="radio" 
                                    name="seat_number" 
                                    id="seat-<?= $i ?>" 
                                    value="<?= $i ?>" 
                                    <?= $is_booked ? 'disabled' : '' ?>
                                    <?php if ($i == 1 && !$is_booked): ?> required <?php endif; ?>
                                >
                                <label for="seat-<?= $i ?>" class="<?= $is_booked ? 'booked' : 'available' ?>">
                                    <span class="seat-number"><?= $i ?></span>
                                    <?php if ($is_booked): ?>
                                        <span class="seat-status">Booked</span>
                                    <?php else: ?>
                                        <span class="seat-status">Available</span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                    <button type="submit" class="btn btn-primary">Confirm Booking <i class="fas fa-check"></i></button>
                </div>
            </form>
        </div>
    </div>
<?php
}
// Final step: Process booking
else {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    // Validate all inputs
    $travel_date = $_POST['travel_date'];
    if (!validateDate($travel_date, $min_date, $max_date)) {
        echo "<div class='container error-message'>
                <h2><i class='fas fa-exclamation-triangle'></i> Invalid Date</h2>
                <p>Please select a valid travel date between $min_date and $max_date.</p>
                <a href='javascript:history.back()' class='btn btn-primary'><i class='fas fa-arrow-left'></i> Go Back</a>
              </div>";
        include "footer.php";
        exit;
    }
    
    $route_id = intval($_POST['route']);
    $from_stop = htmlspecialchars($_POST['from']);
    $to_stop = htmlspecialchars($_POST['to']);
    $bus_id = intval($_POST['bus_id']);
    $seat_number = intval($_POST['seat_number']);

    // Check again if seat is available (race condition) using prepared statement
    $check_query = "SELECT * FROM bookings 
                   WHERE bus_id = ? 
                   AND seat_number = ? 
                   AND travel_date = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "iis", $bus_id, $seat_number, $travel_date);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo "<div class='container error-message'>
                <h2><i class='fas fa-exclamation-triangle'></i> Seat Already Booked</h2>
                <p>Sorry, this seat was just booked by someone else. Please go back and choose another seat.</p>
                <a href='javascript:history.back()' class='btn btn-primary'><i class='fas fa-arrow-left'></i> Choose Another Seat</a>
              </div>";
        include "footer.php";
        exit;
    }
    mysqli_stmt_close($check_stmt);

    // Insert booking using prepared statement
    $insert_sql = "INSERT INTO bookings 
                  (user_id, bus_id, route_id, seat_number, from_stop, to_stop, travel_date, booked_at, status)
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'confirmed')";
    
    $stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($stmt, "iiissss", 
        $user_id, 
        $bus_id, 
        $route_id, 
        $seat_number, 
        $from_stop, 
        $to_stop, 
        $travel_date
    );
    // Fetch plate number for the bus
$plate_sql = "SELECT plate_number FROM buses WHERE id = ?";
$plate_stmt = mysqli_prepare($conn, $plate_sql);
mysqli_stmt_bind_param($plate_stmt, "i", $bus_id);
mysqli_stmt_execute($plate_stmt);
$plate_result = mysqli_stmt_get_result($plate_stmt);
if ($plate_row = mysqli_fetch_assoc($plate_result)) {
    $bus_plate_number = $plate_row['plate_number'];
} else {
    $bus_plate_number = "Unknown";
}
mysqli_stmt_close($plate_stmt);

    
    if (mysqli_stmt_execute($stmt)) {
        $booking_id = mysqli_insert_id($conn);
?>
    <div class="booking-container">
        <div class="booking-success">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Booking Confirmed!</h2>
            <div class="booking-details">
                <p><strong>Booking ID:</strong> #<?= str_pad($booking_id, 6, '0', STR_PAD_LEFT) ?></p>
                <p><strong>Seat Number:</strong> <?= htmlspecialchars($seat_number) ?></p>
                <p><strong>Bus:</strong> <?= htmlspecialchars($bus_plate_number) ?></p>
                <p><strong>Date:</strong> <?= date('l, F j, Y', strtotime($travel_date)) ?></p>
                <p><strong>Route:</strong> <?= htmlspecialchars($from_stop) ?> to <?= htmlspecialchars($to_stop) ?></p>
            </div>
            
            <div class="success-actions">
                <a href="booking.php" class="btn btn-primary"><i class="fas fa-plus"></i> Book Another Seat</a>
                <a href="booking_history.php" class="btn btn-secondary"><i class="fas fa-list"></i> View My Bookings</a>
            </div>
        </div>
    </div>
<?php
    } else {
        echo "<div class='container error-message'>
                <h2><i class='fas fa-exclamation-triangle'></i> Booking Failed</h2>
                <p>There was an error processing your booking. Please try again.</p>
                <a href='javascript:history.back()' class='btn btn-primary'><i class='fas fa-arrow-left'></i> Try Again</a>
              </div>";
    }
    
    mysqli_stmt_close($stmt);
}

// Date validation function
function validateDate($date, $min, $max) {
    if (!DateTime::createFromFormat('Y-m-d', $date)) {
        return false;
    }
    return ($date >= $min && $date <= $max);
}
?>

<style>
    /* Base Styles */
    :root {
        --primary-color: #4a6bff;
        --secondary-color: #6c757d;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
        --info-color: #17a2b8;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f7fa;
        color: #333;
        line-height: 1.6;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }
    
    .booking-container {
        max-width: 900px;
        margin: 30px auto;
        padding: 20px;
    }
    
    .booking-progress {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        position: relative;
    }
    
    .booking-progress::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 0;
        right: 0;
        height: 2px;
        background-color: #e0e0e0;
        z-index: 1;
    }
    
    .progress-step {
        position: relative;
        z-index: 2;
        text-align: center;
        font-size: 14px;
        color: #999;
        flex: 1;
    }
    
    .progress-step::before {
        content: '';
        display: block;
        width: 30px;
        height: 30px;
        margin: 0 auto 10px;
        border-radius: 50%;
        background-color: #e0e0e0;
        color: #fff;
        line-height: 30px;
        text-align: center;
        font-weight: bold;
    }
    
    .progress-step.active {
        color: var(--primary-color);
        font-weight: bold;
    }
    
    .progress-step.active::before {
        background-color: var(--primary-color);
        content: counter(step);
        counter-increment: step;
    }
    
    .progress-step.completed {
        color: var(--success-color);
    }
    
    .progress-step.completed::before {
        background-color: var(--success-color);
        content: '✓';
    }
    
    .booking-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .booking-card h2 {
        color: var(--primary-color);
        margin-bottom: 25px;
        font-size: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .booking-form {
        margin-top: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
        transition: border-color 0.3s;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(74, 107, 255, 0.2);
    }
    
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }
    
    .btn {
        display: inline-block;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 500;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
        font-size: 16px;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #3a5bef;
        transform: translateY(-2px);
    }
    
    .btn-secondary {
        background-color: var(--secondary-color);
        color: white;
    }
    
    .btn-secondary:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
    }
    
    .form-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }
    
    .bus-info {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 25px;
    }
    
    .bus-info p {
        margin: 5px 0;
    }
    
    .seat-selection {
        margin-top: 20px;
    }
    
    .seats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .seat-option {
        position: relative;
    }
    
    .seat-option input[type="radio"] {
        position: absolute;
        opacity: 0;
    }
    
    .seat-option label {
        display: block;
        padding: 15px 10px;
        border-radius: 6px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .seat-option .available {
        background-color: #e8f5e9;
        border: 2px solid #81c784;
        color: #2e7d32;
    }
    
    .seat-option .available:hover {
        background-color: #c8e6c9;
        transform: translateY(-3px);
    }
    
    .seat-option input[type="radio"]:checked + .available {
        background-color: #4caf50;
        color: white;
        border-color: #4caf50;
    }
    
    .seat-option .booked {
        background-color: #efefef;
        border: 2px solid #bdbdbd;
        color: #757575;
        cursor: not-allowed;
    }
    
    .seat-number {
        display: block;
        font-size: 18px;
        font-weight: bold;
    }
    
    .seat-status {
        display: block;
        font-size: 12px;
        margin-top: 5px;
    }
    
    .booking-success {
        text-align: center;
        padding: 40px 20px;
    }
    
    .success-icon {
        font-size: 60px;
        color: var(--success-color);
        margin-bottom: 20px;
    }
    
    .booking-details {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        max-width: 500px;
        margin: 20px auto;
        text-align: left;
    }
    
    .booking-details p {
        margin: 10px 0;
    }
    
    .success-actions {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        gap: 15px;
    }
    
    .error-message {
        text-align: center;
        padding: 40px 20px;
    }
    
    .error-message h2 {
        color: var(--danger-color);
    }
    
    .form-text.text-muted {
        color: #6c757d;
        font-size: 0.875em;
        margin-top: 0.25rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .booking-progress {
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .progress-step {
            flex: 0 0 calc(33.333% - 10px);
        }
        
        .seats-grid {
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        }
        
        .form-actions, .success-actions {
            flex-direction: column;
            gap: 10px;
        }
    }
    
    @media (max-width: 480px) {
        .progress-step {
            flex: 0 0 calc(50% - 10px);
            font-size: 12px;
        }
        
        .progress-step::before {
            width: 25px;
            height: 25px;
            line-height: 25px;
        }
        
        .booking-card {
            padding: 20px 15px;
        }
        
        .seats-grid {
            grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            gap: 10px;
        }
    }
</style>

<?php include "footer.php"; ?>