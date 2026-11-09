function showError(fieldId, message) {
    document.getElementById("error-" + fieldId).innerHTML = message;

    var input = document.getElementById(fieldId);
    if (message == "") {
        input.className = "";
    } else {
        input.className = "invalid";
    }
}

function getToday() {
    var d = new Date();
    var month = d.getMonth() + 1;
    var day = d.getDate();

    if (month < 10) month = "0" + month;
    if (day < 10) day = "0" + day;

    return d.getFullYear() + "-" + month + "-" + day;
}

function validateForm() {
    var ok = true;

    var name     = document.getElementById("name").value.trim();
    var address  = document.getElementById("address").value.trim();
    var phone    = document.getElementById("phone").value.trim();
    var license  = document.getElementById("car_license").value.trim();
    var engine   = document.getElementById("car_engine").value.trim();
    var date     = document.getElementById("date").value;
    var mechanic = document.getElementById("mechanic_id").value;

    if (name == "") {
        showError("name", "Name is required.");
        ok = false;
    } else if (!/^[a-zA-Z .'-]+$/.test(name)) {
        showError("name", "Name can only contain letters and spaces.");
        ok = false;
    } else {
        showError("name", "");
    }

    if (address == "") {
        showError("address", "Address is required.");
        ok = false;
    } else {
        showError("address", "");
    }

    if (phone == "") {
        showError("phone", "Phone number is required.");
        ok = false;
    } else if (!/^[0-9]{7,15}$/.test(phone)) {
        showError("phone", "Phone must contain only numbers (7-15 digits).");
        ok = false;
    } else {
        showError("phone", "");
    }

    if (license == "") {
        showError("car_license", "Car license number is required.");
        ok = false;
    } else {
        showError("car_license", "");
    }

    if (engine == "") {
        showError("car_engine", "Car engine number is required.");
        ok = false;
    } else if (!/^[0-9]+$/.test(engine)) {
        showError("car_engine", "Engine number must contain only numbers.");
        ok = false;
    } else {
        showError("car_engine", "");
    }

    if (date == "") {
        showError("date", "Appointment date is required.");
        ok = false;
    } else if (date < getToday()) {
        showError("date", "Date cannot be in the past.");
        ok = false;
    } else {
        showError("date", "");
    }

    if (mechanic == "") {
        showError("mechanic_id", "Please select a mechanic.");
        ok = false;
    } else {
        showError("mechanic_id", "");
    }

    return ok;
}

window.onload = function () {
    var form = document.getElementById("booking-form");

    form.onsubmit = function () {
        return validateForm();
    };
};
