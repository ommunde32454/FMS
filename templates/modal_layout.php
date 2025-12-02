<?php
// templates/modal_layout.php
// Variables expected: $modalId, $modalTitle
?>
<div id="<?php echo $modalId; ?>" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white w-full max-w-lg mx-auto rounded shadow-lg overflow-hidden transform transition-all">
        
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800"><?php echo $modalTitle; ?></h3>
            <button onclick="toggleModal('<?php echo $modalId; ?>')" class="text-gray-500 hover:text-red-500 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-6">
            <?php // Content is injected here by the parent page ?>
            ```
*(Note: You close the divs in the main page where you include this).*

---

**Status:** The **`/templates`** folder is now fully complete and matched to the structure.

Shall I proceed with the code for the **`/controllers`**?