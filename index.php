<?php
// index.php - User panel: a client books an appointment with a mechanic.
require 'db.php';
require 'functions.php';
require 'layout.php';

// Empty form values (filled in again if the form has errors)
$name        = "";
$address     = "";
$phone       = "";
$car_license = "";
$car_engine  = "";
$date        = "";
$mechanic_id = "";

$errors  = [];   // error messages, e.g. $errors['phone'] = "..."
$message = "";   // success message

// ---------- The form was submitted ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 1. Read the form fields
    $name        = trim($_POST['name'] ?? "");
    $address     = trim($_POST['address'] ?? "");
    $phone       = trim($_POST['phone'] ?? "");
    $car_license = normalize_license($_POST['car_license'] ?? "");
    $car_engine  = trim($_POST['car_engine'] ?? "");
    $date        = trim($_POST['date'] ?? "");
    $mechanic_id = trim($_POST['mechanic_id'] ?? "");

    // 2. Check that the fields are filled in correctly
    $errors = validate_booking([
        'name'        => $name,
        'address'     => $address,
        'phone'       => $phone,
        'car_license' => $car_license,
        'car_engine'  => $car_engine,
        'date'        => $date,
        'mechanic_id' => $mechanic_id,
    ]);

    // 3. Check the booking rules using the database
    if (count($errors) == 0) {
        $mechanic = get_mechanic($conn, $mechanic_id);

        if ($mechanic == null) {
            $errors['mechanic_id'] = "The selected mechanic does not exist.";

        } elseif (client_has_booking($conn, $car_license, $phone, $date)) {
            // Rule: one appointment per client per day
            $errors['form'] = "You already have an appointment on $date. Only one appointment per day is allowed.";

        } elseif (booked_count($conn, $mechanic_id, $date) >= $mechanic['max_daily']) {
            // Rule: a mechanic can take at most max_daily (4) cars per day
            $errors['mechanic_id'] = $mechanic['name'] . " is fully booked on $date. Please choose another mechanic.";

        } else {
            // 4. Everything is OK - save the appointment
            $sql = "INSERT INTO appointments
                    (client_name, address, phone, car_license, car_engine, appointment_date, mechanic_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            run($conn, $sql, "ssssssi", [$name, $address, $phone, $car_license, $car_engine, $date, $mechanic_id]);

            $message = "Appointment confirmed! $name is booked with " . $mechanic['name'] . " on $date.";

            // Clear the form (keep the date so the free slots list stays on that day)
            $name = $address = $phone = $car_license = $car_engine = $mechanic_id = "";
        }
    }
}

// Which date to show free slots for: the chosen date, or today
if (valid_date($date)) {
    $slotDate = $date;
} else {
    $slotDate = date("Y-m-d");
}
$slots = mechanic_slots($conn, $slotDate);

// Print the error message for one field (or an empty box)
function show_error($errors, $field)
{
    $text = "";
    if (isset($errors[$field])) {
        $text = $errors[$field];
    }
    echo '<small class="error" id="error-' . $field . '">' . h($text) . '</small>';
}

page_header("Book an Appointment", "book");
?>

<h1>Book an Appointment</h1>
<p>Choose your mechanic and date. Each mechanic can take at most 4 cars per day.
   Need help? See the <a href="help.php">help page</a>.</p>

<?php if ($message != ""): ?>
    <div class="alert success"><?= h($message) ?></div>
<?php endif; ?>

<?php if (isset($errors['form'])): ?>
    <div class="alert error"><?= h($errors['form']) ?></div>
<?php elseif (count($errors) > 0): ?>
    <div class="alert error">Please fix the errors below.</div>
<?php endif; ?>

<form id="booking-form" class="card" method="post" action="index.php">
    <h2>Your details</h2>

    <label for="name">Full name</label>
    <input id="name" name="name" type="text" value="<?= h($name) ?>">
    <?php show_error($errors, 'name'); ?>

    <label for="address">Address</label>
    <input id="address" name="address" type="text" value="<?= h($address) ?>">
    <?php show_error($errors, 'address'); ?>

    <label for="phone">Phone</label>
    <input id="phone" name="phone" type="text" value="<?= h($phone) ?>" placeholder="01XXXXXXXXX">
    <?php show_error($errors, 'phone'); ?>

    <label for="car_license">Car license number</label>
    <input id="car_license" name="car_license" type="text" value="<?= h($car_license) ?>">
    <?php show_error($errors, 'car_license'); ?>

    <label for="car_engine">Car engine number</label>
    <input id="car_engine" name="car_engine" type="text" value="<?= h($car_engine) ?>">
    <?php show_error($errors, 'car_engine'); ?>

    <h2>Appointment</h2>

    <label for="date">Appointment date</label>
    <input id="date" name="date" type="date" min="<?= date("Y-m-d") ?>" value="<?= h($date) ?>">
    <?php show_error($errors, 'date'); ?>

    <label for="mechanic_id">Mechanic</label>
    <select id="mechanic_id" name="mechanic_id">
        <option value="">-- Select a mechanic --</option>
        <?php foreach ($slots as $m): ?>
            <?php if ($m['free'] > 0): ?>
                <option value="<?= $m['id'] ?>" <?php if ($m['id'] == $mechanic_id) echo "selected"; ?>>
                    <?= h($m['name']) ?> - <?= $m['free'] ?> of <?= $m['max'] ?> free
                </option>
            <?php else: ?>
                <option value="<?= $m['id'] ?>" disabled><?= h($m['name']) ?> - fully booked</option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
    <?php show_error($errors, 'mechanic_id'); ?>

    <button type="submit" class="btn">Book appointment</button>
</form>

<div class="card">
    <h2>Free places on <span id="slot-date"><?= h($slotDate) ?></span></h2>
    <ul id="slot-list" class="slot-list">
        <?php foreach ($slots as $m): ?>
            <li>
                <?= h($m['name']) ?>
                <?php if ($m['free'] > 0): ?>
                    <span class="badge ok"><?= $m['free'] ?> / <?= $m['max'] ?> free</span>
                <?php else: ?>
                    <span class="badge full">0 / <?= $m['max'] ?> free</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php page_footer(['script.js']); ?>
