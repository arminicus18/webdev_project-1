document.addEventListener("DOMContentLoaded", function () {
    
    const carouselWrapper = document.getElementById('tourGalleryCarousel');
    const slideContainer = document.getElementById('gallery-container');
    const indicatorContainer = document.getElementById('carousel-indicators');

    if (!carouselWrapper || !slideContainer) return;

    // 1. GET RAW DATA
    const rawName = carouselWrapper.getAttribute('data-name');
    const rawLocation = carouselWrapper.getAttribute('data-location');

    if (!rawName) return;

    // 2. CLEAN THE NAME
    let cleanName = rawName.split(' via ')[0];
    cleanName = cleanName.replace(/\(.*?\)/g, '');
    const noiseWords = /Dayhike|Overnight|Traverse|Tour|Major Climb|Minor Climb|Eco-Trail|Trilogy|Adventure/gi;
    let tourName = cleanName.replace(noiseWords, '').trim();

    console.log(`Requesting Gallery. Name: [${tourName}], Location: [${rawLocation}]`);

    // 3. SEND SEPARATE PARAMETERS TO PHP
    // Notice we use ?name=...&loc=... instead of ?search=...
    const apiUrl = `service_gallery.php?name=${encodeURIComponent(tourName)}&loc=${encodeURIComponent(rawLocation)}`;

    // 4. FETCH
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            if (data.results && data.results.length > 0) {
                slideContainer.innerHTML = ''; 
                indicatorContainer.innerHTML = '';

                data.results.forEach((photo, index) => {
                    const isActive = index === 0 ? 'active' : '';

                    // Indicator
                    const dot = `
                        <button type="button" data-bs-target="#tourGalleryCarousel" 
                                data-bs-slide-to="${index}" 
                                class="${isActive}" aria-label="Slide ${index + 1}"></button>
                    `;
                    indicatorContainer.innerHTML += dot;

                    // Slide
                    const slide = `
                        <div class="carousel-item ${isActive}" style="height: 450px; background-color: #000;">
                            <img src="${photo.urls.regular}" class="d-block w-100 h-100" 
                                 alt="${photo.alt_description}" 
                                 style="object-fit: cover; opacity: 0.9;">
                            
                            <div class="carousel-caption d-none d-md-block" 
                                 style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); bottom: 0; left: 0; right: 0; padding-bottom: 20px;">
                                <p class="mb-0 small text-light">
                                    <i class="fa-solid fa-camera"></i> Photo by ${photo.user.name} on Unsplash
                                </p>
                            </div>
                        </div>
                    `;
                    slideContainer.innerHTML += slide;
                });

            } else {
                slideContainer.innerHTML = '<div class="d-flex align-items-center justify-content-center" style="height:300px;"><p class="text-muted">No images found.</p></div>';
            }
        })
        .catch(error => {
            console.error('Gallery Error:', error);
            slideContainer.innerHTML = '<div class="d-flex align-items-center justify-content-center" style="height:300px;"><p class="text-muted">Could not load images.</p></div>';
        });
});