<?php

function h($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function run($conn, $sql, $types = "", $values = [])
{
    $stmt = $conn->prepare($sql);

    if ($types != "") {
        $stmt->bind_param($types, ...$values);
    }

    $stmt->execute();
    return $stmt;
}

function valid_date($date)
{
    $parts = explode("-", $date);

    if (count($parts) != 3) {
        return false;
    }

    $year  = (int) $parts[0];
    $month = (int) $parts[1];
    $day   = (int) $parts[2];

    return checkdate($month, $day, $year);
}

function normalize_license($license)
{
    return strtoupper(trim($license));
}

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

function validate_booking($data)
{
    $errors = [];

    if ($data['name'] == "") {
        $errors['name'] = "Name is required.";
    } elseif (!preg_match("/^[a-zA-Z .'-]+$/", $data['name'])) {
        $errors['name'] = "Name can only contain letters and spaces.";
    }

    if ($data['address'] == "") {
        $errors['address'] = "Address is required.";
    }

    if ($data['phone'] == "") {
        $errors['phone'] = "Phone number is required.";
    } elseif (!preg_match("/^[0-9]{7,15}$/", $data['phone'])) {
        $errors['phone'] = "Phone must contain only numbers (7-15 digits).";
    }

    if ($data['car_license'] == "") {
        $errors['car_license'] = "Car license number is required.";
    }

    if ($data['car_engine'] == "") {
        $errors['car_engine'] = "Car engine number is required.";
    } elseif (!preg_match("/^[0-9]+$/", $data['car_engine'])) {
        $errors['car_engine'] = "Engine number must contain only numbers.";
    }

    $more = validate_date_and_mechanic($data['date'], $data['mechanic_id']);
    $errors = array_merge($errors, $more);

    return $errors;
}

function get_mechanic($conn, $id)
{
    $result = run($conn, "SELECT id, name, max_daily FROM mechanics WHERE id = ?", "i", [$id])->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        return $row;
    }
    return null;
}

function booked_count($conn, $mechanicId, $date, $excludeId = 0)
{
    $sql = "SELECT COUNT(*) FROM appointments
            WHERE mechanic_id = ? AND appointment_date = ? AND id != ?";

    $result = run($conn, $sql, "isi", [$mechanicId, $date, $excludeId])->get_result();
    $row = $result->fetch_row();

    return (int) $row[0];
}

function client_has_booking($conn, $license, $phone, $date, $excludeId = 0)
{
    $sql = "SELECT COUNT(*) FROM appointments
            WHERE appointment_date = ? AND (car_license = ? OR phone = ?) AND id != ?";

    $result = run($conn, $sql, "sssi", [$date, $license, $phone, $excludeId])->get_result();
    $row = $result->fetch_row();

    return $row[0] > 0;
}

function mechanic_slots($conn, $date)
{
    $list = [];
    $mechanics = $conn->query("SELECT id, name, max_daily FROM mechanics ORDER BY id");

    while ($m = $mechanics->fetch_assoc()) {
        $booked = booked_count($conn, $m['id'], $date);
        $max    = (int) $m['max_daily'];
        $free   = $max - $booked;

        if ($free < 0) {
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
