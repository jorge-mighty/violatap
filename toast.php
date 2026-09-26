<?php
// toast.php (Root Folder)
?>
<div class="toast-floating-wrapper" id="globalToastContainer"></div>

<script>
    function showToast(title, message, type = 'success', duration = 5000) {
        const container = document.getElementById('globalToastContainer');
        if (!container) return;

        const toast = document.createElement('article');
        toast.className = `toast-notification-card toast-${type}`;

        // Default icon (Success)
        let iconImg = '<img src="assets/icons/outline/check.svg" alt="Success Icon" class="asset-icon-img">';
        
        if (type === 'error') {
            iconImg = '<img src="assets/icons/outline/alert-triangle.svg" alt="Error Icon" class="asset-icon-img alert-icon-red">';
        } else if (type === 'warning') {
            iconImg = '<img src="assets/icons/outline/alert-circle.svg" alt="Warning Icon" class="asset-icon-img" style="filter: brightness(0) saturate(100%) invert(80%) sepia(70%) saturate(1000%) hue-rotate(350deg);">';
        } else if (type === 'info') {
            iconImg = '<img src="assets/icons/outline/info-circle.svg" alt="Info Icon" class="asset-icon-img">';
        } else if (type === 'notification') {
            iconImg = '<img src="assets/icons/outline/bell.svg" alt="Notification Icon" class="asset-icon-img">';
        }

        toast.innerHTML = `
            <div class="toast-icon-wrapper">${iconImg}</div>
            <div class="toast-content-wrapper">
                <strong>${title}</strong>
                <p>${message}</p>
            </div>
            <button type="button" class="toast-close-btn" aria-label="Close Toast">&times;</button>
            <div class="toast-countdown-progress" style="animation-duration: ${duration}ms;"></div>
        `;

        const closeBtn = toast.querySelector('.toast-close-btn');
        const dismiss = () => {
            toast.classList.add('toast-hiding');
            setTimeout(() => toast.remove(), 300);
        };

        closeBtn.addEventListener('click', dismiss);
        container.appendChild(toast);

        setTimeout(dismiss, duration);
    }
</script>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['flash_message'])): 
    $msgText = htmlspecialchars($_SESSION['flash_message'], ENT_QUOTES, 'UTF-8');
    $msgType = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof showToast === 'function') {
                showToast('System Notification', '<?php echo $msgText; ?>', '<?php echo $msgType; ?>');
            }
        });
    </script>
<?php endif; ?>