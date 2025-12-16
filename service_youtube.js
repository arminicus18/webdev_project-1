
const API_KEY = 'AIzaSyBTa4poOOkLGkpVQOiCRGJ5ndkkQxvocQg';

// 2. CONFIGURATION
const SEARCH_TERM = 'Hiking Safety Philippines Guide'; // You can change this query
const MAX_RESULTS = 3;

// 3. EXECUTE ON PAGE LOAD
document.addEventListener("DOMContentLoaded", function () {
    fetchVideos();
});

function fetchVideos() {
    const url = `https://www.googleapis.com/youtube/v3/search?part=snippet&q=${SEARCH_TERM}&type=video&maxResults=${MAX_RESULTS}&key=${API_KEY}`;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error("API Error or Limit Reached");
            }
            return response.json();
        })
        .then(data => {
            displayVideos(data.items);
        })
        .catch(error => {
            console.warn("YouTube API failed, switching to fallback:", error);
            // FALLBACK: If API fails, show these specific videos so the page isn't empty
            const fallbackData = [
                { id: { videoId: 'wO6pLClGj2w' }, snippet: { title: 'Basic Mountaineering Course (BMC)', channelTitle: 'Pinoy Mountaineer' } },
                { id: { videoId: '7K0Y_tVvQjw' }, snippet: { title: 'LNT: Leave No Trace Principles', channelTitle: 'REI' } },
                { id: { videoId: 'yvL-iY6r-K8' }, snippet: { title: 'Hiking Preparation for Beginners', channelTitle: 'The Hike' } }
            ];
            displayVideos(fallbackData);
        });
}

function displayVideos(videos) {
    const container = document.getElementById('video-container');
    container.innerHTML = ""; // Clear the loading spinner

    videos.forEach(video => {
        const videoId = video.id.videoId;
        const title = video.snippet.title;
        const channel = video.snippet.channelTitle;

        // Create the HTML Card for each video
        const cardHTML = `
                    <div class="col-md-4 mb-4">
                        <div class="card h-100" style="background-color: #1e1e1e; border: 1px solid #333; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
                            <div class="ratio ratio-16x9">
                               <iframe src="https://www.youtube.com/embed/${videoId}" title="YouTube video" allowfullscreen style="border-radius: 5px 5px 0 0;"></iframe>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title text-warning text-truncate" style="font-size: 1rem;">${title}</h5>
                                <p class="card-text text-secondary" style="font-size: 0.8rem;">
                                    <i class="bi bi-camera-video-fill"></i> ${channel}
                                </p>
                            </div>
                        </div>
                    </div>
                `;
        container.innerHTML += cardHTML;
    });
}


// --- SMART PACKING LIST LOGIC ---

// 1. Select all checkboxes with class 'pack-item'
const checkboxes = document.querySelectorAll('.pack-item');
const progressBar = document.getElementById('packProgressBar');
const progressText = document.getElementById('packProgressText');

// 2. Load saved state from LocalStorage on page load
document.addEventListener("DOMContentLoaded", function () {
    checkboxes.forEach(box => {
        // Check if this specific item ID was saved as 'true'
        const isChecked = localStorage.getItem(box.id) === 'true';
        box.checked = isChecked;
    });
    updateProgress(); // Update bar based on loaded data
});

// 3. Listen for clicks
checkboxes.forEach(box => {
    box.addEventListener('change', function () {
        // Save status to browser memory
        localStorage.setItem(this.id, this.checked);
        updateProgress();
    });
});

// 4. Update Progress Bar Function
function updateProgress() {
    const total = checkboxes.length;
    const checkedCount = document.querySelectorAll('.pack-item:checked').length;
    const percentage = Math.round((checkedCount / total) * 100);

    // Update CSS width and text
    progressBar.style.width = percentage + "%";
    progressText.innerText = percentage + "% Ready";

    // Change color to Green when 100%
    if (percentage === 100) {
        progressBar.classList.remove('bg-warning');
        progressBar.classList.add('bg-success');
        progressText.innerText = "100% Ready - Let's Go!";
        progressText.classList.add('text-success');
    } else {
        progressBar.classList.add('bg-warning');
        progressBar.classList.remove('bg-success');
    }
}

// 5. Reset Button
function resetPackingList() {
    checkboxes.forEach(box => {
        box.checked = false;
        localStorage.removeItem(box.id);
    });
    updateProgress();
}