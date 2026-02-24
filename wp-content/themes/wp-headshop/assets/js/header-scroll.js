document.addEventListener('DOMContentLoaded', function() {
    var header = document.getElementById('masthead');

    if (!header) return;

    var isHome = document.body.classList.contains('home') || document.body.classList.contains('front-page');
    var banner = null;
    var nextSection = null;

    function recalcRefs() {
        if (!isHome) return;
        banner = document.querySelector('.banner-slider-wrapper, .headshop-banner');
        nextSection = document.querySelector('.banner-slider-wrapper + section, .headshop-banner + section') ||
            document.querySelector('.headshop-categories');
    }

    function updateHeader() {
        if (!isHome) {
            header.classList.add('site-header-scrolled');
            return;
        }

        var scrolled = window.pageYOffset || document.documentElement.scrollTop || 0;
        var headerHeight = header.offsetHeight || 0;
        var shouldCompact = false;

        if (nextSection) {
            var nextSectionTop = nextSection.getBoundingClientRect().top + scrolled;
            shouldCompact = (scrolled + headerHeight) >= nextSectionTop;
        } else if (banner) {
            shouldCompact = banner.getBoundingClientRect().bottom <= headerHeight;
        } else {
            shouldCompact = scrolled > 100;
        }

        header.classList.toggle('site-header-scrolled', shouldCompact);
    }

    recalcRefs();
    // Run on init in case page is reloaded scrolled down
    updateHeader();

    window.addEventListener('load', function() {
        recalcRefs();
        updateHeader();
    });
    window.addEventListener('resize', function() {
        recalcRefs();
        updateHeader();
    });
    window.addEventListener('orientationchange', function() {
        recalcRefs();
        updateHeader();
    });
    window.addEventListener('scroll', updateHeader);
});
