document.addEventListener("DOMContentLoaded", () => {
    loadOtherTours();
});

function loadOtherTours() {
    // Fetch from the NEW separate PHP file
    fetch('tours_others.php')
        .then(response => response.json())
        .then(data => {
            if(data.error) {
                console.error("DB Error:", data.error);
                return;
            }

            // No need to filter by Class here, the PHP already did the work.
            // Just pass ALL the data to the renderer.
            renderOtherRow('others-row', data);
        })
        .catch(err => console.error("Fetch Error:", err));
}

function renderOtherRow(containerId, tours) {
    const container = document.getElementById(containerId);
    if (!container) return; 

    container.innerHTML = ''; 

    if(tours.length === 0) {
        container.innerHTML = '<p class="text-secondary ms-2 small">No adventures found.</p>';
        return;
    }

    tours.forEach(tour => {
        // 1. Process Inclusions
        let inclusionsHTML = '';
        if (tour.INC_LIST) {
            const incArray = tour.INC_LIST.split(',');
            const top3 = incArray.slice(0, 3);
            const remainder = incArray.length - 3;
            
            top3.forEach(item => {
                inclusionsHTML += `<div><i class="fa-solid fa-check text-success me-2"></i>${item}</div>`;
            });
            
            if (remainder > 0) {
                inclusionsHTML += `<div class="text-warning small mt-1 fw-bold">+ ${remainder} more</div>`;
            }
        } else {
            inclusionsHTML = '<div class="text-muted small">See details for inclusions</div>';
        }

        // 2. Stars
        let starsHTML = '';
        const rating = Math.round(tour.RATING); 
        for(let i=0; i<5; i++) {
            if(i < rating) starsHTML += '<i class="fa-solid fa-circle text-warning"></i>';
            else starsHTML += '<i class="fa-regular fa-circle text-secondary"></i>';
        }

        const price = parseFloat(tour.PRICE).toLocaleString('en-US', {minimumFractionDigits: 2});

        // 3. Card HTML (Exact same design as Hikes)
        const html = `
        <div class="col-lg-4">
            <a href="tour_details.php?id=${tour.TOUR_ID}" class="text-decoration-none">
                <div class="card card-custom h-100">
                    <div style="position: relative; height: 220px;">
                        <img src="${tour.IMAGE_URL}" class="card-img-top h-100 w-100" style="object-fit: cover;" alt="${tour.TOUR_NAME}">
                    </div>

                    <div class="card-body p-4">
                        <span class="location-tag align-self-start">${tour.LOCATION}</span>
                        <h5 class="card-title text-success fw-bold mb-1">${tour.TOUR_NAME}</h5>
                        
                        <div class="mb-3">
                            <span class="fw-bold text-white me-2">${tour.RATING}</span>
                            ${starsHTML}
                            <span class="text-secondary ms-1">(${tour.REVIEWS})</span>
                        </div>

                        <div class="price-display mb-3">
                            <small class="text-secondary price-label">from</small> 
                            <span class="price-amount text-warning fw-bold fs-5">₱ ${price}</span> 
                            <small class="text-secondary price-unit">per person</small>
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