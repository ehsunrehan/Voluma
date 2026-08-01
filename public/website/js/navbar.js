document.addEventListener('click', function (e) {

    const dropdown = document.getElementById('userDropdown');
    const menu = document.getElementById('dropdownMenu');

    if (dropdown && menu) {
        if (dropdown.contains(e.target)) {
            dropdown.classList.toggle('active');
            menu.classList.toggle('show');
        } else if (!menu.contains(e.target)) {
            dropdown.classList.remove('active');
            menu.classList.remove('show');
        }
    }

    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');

    if (navToggle && navLinks) {
        if (navToggle.contains(e.target)) {
            navToggle.classList.toggle('active');
            navLinks.classList.toggle('open');
        } else if (navLinks.contains(e.target) && e.target.tagName === 'A') {
            navToggle.classList.remove('active');
            navLinks.classList.remove('open');
        } else if (!navLinks.contains(e.target) && !navToggle.contains(e.target)) {
            navToggle.classList.remove('active');
            navLinks.classList.remove('open');
        }
    }

});