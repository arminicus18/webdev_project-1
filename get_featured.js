// Wait for the DOM to be fully loaded before running the script
document.addEventListener("DOMContentLoaded", function() {
    fetchTours();
});

async function fetchTours() {
    try {
        // 1. Call the PHP API (Backend)
        const response = await fetch('get_featured.php'); 
        
        // 2. Convert the response to JSON data
        const tours = await response.json();

        // 3. Select the HTML elements
        const container = document.getElementById('tours-container');
        const loadingMsg = document.getElementById('loading-msg');
        
        // Remove the loading text
        if(loadingMsg) loadingMsg.remove();

        // 4. Loop through the tours and generate HTML for each one
        tours.forEach(tour => {
            // Extract variables from the data object
            const name = tour.TOUR_NAME;
            const image = tour.IMAGE_URL;
            const location = tour.LOCATION;
            const rating = tour.RATING;
            const reviews = tour.REVIEWS;
            const price = tour.PRICE;

            // Build the card HTML string
            const cardHTML = `
                <div class="col-lg-3 offset-lg-0 col-md-6 d-flex align-items-stretch">
                    <a href="#" class="text-decoration-none w-100">
                        <div class="card tour-card">
                            <div class="card-image-wrapper">
                                <div class="zoom-wrapper">
                                    <img src="${image}" class="card-img-top" alt="${name}">
                                </div>
                                <button class="btn btn-light rounded-circle favorite-btn" aria-label="Add to favorites">
                                    <i class="fa-regular fa-bookmark fav-btn"></i>
                                </button>
                            </div>
                            <div class="card-body">
                                <span class="location-tag">${location}</span>
                                <h5 class="card-title tour-title">${name}</h5>
                                
                                <div class="d-flex align-items-center mb-2">
                                    <span class="rating-number">${rating}</span>
                                    <span class="rating-stars me-2">
                                        <i class="fa-solid fa-circle"></i><i class="fa-solid fa-circle"></i><i class="fa-solid fa-circle"></i><i class="fa-solid fa-circle"></i><i class="fa-solid fa-circle"></i>
                                    </span>
                                    <span class="review-count">${reviews}</span>
                                </div>
                                
                                <div class="price-display mt-10">
                                    <span class="price-label">from</span>
                                    <span class="price-amount">₱${price}</span>
                                    <span class="price-unit">per person</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            `;

            // Append the new card to the container
            container.innerHTML += cardHTML;
        });

    } catch (error) {
        console.error('Error fetching tours:', error);
        document.getElementById('tours-container').innerHTML = '<p class="text-white text-center">Failed to load tours. Please check the backend connection.</p>';
    }
}