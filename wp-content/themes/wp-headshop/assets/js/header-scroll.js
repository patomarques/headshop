document.addEventListener('DOMContentLoaded', function() {
    var header = document.getElementById('masthead');
    var body = document.body;
    
    if (!header) return;

    function updateHeader() {
        if (window.scrollY > 50) {
            header.classList.add('site-header-scrolled');
        } else {
            header.classList.remove('site-header-scrolled');
        }
    }

    // Run on init in case page is reloaded scrolled down
    updateHeader();

    window.addEventListener('scroll', function() {
        updateHeader();
    });
});
