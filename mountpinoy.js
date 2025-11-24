const activePage = window.location.pathname.split("/").pop(); // get file name only
const navLinks = document.querySelectorAll("nav a");

navLinks.forEach(link => {
    const linkPage = link.getAttribute("href").split("/").pop();

    if (linkPage === activePage) {
        link.classList.add("active");
        link.setAttribute("aria-current", "page");
    }
});


