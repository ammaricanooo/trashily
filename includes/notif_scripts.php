<script>
// Base URL — di-set otomatis dari PHP agar relative path bekerja di lokal maupun production
const BASE_URL = '<?= defined("BASE_URL") ? BASE_URL : "" ?>';
</script>
<script>
// Notif admin dropdown
if (typeof toggleAdminNotifDropdown === 'undefined') {
    function toggleAdminNotifDropdown(e) {
        e.stopPropagation();
        const dd = document.getElementById('adminNotifDropdown');
        if (dd) dd.classList.toggle('open');
    }
    document.addEventListener('click', function(e) {
        const dd = document.getElementById('adminNotifDropdown');
        const btn = document.getElementById('btnAdminNotif');
        if (dd && dd.classList.contains('open') && !dd.contains(e.target) && (!btn || !btn.contains(e.target))) {
            dd.classList.remove('open');
        }
    });
}

// Notif customer dropdown
if (typeof toggleCustomerNotifDropdown === 'undefined') {
    function toggleCustomerNotifDropdown(e) {
        e.stopPropagation();
        const dd = document.getElementById('customerNotifDropdown');
        if (dd) dd.classList.toggle('open');
    }
    document.addEventListener('click', function(e) {
        const dd = document.getElementById('customerNotifDropdown');
        const btn = document.getElementById('btnCustomerNotif');
        if (dd && dd.classList.contains('open') && !dd.contains(e.target) && (!btn || !btn.contains(e.target))) {
            dd.classList.remove('open');
        }
    });
}
</script>
