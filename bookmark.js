
document.addEventListener('click', function(event) {

    // 1. Check if the clicked element OR any of its parents is a '.favorite-btn'

    const bookmarkButton = event.target.closest('.favorite-btn');

    // 2. If a bookmark button was indeed clicked
    if (bookmarkButton) {
        // Prevent unwanted default behaviors (good practice)
        event.preventDefault();
        bookmarkButton.blur(); // Removes focus ring after click

        console.log("Bookmark clicked! Triggering modal...");

        const modalElement = document.getElementById('loginPromptModal');
        const loginModal = new bootstrap.Modal(modalElement);
        loginModal.show();
    }
});