// script.js
// 1. Checks the booking form before it is sent to the server.
// 2. When the date changes, asks the server (slots.php) how many
//    free places each mechanic has on that date.


// Show an error message under a field (or clear it if message is "")
function showError(fieldId, message) {
    document.getElementById("error-" + fieldId).innerHTML = message;

    var input = document.getElementById(fieldId);
    if (message == "") {
        input.className = "";          // remove red border
    } else {
        input.className = "invalid";   // add red border
    }
}


// Today's date as text in the form YYYY-MM-DD (same format as the date input)
function getToday() {
    var d = new Date();
    var month = d.getMonth() + 1;      // months start at 0 in JavaScript
    var day = d.getDate();

    if (month < 10) month = "0" + month;
    if (day < 10) day = "0" + day;

    return d.getFullYear() + "-" + month + "-" + day;
}


// Check every field. Returns true if the form is OK, false if not.
function validateForm() {
    var ok = true;

    var name     = document.getElementById("name").value.trim();
    var address  = document.getElementById("address").value.trim();
    var phone    = document.getElementById("phone").value.trim();
    var license  = document.getElementById("car_license").value.trim();
    var engine   = document.getElementById("car_engine").value.trim();
    var date     = document.getElementById("date").value;
    var mechanic = document.getElementById("mechanic_id").value;

    // Name: not empty, only letters, spaces, dots, apostrophes and hyphens
    if (name == "") {
        showError("name", "Name is required.");
        ok = false;
    } else if (!/^[a-zA-Z .'-]+$/.test(name)) {
        showError("name", "Name can only contain letters and spaces.");
        ok = false;
    } else {
        showError("name", "");
    }

    // Address: not empty
    if (address == "") {
        showError("address", "Address is required.");
        ok = false;
    } else {
        showError("address", "");
    }

    // Phone: not empty, digits only
    if (phone == "") {
        showError("phone", "Phone number is required.");
        ok = false;
    } else if (!/^[0-9]{7,15}$/.test(phone)) {
        showError("phone", "Phone must contain only numbers (7-15 digits).");
        ok = false;
    } else {
        showError("phone", "");
    }

    // Car license: not empty
    if (license == "") {
        showError("car_license", "Car license number is required.");
        ok = false;
    } else {
        showError("car_license", "");
    }

    // Engine number: not empty, digits only
    if (engine == "") {
        showError("car_engine", "Car engine number is required.");
        ok = false;
    } else if (!/^[0-9]+$/.test(engine)) {
        showError("car_engine", "Engine number must contain only numbers.");
        ok = false;
    } else {
        showError("car_engine", "");
    }

    // Date: not empty, not in the past
    if (date == "") {
        showError("date", "Appointment date is required.");
        ok = false;
    } else if (date < getToday()) {
        showError("date", "Date cannot be in the past.");
        ok = false;
    } else {
        showError("date", "");
    }

    // Mechanic: must choose one
    if (mechanic == "") {
        showError("mechanic_id", "Please select a mechanic.");
        ok = false;
    } else {
        showError("mechanic_id", "");
    }

    return ok;
}


// Ask the server for free places on the chosen date and update the page
function loadSlots() {
    var date = document.getElementById("date").value;
    if (date == "") {
        return;
    }

    // fetch() sends a request to slots.php without reloading the page
    fetch("slots.php?date=" + date)
        .then(function (response) {
            return response.json();     // turn the reply into a JavaScript array
        })
        .then(function (mechanics) {
            updateSlots(date, mechanics);
        });
}


// Rebuild the mechanic dropdown and the "free slots" list
function updateSlots(date, mechanics) {
    var select = document.getElementById("mechanic_id");
    var list = document.getElementById("slot-list");

    document.getElementById("slot-date").innerHTML = date;

    var options = '<option value="">-- Select a mechanic --</option>';
    var items = "";

    for (var i = 0; i < mechanics.length; i++) {
        var m = mechanics[i];

        if (m.free > 0) {
            options += '<option value="' + m.id + '">' + m.name + ' - ' + m.free + ' of ' + m.max + ' free</option>';
            items += '<li>' + m.name + ' <span class="badge ok">' + m.free + ' / ' + m.max + ' free</span></li>';
        } else {
            options += '<option value="' + m.id + '" disabled>' + m.name + ' - fully booked</option>';
            items += '<li>' + m.name + ' <span class="badge full">0 / ' + m.max + ' free</span></li>';
        }
    }

    select.innerHTML = options;
    list.innerHTML = items;
}


// Connect the functions to the page once it has loaded
window.onload = function () {
    var form = document.getElementById("booking-form");

    // Run validateForm() when the user presses "Book appointment".
    // If it returns false the form is NOT sent.
    form.onsubmit = function () {
        return validateForm();
    };

    // When the date changes, load the free places for that date
    document.getElementById("date").onchange = loadSlots;
};
