/**
 * public/js/inventory.js
 */

function setupTransactionForm(itemId, itemName, currentStock, unit) {
    // This helper populates the modal fields dynamically
    document.getElementById('txnInvId').value = itemId;
    document.getElementById('txnItemDisplay').innerText = `${itemName} (Current: ${currentStock} ${unit})`;
    document.getElementById('txnUnit').innerText = unit;
    
    // Reset form
    document.getElementById('txnQty').value = '';
    document.getElementById('txnNotes').value = '';
    
    toggleModal('txnModal');
}