document.addEventListener("DOMContentLoaded", function () {

    let lastScrollTop = 0;

    // 2. NOW we look for the navbar (it is guaranteed to exist now)
    const navbar = document.querySelector('.navbar-custom');

    // Safety check: verify we actually found it
    if (navbar) {
        window.addEventListener('scroll', function () {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop < 0) {
                scrollTop = 0;
            }

            if (scrollTop > lastScrollTop) {
                // DOWNSCROLL
                navbar.classList.add('navbar-hidden');
            } else {
                // UPSCROLL
                navbar.classList.remove('navbar-hidden');
            }

            lastScrollTop = scrollTop;
        });
    } else {
        console.error("Error: Could not find element with class 'navbar-custom'");
    }
});