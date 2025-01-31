document.addEventListener('DOMContentLoaded', function () {
    // Countdown Timer
    const countdowns = document.querySelectorAll('.countdown-timer');
    countdowns.forEach(countdown => {
        const deadline = new Date(countdown.dataset.deadline).getTime();

        const timer = setInterval(() => {
            const now = new Date().getTime();
            const distance = deadline - now;

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

            countdown.innerHTML = `
                <i class="bi bi-clock"></i>
                ${days}d ${hours}h ${minutes}m
            `;

            if (distance < 0) {
                clearInterval(timer);
                countdown.innerHTML = 'Registration Closed';
            }
        }, 1000);
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});