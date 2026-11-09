<?php
// admin.php - Admin panel: see all appointments and change date / mechanic.
require 'db.php';
require 'functions.php';
require 'layout.php';
require 'admin_auth.php';

require_admin();   // send to login page if not logged in

$message     = "";
$messageType = "";   // "success" or "error"

// ---------- The admin pressed "Complete" on a row ----------
// The car is finished, so the appointment is removed from the list.
// This also frees one place for that mechanic on that day.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete'])) {
    $id = (int) $_POST['id'];

    run($conn, "DELETE FROM appointments WHERE id = ?", "i", [$id]);

    $message     = "Appointment completed and removed from the list.";
    $messageType = "success";
}

// ---------- The admin pressed "Save" on a row ----------
elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id          = (int) ($_POST['id'] ?? 0);
    $date        = trim($_POST['date'] ?? "");
    $mechanic_id = trim($_POST['mechanic_id'] ?? "");

    // Check the new date and mechanic
    $errors = validate_date_and_mechanic($date, $mechanic_id);

    // Get the appointment being changed and the new mechanic
    $sql  = "SELECT client_name, car_license, phone FROM appointments WHERE id = ?";
    $appt = run($conn, $sql, "i", [$id])->get_result()->fetch_assoc();
    $mechanic = get_mechanic($conn, $mechanic_id);

    if (count($errors) > 0) {
        $message = implode(" ", $errors);

    } elseif ($appt == null) {
        $message = "Appointment not found.";

    } elseif ($mechanic == null) {
        $message = "Mechanic not found.";

    } elseif (client_has_booking($conn, $appt['car_license'], $appt['phone'], $date, $id)) {
        // $id is passed so this appointment does not count against itself
        $message = $appt['client_name'] . " already has another appointment on $date.";

    } elseif (booked_count($conn, $mechanic_id, $date, $id) >= $mechanic['max_daily']) {
        $message = $mechanic['name'] . " is fully booked on $date.";

    } else {
        $sql = "UPDATE appointments SET appointment_date = ?, mechanic_id = ? WHERE id = ?";
        run($conn, $sql, "sii", [$date, $mechanic_id, $id]);

        $message     = "Updated: " . $appt['client_name'] . " is now with " . $mechanic['name'] . " on $date.";
        $messageType = "success";
    }

    if ($messageType == "") {
        $messageType = "error";
    }
}

// ---------- Load data for the page ----------
$mechanics = $conn->query("SELECT id, name FROM mechanics ORDER BY id")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT a.id, a.client_name, a.phone, a.car_license, a.appointment_date, a.mechanic_id,
               m.name AS mechanic
        FROM appointments a
        JOIN mechanics m ON m.id = a.mechanic_id
        ORDER BY a.appointment_date, m.name";
$appointments = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

page_header("Admin Panel", "admin");
?>

<h1>Appointment List</h1>
<p>To change an appointment, pick a new date and/or mechanic in the last column and press Save.</p>

<?php if ($message != ""): ?>
    <div class="alert <?= $messageType ?>"><?= h($message) ?></div>
<?php endif; ?>

<?php if (count($appointments) == 0): ?>
    <p class="card">No appointments yet.</p>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <tr>
                <th>#</th>
                <th>Client name</th>
                <th>Phone</th>
                <th>Car reg. no.</th>
                <th>Date</th>
                <th>Mechanic</th>
                <th>Change date / mechanic</th>
                <th>Complete</th>
            </tr>
            <?php foreach ($appointments as $i => $a): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= h($a['client_name']) ?></td>
                    <td><?= h($a['phone']) ?></td>
                    <td><?= h($a['car_license']) ?></td>
                    <td><?= h($a['appointment_date']) ?></td>
                    <td><?= h($a['mechanic']) ?></td>
                    <td>
                        <form class="inline-edit" method="post" action="admin.php">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <input type="date" name="date" value="<?= h($a['appointment_date']) ?>">
                            <select name="mechanic_id">
                                <?php foreach ($mechanics as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?php if ($m['id'] == $a['mechanic_id']) echo "selected"; ?>>
                                        <?= h($m['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn small">Save</button>
                        </form>
                    </td>
                    <td>
                        <!-- confirm() shows an OK/Cancel box; Cancel stops the form -->
                        <form method="post" action="admin.php" onsubmit="return confirm('Mark this appointment as complete? It will be removed.');">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="submit" name="complete" class="btn small complete">Complete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>

<?php page_footer(); ?>
