<?php
// Help page for clients and the admin.
require 'functions.php';
require 'layout.php';

page_header('Help', 'help');
?>
<section class="hero">
    <h1>Help</h1>
    <p>Everything you need to know about booking a service appointment.</p>
</section>

<div class="card prose">
    <h2>How do I book an appointment?</h2>
    <ol>
        <li>Open the <a href="index.php">Book</a> page.</li>
        <li>Fill in your name, address, phone, car license number and car engine number.</li>
        <li>Pick an appointment date. The list of mechanics updates to show how many free places each one has on that day.</li>
        <li>Choose a mechanic who still has free places, then press <strong>Book appointment</strong>.</li>
        <li>A green message confirms your booking. A red message explains what to fix.</li>
    </ol>

    <h2>Booking rules</h2>
    <ul>
        <li>Each mechanic can take at most <strong>4 cars per day</strong>. Fully booked mechanics are greyed out.</li>
        <li>Each client can have only <strong>one appointment per day</strong> (checked by car license number and phone).</li>
        <li>You cannot book a date in the past.</li>
    </ul>

    <h2>What should I type in each field?</h2>
    <table>
        <thead><tr><th>Field</th><th>Rule</th><th>Example</th></tr></thead>
        <tbody>
        <tr><td>Name</td><td>Letters, spaces, dots, apostrophes, hyphens</td><td>Mashfi Mahin</td></tr>
        <tr><td>Address</td><td>Required, up to 255 characters</td><td>House 12, Road 5, Dhanmondi, Dhaka</td></tr>
        <tr><td>Phone</td><td>Digits only, 7&ndash;15 digits</td><td>01712345678</td></tr>
        <tr><td>Car license no.</td><td>Letters, digits, spaces, hyphens</td><td>DHAKA-GA-12-3456</td></tr>
        <tr><td>Car engine no.</td><td>Digits only</td><td>458812345</td></tr>
        <tr><td>Date</td><td>Today or later</td><td><?= date('Y-m-d') ?></td></tr>
        <tr><td>Mechanic</td><td>Must choose one with free places</td><td>&mdash;</td></tr>
        </tbody>
    </table>

    <h2>Common messages</h2>
    <dl>
        <dt>&ldquo;You already have an appointment on this date&rdquo;</dt>
        <dd>Your car or phone number is already booked that day. Pick another date.</dd>
        <dt>&ldquo;Mechanic is fully booked&rdquo;</dt>
        <dd>All 4 places for that mechanic are taken. Choose another mechanic or another date.</dd>
    </dl>

    <h2>I need to change my appointment</h2>
    <p>Contact the workshop. The admin can move your appointment to another date or mechanic.</p>

    <h2>For the admin</h2>
    <p>Log in from the <a href="admin.php">Admin</a> page. You can see every appointment and change any
       appointment's date or mechanic. The system will not let
       you move a car to a mechanic who is already full on that day. When a car is finished, press
       <strong>Complete</strong> to remove the appointment; this frees a place for that mechanic.</p>
</div>
<?php page_footer(); ?>
