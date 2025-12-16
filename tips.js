const ratings = [
    {}, // Placeholder for 0
    { title: "Very Easy", desc: "Walk in the park. paved paths, minimal elevation.", ex: "Mt. Samat" },
    { title: "Easy", desc: "Established trails, short hike (2-4 hours).", ex: "Mt. Pinatubo, Mt. Maculot (Rockies)" },
    { title: "Minor Climb", desc: "Moderate steepness. Requires basic cardio.", ex: "Mt. Batulao, Mt. Ulap" }, // 3
    { title: "Moderate", desc: "Whole day hike. Steep assaults. Can be tiring.", ex: "Mt. Pulag (Ambangeg), Mt. Ugo" },
    { title: "Challenging", desc: "Major climb. Steep, slippery, requires endurance.", ex: "Mt. Arayat, Mt. Napulauan" },
    { title: "Difficult", desc: "Multi-day hikes or very technical trails.", ex: "Mt. Tapulao, Mt. Isarog" }, // 6
    { title: "Very Difficult", desc: "Requires technical skills (ropes). High risk.", ex: "Mt. Guiting-Guiting (G2)" },
    { title: "Strenuous", desc: "Expedition style. Unpredictable terrain.", ex: "Mt. Mantalingajan" },
    { title: "Extreme", desc: "The toughest in PH. Mental & physical torture.", ex: "Mt. Halcon, Mt. Apo (Traverse)" } // 9
];

function updateDifficulty() {
    const val = document.getElementById('difficultyRange').value;
    const data = ratings[val];

    // Update Text
    document.getElementById('diffNumber').innerText = val + "/9";
    document.getElementById('diffTitle').innerText = data.title;
    document.getElementById('diffDesc').innerText = data.desc;
    document.getElementById('diffExample').innerText = data.ex;
}