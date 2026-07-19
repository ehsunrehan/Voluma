document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.getElementById('userDropdown');
    const menu = document.getElementById('dropdownMenu');
    if (dropdown && menu) {
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
            menu.classList.toggle('show');
        });
        document.addEventListener('click', function() {
            dropdown.classList.remove('active');
            menu.classList.remove('show');
        });
    }
});