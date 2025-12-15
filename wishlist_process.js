function toggleWishlist(button) {
    const tourId = button.getAttribute('data-id');
    const icon = button.querySelector('i');
    const span = button.querySelector('span');

    const formData = new FormData();
    formData.append('tour_id', tourId);

    fetch('wishlist_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (data.action === 'added') {
                button.classList.remove('btn-outline-light');
                button.classList.add('btn-danger', 'text-white');
                icon.classList.remove('fa-regular');
                icon.classList.add('fa-solid');
                span.innerText = "Saved to Wishlist";
            } else if (data.action === 'removed') {
                button.classList.remove('btn-danger', 'text-white');
                button.classList.add('btn-outline-light');
                icon.classList.remove('fa-solid');
                icon.classList.add('fa-regular');
                span.innerText = "Add to Wishlist";
            }
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}