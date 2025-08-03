//Sidebar code
$(".sidebar-dropdown > a").click(function() {
    $(".sidebar-submenu").slideUp(200);
    if (
        $(this)
            .parent()
            .hasClass("active")
    ) {
        $(".sidebar-dropdown").removeClass("active");
        $(this)
            .parent()
            .removeClass("active");
    } else {
        $(".sidebar-dropdown").removeClass("active");
        $(this)
            .next(".sidebar-submenu")
            .slideDown(200);
        $(this)
            .parent()
            .addClass("active");
    }
});

$("#close-sidebar").click(function() {
    $(".page-wrapper").removeClass("toggled");
});
$("#show-sidebar").click(function() {
    $(".page-wrapper").addClass("toggled");
});


//Login form
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const eyeIcon = document.getElementById(fieldId + '-eye');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
    } else {
        passwordField.type = 'password';
    }
}
// toaster
document.addEventListener('DOMContentLoaded', function () {
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.forEach(function (toastEl) {
        const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
        toast.show();
    });
});


// Show/hide time inputs on checkbox toggle
document.querySelectorAll('.day-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        const timeDiv = document.getElementById(this.id.replace('Checkbox', 'Time'));
        timeDiv.classList.toggle('d-none', !this.checked);
    });
});

// Validate end time > start time
document.querySelectorAll('.end-time').forEach(input => {
    input.addEventListener('change', function () {
        const day = this.dataset.day;
        const startTime = document.querySelector(`.start-time[data-day="${day}"]`);
        if (startTime && this.value <= startTime.value) {
            alert(`${day}: End time must be after start time.`);
            this.value = '';
        }
    });
});

const bufferInput = document.getElementById('bufferTime');

if (bufferInput) {
    bufferInput.addEventListener('input', function () {
        const val = parseInt(this.value, 10);
        if (val < 1 || val > 60) {
            alert("Buffer time must be between 1 and 60 minutes.");
            this.value = '';
        }
    });
}
