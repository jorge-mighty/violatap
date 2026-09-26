<?php
// modals.php (Root Folder)
?>
<div class="modal-backdrop-overlay" id="globalModalBackdrop">
    <article class="modal-card-wrapper" id="globalModalCard">
        <header class="modal-card-header">
            <h3 id="globalModalTitle">Confirmation</h3>
            <button type="button" class="modal-close-btn" id="globalModalCloseBtn" aria-label="Close Modal">&times;</button>
        </header>
        <section class="modal-body" id="globalModalBody">
            <p id="globalModalMessage">Are you sure you want to proceed with this action?</p>
        </section>
        <footer class="modal-actions-row" id="globalModalFooter">
            <button type="button" class="btn btn-secondary" id="globalModalCancelBtn">Cancel</button>
            <button type="button" class="btn btn-primary" id="globalModalConfirmBtn">Confirm</button>
        </footer>
    </article>
</div>

<script>
    function showGlobalModal(title, message, onConfirmCallback, confirmText = 'Confirm', isDanger = false) {
        const backdrop = document.getElementById('globalModalBackdrop');
        const titleEl = document.getElementById('globalModalTitle');
        const msgEl = document.getElementById('globalModalMessage');
        const confirmBtn = document.getElementById('globalModalConfirmBtn');
        
        if (titleEl) titleEl.textContent = title;
        if (msgEl) msgEl.textContent = message;
        if (confirmBtn) {
            confirmBtn.textContent = confirmText;
            confirmBtn.className = isDanger ? 'btn btn-danger-active' : 'btn btn-primary';
            
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            newConfirmBtn.addEventListener('click', () => {
                hideGlobalModal();
                if (typeof onConfirmCallback === 'function') {
                    onConfirmCallback();
                }
            });
        }

        if (backdrop) backdrop.classList.add('active');
    }

    function hideGlobalModal() {
        const backdrop = document.getElementById('globalModalBackdrop');
        if (backdrop) backdrop.classList.remove('active');
    }

    document.getElementById('globalModalCloseBtn')?.addEventListener('click', hideGlobalModal);
    document.getElementById('globalModalCancelBtn')?.addEventListener('click', hideGlobalModal);
    document.getElementById('globalModalBackdrop')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) hideGlobalModal();
    });
</script>