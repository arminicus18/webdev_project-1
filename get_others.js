// Run both functions when the page loads
document.addEventListener("DOMContentLoaded", function() {
    fetchOthers(); // The new Others function
});

async function fetchOthers() {
    try {
        // 1. Call the new PHP API
        // Make sure this path is correct relative to your HTML file
        const response = await fetch('get_others.php'); 
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const tours = await response.json();

        // 2. Select the specific container for "Mountains and More"
        const container = document.getElementById('others-container');
        const loadingMsg = document.getElementById('loading-others');

        // Remove loading text
        if(loadingMsg) loadingMsg.remove();

        // 3. Generate the Cards (Matches your specific HTML structure)
        tours.forEach(tour => {
            // Data mapping (Matches your PHP JSON keys)
            const name = tour.TOUR_NAME;
            const image = tour.IMAGE_URL;
            const location = tour.LOCATION;
            const rating = tour.RATING;
            const reviews = tour.REVIEWS;
            const price = tour.PRICE; // Already formatted with commas by PHP
            const id = tour.TOUR_ID;

            const cardHTML = `
                <div class="col-lg-3 offset-lg-0 col-md-6 d-flex align-items-stretch mb-4">
                    <a href="tour_details.php?id=${id}" class="text-decoration-none w-100">
                        <div class="card tour-card">
                            <div class="card-image-wrapper">
                                <div class="zoom-wrapper">
                                    <img src="${image}" class="card-img-top" alt="${name}" 
                                         onerror="this.src='assets/placeholder.jpg'">
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
                                        <i class="fa-solid fa-circle"></i>
                                        <i class="fa-solid fa-circle"></i>
                                        <i class="fa-solid fa-circle"></i>
                                        <i class="fa-solid fa-circle"></i>
                                        <i class="fa-solid fa-circle"></i>
                                    </span>
                                    <span class="review-count">${reviews}</span>
                                </div>
                                
                                <div class="price-display">
                                    <span class="price-label">from</span>
                                    <span class="price-amount">₱${price}</span>
                                    <span class="price-unit">per person</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            `;

            container.innerHTML += cardHTML;
        });

    } catch (error) {
        console.error('Error fetching others:', error);
        const container = document.getElementById('others-container');
        if (container) {
            container.innerHTML = '<p class="text-center text-danger">Unable to load adventures at this time.</p>';
        }
    }
}