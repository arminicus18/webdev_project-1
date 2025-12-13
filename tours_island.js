let allTourData = [];
let currentIslandStr = 'Luzon';

document.addEventListener("DOMContentLoaded", () => {
    // 1. CHECK URL PARAMETER
    // This grabs 'Visayas' from 'tours.html?region=Visayas'
    const params = new URLSearchParams(window.location.search);
    const regionParam = params.get('region');

    // If the URL has a valid region, override the default 'Luzon'
    if (regionParam && ['Luzon', 'Visayas', 'Mindanao'].includes(regionParam)) {
        currentIslandStr = regionParam;
    }

    // 2. FETCH DATA
    // This will now use the correct region immediately
    fetchAllToursForIslands();

    // 3. SETUP NAVPILLS & SET ACTIVE STATE
    const islandPills = document.querySelectorAll('.island-pill');
    islandPills.forEach(pill => {

        // VISUAL FIX: Ensure the correct pill is highlighted on load
        if (pill.textContent.trim() === currentIslandStr) {
            pill.classList.add('active');
        } else {
            pill.classList.remove('active');
        }

        // Add Click Listener
        pill.addEventListener('click', (e) => {
            e.preventDefault();
            // Visual Update
            islandPills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');

            // Logic Update
            currentIslandStr = pill.textContent.trim();
            renderIslandGrid();
        });
    });
});

function fetchAllToursForIslands() {
    // Make sure tours_all.php exists!
    fetch('tours_island.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) return;
            allTourData = data;
            renderIslandGrid(); // Initial Load
        })
        .catch(err => console.error(err));
}

function renderIslandGrid() {
    const container = document.getElementById('island-grid-container');
    if (!container) return;

    // 1. Filter Data
    const islandTours = allTourData.filter(t => t.ISLAND === currentIslandStr);

    // 2. Clear Container
    container.innerHTML = '';

    // 3. Handle Empty State
    if (islandTours.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-person-hiking fa-3x text-secondary mb-3"></i>
                <p class="text-secondary">No adventures found in ${currentIslandStr} yet.</p>
            </div>`;
        return;
    }

    // 4. Render Compact Cards
    islandTours.forEach(tour => {
        const price = parseFloat(tour.PRICE).toLocaleString('en-US', { minimumFractionDigits: 2 });

        // Simple star generator
        let starsHTML = '';
        const rating = Math.round(tour.RATING);
        for (let i = 0; i < 5; i++) {
            starsHTML += (i < rating) ? '<i class="fa-solid fa-circle text-warning small me-1"></i>' : '<i class="fa-regular fa-circle text-secondary small me-1"></i>';
        }

        const html = `
        <div class="col-lg-4 col-md-6 animate-fade-in">
            <a href="tour_details.php?id=${tour.TOUR_ID}" class="compact-card d-flex">
                <div class="compact-img-container">
                    <img src="${tour.IMAGE_URL}" class="compact-img" alt="${tour.TOUR_NAME}">
                </div>
                
                <div class="compact-body">
                    <div class="compact-location"><i class="fa-solid fa-location-dot me-1"></i>${tour.LOCATION}</div>
                    <div class="compact-title">${tour.TOUR_NAME}</div>
                    
                    <div class="compact-meta">
                        <div class="compact-rating">
                            ${starsHTML} <span class="text-secondary ms-1">(${tour.REVIEWS})</span>
                        </div>
                        <div class="compact-price">₱${price}</div>
                    </div>
                </div>
            </a>
        </div>
        `;
        container.innerHTML += html;
    });
}