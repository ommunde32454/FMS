/**
 * public/js/app.js
 * Main UI Logic
 */

document.addEventListener("DOMContentLoaded", function() {
    
    // 1. Auto-dismiss Flash Alerts
    const alerts = document.querySelectorAll('.alert-dismissible');
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(el => {
                el.style.opacity = "0";
                setTimeout(() => el.remove(), 500);
            });
        }, 4000);
    }
});

// 2. Global Modal Toggler
window.toggleModal = function(modalID) {
    const modal = document.getElementById(modalID);
    if (!modal) return;
    
    const isHidden = modal.classList.contains('hidden');
    if (isHidden) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('modal-active');
    } else {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('modal-active');
    }
};

// 3. Confirm Delete Helper
window.confirmDelete = function(msg = "Are you sure you want to delete this?") {
    return confirm(msg);
};