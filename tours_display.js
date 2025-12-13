document.addEventListener("DOMContentLoaded", () => {
    loadAllTours();
});

function loadAllTours() {
    fetch('tours_display.php')
        .then(response => response.json())
        .then(data => {
            // Filter data by CLASS column
            const beginners = data.filter(t => t.CLASS === 'Beginner');
            const intermediate = data.filter(t => t.CLASS === 'Intermediate');
            const advanced = data.filter(t => t.CLASS === 'Advance');
            const expert = data.filter(t => t.CLASS === 'Expert');

            // Inject into rows
            renderRow('beginner-row', beginners);
            renderRow('intermediate-row', intermediate);
            renderRow('advanced-row', advanced);
            renderRow('expert-row', expert);
        })
        .catch(err => console.error(err));
}

function renderRow(containerId, tours) {
    const container = document.getElementById(containerId);
    if (tours.length === 0) {
        container.innerHTML = '<p class="text-secondary ms-2">No hikes available in this category yet.</p>';
        return;
    }

    tours.forEach(tour => {
        // 1. Process Inclusions (Split string into array)
        let inclusionsHTML = '';
        if (tour.INC_LIST) {
            const incArray = tour.INC_LIST.split(',');
            // Take top 3
            const top3 = incArray.slice(0, 3);
            const remainder = incArray.length - 3;

            top3.forEach(item => {
                inclusionsHTML += `<div><i class="fa-solid fa-check text-success me-2"></i>${item}</div>`;
            });

            if (remainder > 0) {
                inclusionsHTML += `<div class="text-warning small mt-1">+ ${remainder} more</div>`;
            }
        } else {
            inclusionsHTML = '<div class="text-muted small">See details for inclusions</div>';
        }

        // 2. Generate Review Stars
        let stars = '';
        const rating = Math.round(tour.RATING);
        for (let i = 0; i < 5; i++) {
            if (i < rating) stars += '<i class="fa-solid fa-circle text-warning"></i>';
            else stars += '<i class="fa-regular fa-circle text-secondary"></i>';
        }

        // 3. The Card HTML
        // Wraps the card in an anchor tag pointing to tour_details.php with the ID
        const html = `
        <div class="col-lg-4">
            <a href="tour_details.php?id=${tour.TOUR_ID}" class="text-decoration-none">
                <div class="card card-custom h-100">
                    <div style="position: relative; height: 220px;">
                        <img src="${tour.IMAGE_URL}" class="card-img-top h-100 w-100" style="object-fit: cover;" alt="${tour.TOUR_NAME}">
                    </div>

                    <div class="card-body p-4">
                        <span class="location-tag">${tour.LOCATION}</span>
                        <h5 class="card-title text-success fw-bold mb-1">${tour.TOUR_NAME}</h5>
                        
                        <div class="mb-3">
                            <span class="fw-bold text-white me-2">${tour.RATING}</span>
                            ${stars}
                            <span class="text-secondary ms-1">(${tour.REVIEWS})</span>
                        </div>

                        <div class="price-display mb-3">
                            <small class="text-secondary">from</small> 
                            <span class="price-amount text-warning fw-bold fs-5">₱ ${parseFloat(tour.PRICE).toLocaleString('en-US', { minimumFractionDigits: 2 })}</span> 
                            <small class="text-secondary">per person</small>
                        </div>

                        <div class="inclusions-list mt-auto">
                            ${inclusionsHTML}
                        </div>
                    </div>
                </div>
            </a>
        </div>
        `;
        container.innerHTML += html;
    });
}

// 4. SCROLL LOGIC
function scrollRow(rowId, direction) {
    const container = document.getElementById(rowId);
    const scrollAmount = 400; // Width of one card
    if (direction === 1) {
        container.scrollLeft += scrollAmount;
    } else {
        container.scrollLeft -= scrollAmount;
    }
}