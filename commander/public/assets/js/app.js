document.addEventListener('DOMContentLoaded', () => {
    // Sidebar Toggle (Mobile)
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.createElement('button');
    toggleBtn.classList.add('mobile-toggle');
    toggleBtn.innerHTML = '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>';

    // Add toggle button to header on mobile only (handled via CSS mostly, but JS logic here)
    if (window.innerWidth < 768) {
        document.querySelector('.top-bar').prepend(toggleBtn);

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }

    // Interactive Rows
    const rows = document.querySelectorAll('tr[data-href]');
    rows.forEach(row => {
        row.addEventListener('click', () => {
            window.location.href = row.dataset.href;
        });
        row.style.cursor = 'pointer';
    });

    // Alert Dismissal
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        alert.addEventListener('click', () => {
            alert.remove();
        });
    });
});
