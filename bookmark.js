// ================== BOOKMARK BUTTON LISTENER ==================

// We use "Event Delegation" here. We attach the click listener to the
// entire document.
document.addEventListener('click', function(event) {

    // 1. Check if the clicked element OR any of its parents is a '.favorite-btn'
    // .closest() is perfect for this because the user might click the <i> icon inside the button.
    const bookmarkButton = event.target.closest('.favorite-btn');

    // 2. If a bookmark button was indeed clicked
    if (bookmarkButton) {
        // Prevent unwanted default behaviors (good practice)
        event.preventDefault();
        bookmarkButton.blur(); // Removes focus ring after click

        console.log("Bookmark clicked! Triggering modal...");

        // --- FUTURE BACKEND CHECK HERE ---
        // In the future, you would put an "if" statement here:
        // if (userIsLoggedIn) { 
        //    saveToDatabase(tourID); 
        //    toggleBookmarkIconColor();
        // } else {
        //    showLoginModal();
        // }
        // ---------------------------------

        // 3. For now, assume logged out, and show the modal.
        // We need to instantiate the Bootstrap Modal via JS to show it.
        const modalElement = document.getElementById('loginPromptModal');
        const loginModal = new bootstrap.Modal(modalElement);
        loginModal.show();
    }
});