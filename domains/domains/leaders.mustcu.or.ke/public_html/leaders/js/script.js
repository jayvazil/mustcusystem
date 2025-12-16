document.addEventListener('DOMContentLoaded', function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        alert.style.opacity = 0;
        var opacity = 0;
        var fadeIn = setInterval(function() {
            if (opacity >= 1) clearInterval(fadeIn);
            alert.style.opacity = opacity;
            opacity += 0.1;
        }, 50);
    });
});