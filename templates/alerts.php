<?php
// templates/alerts.php

// Check for Success Message
if (isset($_SESSION['flash_success'])): ?>
    <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-6 shadow-sm relative" role="alert">
        <div class="flex justify-between items-center">
            <p><i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['flash_success']; ?></p>
            <button onclick="this.parentElement.parentElement.remove()" class="text-emerald-700 font-bold">&times;</button>
        </div>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php 
// Check for Error Message
if (isset($_SESSION['flash_error'])): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm relative" role="alert">
        <div class="flex justify-between items-center">
            <p><i class="fas fa-exclamation-triangle mr-2"></i> <?php echo $_SESSION['flash_error']; ?></p>
            <button onclick="this.parentElement.parentElement.remove()" class="text-red-700 font-bold">&times;</button>
        </div>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>