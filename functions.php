<?php
// functions.php
// Small helper functions used by index.php, admin.php and slots.php.


// Make text safe to print inside HTML (stops people injecting HTML/JavaScript).
function h($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}


// Run an SQL query safely using a prepared statement.
//   $sql    - the query with ? where values go
//   $types  - one letter per value: s = string, i = integer
//   $values - array of the values
// Example: run($conn, "SELECT * FROM mechanics WHERE id = ?", "i", [3]);
function run($conn, $sql, $types = "", $values = [])
{
    $stmt = $conn->prepare($sql);

    if ($types != "") {
        // "...$values" passes each array item as a separate argument
        $stmt->bind_param($types, ...$values);
    }

    $stmt->execute();
    return $stmt;
}


// Check that $date looks like YYYY-MM-DD and is a real date (not 2026-02-31).
function valid_date($date)
{
    $parts = explode("-", $date);          // "2026-11-09" -> ["2026", "11", "09"]

    if (count($parts) != 3) {
        return false;
    }

    $year  = (int) $parts[0];
    $month = (int) $parts[1];
    $day   = (int) $parts[2];

    return checkdate($month, $day, $year);
}


// Make licence numbers the same format: capital letters, no extra spaces.
function normalize_license($license)
{
    return strtoupper(trim($license));
}


// Check the date and mechanic. Returns an array of error messages.
function validate_date_and_mechanic($date, $mechanicId)
{
    $errors = [];

    if ($date == "") {
        $errors['date'] = "Appointment date is required.";
    } elseif (!valid_date($date)) {
        $errors['date'] = "Enter a valid date.";
    } elseif ($date < date("Y-m-d")) {
        $errors['date'] = "Appointment date cannot be in the past.";
    }

    if ($mechanicId == "" || !is_numeric($mechanicId)) {
        $errors['mechanic_id'] = "Please select a mechanic.";
    }

    return $errors;
}


// Check all booking form fields. Returns an array like ['phone' => 'error message'].
// An empty array means everything is OK.
function validate_booking($data)
{
    $errors = [];

    // Name: required, letters and spaces only
    if ($data['name'] == "") {
        $errors['name'] = "Name is required.";
    } elseif (!preg_match("/^[a-zA-Z .'-]+$/", $data['name'])) {
        $errors['name'] = "Name can only contain letters and spaces.";
    }

    // Address: required
    if ($data['address'] == "") {
        $errors['address'] = "Address is required.";
    }

    // Phone: required, numbers only, 7-15 digits
    if ($data['phone'] == "") {
        $errors['phone'] = "Phone number is required.";
    } elseif (!preg_match("/^[0-9]{7,15}$/", $data['phone'])) {
        $errors['phone'] = "Phone must contain only numbers (7-15 digits).";
    }

    // Car license: required
    if ($data['car_license'] == "") {
        $errors['car_license'] = "Car license number is required.";
    }

    // Engine number: required, numbers only
    if ($data['car_engine'] == "") {
        $errors['car_engine'] = "Car engine number is required.";
    } elseif (!preg_match("/^[0-9]+$/", $data['car_engine'])) {
        $errors['car_engine'] = "Engine number must contain only numbers.";
    }

    // Date and mechanic (same checks the admin page uses)
    $more = validate_date_and_mechanic($data['date'], $data['mechanic_id']);
    $errors = array_merge($errors, $more);

    return $errors;
}


// Get one mechanic (id, name, max_daily) or null if not found.
function get_mechanic($conn, $id)
{
    $result = run($conn, "SELECT id, name, max_daily FROM mechanics WHERE id = ?", "i", [$id])->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        return $row;
    }
    return null;
}


// How many appointments a mechanic has on a date.
// $excludeId lets the admin page ignore the appointment it is moving.
function booked_count($conn, $mechanicId, $date, $excludeId = 0)
{
    $sql = "SELECT COUNT(*) FROM appointments
            WHERE mechanic_id = ? AND appointment_date = ? AND id != ?";

    $result = run($conn, $sql, "isi", [$mechanicId, $date, $excludeId])->get_result();
    $row = $result->fetch_row();

    return (int) $row[0];
}


// Does this client already have an appointment on this date?
// A client is matched by the same car license OR the same phone number.
function client_has_booking($conn, $license, $phone, $date, $excludeId = 0)
{
    $sql = "SELECT COUNT(*) FROM appointments
            WHERE appointment_date = ? AND (car_license = ? OR phone = ?) AND id != ?";

    $result = run($conn, $sql, "sssi", [$date, $license, $phone, $excludeId])->get_result();
    $row = $result->fetch_row();

    return $row[0] > 0;
}


// List every mechanic with how many places are booked and free on a date.
function mechanic_slots($conn, $date)
{
    $list = [];
    $mechanics = $conn->query("SELECT id, name, max_daily FROM mechanics ORDER BY id");

    while ($m = $mechanics->fetch_assoc()) {
        $booked = booked_count($conn, $m['id'], $date);
        $max    = (int) $m['max_daily'];
        $free   = $max - $booked;

        if ($free < 0) {      // can happen if the limit is lowered later
            $free = 0;
        }

        $list[] = [
            'id'     => (int) $m['id'],
            'name'   => $m['name'],
            'max'    => $max,
            'booked' => $booked,
            'free'   => $free,
        ];
    }

    return $list;
}
