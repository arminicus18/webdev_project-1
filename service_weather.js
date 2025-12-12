document.addEventListener("DOMContentLoaded", function () {
    
    // 1. Get location from the Carousel data attribute
    const wrapper = document.getElementById('tourGalleryCarousel');
    if (!wrapper) return;
    
    const location = wrapper.getAttribute('data-location'); // e.g. "Kabayan, Benguet"
    if (!location) return;

    // 2. Fetch Data
    fetch(`service_weather.php?loc=${encodeURIComponent(location)}`)
        .then(response => response.json())
        .then(data => {
            if (!data || data.error) return;

            // --- A. DESKTOP SIDEBAR WIDGET ---
            const desktopCard = document.getElementById('weather-card');
            if (desktopCard) {
                desktopCard.style.display = 'block';
                
                const current = data.current;
                const todayForecast = data.forecast.forecastday[0].day;
                
                document.getElementById('w-icon').src = 'https:' + current.condition.icon;
                document.getElementById('w-temp').innerText = Math.round(current.temp_c) + "°C";
                document.getElementById('w-text').innerText = current.condition.text;
                document.getElementById('w-rain').innerText = todayForecast.daily_chance_of_rain + "%";

                // Desktop Forecast Loop
                const forecastContainer = document.getElementById('forecast-container');
                forecastContainer.innerHTML = ''; 

                data.forecast.forecastday.forEach(day => {
                    const dateObj = new Date(day.date);
                    const dayName = dateObj.toLocaleDateString('en-US', { weekday: 'short' });
                    
                    const html = `
                        <div class="col-4 border-end border-secondary last-no-border">
                            <small class="text-secondary d-block mb-1">${dayName}</small>
                            <img src="https:${day.day.condition.icon}" style="width: 30px;">
                            <div class="small fw-bold text-white">${Math.round(day.day.maxtemp_c)}°</div>
                            <small class="text-muted" style="font-size: 0.7rem;">${day.day.daily_chance_of_rain}% Rain</small>
                        </div>
                    `;
                    forecastContainer.innerHTML += html;
                });
            }

            // --- B. MOBILE BOTTOM BAR WIDGET (NEW) ---
            const mobileRow = document.getElementById('mobile-weather-row');
            if (mobileRow) {
                // Show the row (overriding display: none !important)
                mobileRow.style.setProperty('display', 'flex', 'important');
                
                // Populate compact data
                document.getElementById('mw-icon').src = 'https:' + data.current.condition.icon;
                document.getElementById('mw-text').innerText = data.current.condition.text;
                document.getElementById('mw-temp').innerText = Math.round(data.current.temp_c) + "°C";
            }
        })
        .catch(err => console.error("Weather Error:", err));
});