/**
 * public/js/charts.js
 * Simple visualization logic (can be expanded with Chart.js)
 */

document.addEventListener("DOMContentLoaded", function() {
    // Example: Animate Progress Bars on Dashboard
    const progressBars = document.querySelectorAll('.progress-bar-fill');
    
    progressBars.forEach(bar => {
        const width = bar.getAttribute('data-width');
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width + '%';
        }, 500);
    });
});