const scriptURL = "https://script.google.com/macros/s/AKfycbw60n2Ngl8uONElO4C-ch4sOQfeERnFyaw-oZ5iRgTpNidm_e0r4eFXmscOixNwDDgaow/exec"; // <-- Deine echte URL einfügen!

const tasksPerDay = [
  { time: "05:30", task: "Aufstehen & Wasser" },
  { time: "05:35", task: "Stretching / Hampelmänner" },
  { time: "05:50", task: "Supplemente morgens" },
  { time: "06:00", task: "Fertigmachen" },
  { time: "09:30", task: "Snack" },
  { time: "13:30", task: "Mini-Pause" },
  { time: "16:30", task: "Training / Freundin" },
  { time: "20:00", task: "Chillen" },
  { time: "21:30", task: "Supplemente abends" },
  { time: "21:45", task: "Dehnung / Journaling" },
  { time: "22:00", task: "Podcast / Schlaf" }
];

const days = ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"];
const planner = document.getElementById("planner");

days.forEach(day => {
  const card = document.createElement("div");
  card.className = "day-card";
  card.innerHTML = `<h2>${day}</h2>`;

  tasksPerDay.forEach(({ time, task }) => {
    const id = `${day}_${task}`.replace(/\s+/g, "_");
    card.innerHTML += `
      <div class="task">
        <label>
          <input type="checkbox" id="${id}"> 
          <strong>${time}</strong> – ${task}
        </label>
      </div>`;
  });

  card.innerHTML += `
    <h3>🍽️ Frühstück</h3>
    <textarea id="${day}_Frühstück" rows="2" placeholder="Was gab's?"></textarea>
    <p><em>Letzter Eintrag:</em> <span id="${day}_Frühstück_last"></span></p>

    <h3>🥗 Mittagessen</h3>
    <textarea id="${day}_Mittagessen" rows="2" placeholder="Was gab's?"></textarea>
    <p><em>Letzter Eintrag:</em> <span id="${day}_Mittagessen_last"></span></p>

    <h3>🌙 Abendessen</h3>
    <textarea id="${day}_Abendessen" rows="2" placeholder="Was gab's?"></textarea>
    <p><em>Letzter Eintrag:</em> <span id="${day}_Abendessen_last"></span></p>

    <h3>🥤 Greens-Shake (fest)</h3>
    <ul>
      <li>200–250 ml Wasser oder Pflanzenmilch</li>
      <li>1 TL Spirulina</li>
      <li>1 TL Moringa</li>
      <li>1 TL Leinsamen</li>
      <li>1 TL Zitronensaft</li>
      <li>1 TL Leinöl</li>
      <li>Handvoll Beeren</li>
    </ul>
  `;

  planner.appendChild(card);
});

// Speichern
document.getElementById("saveBtn").addEventListener("click", async () => {
  const date = new Date().toISOString().split("T")[0];

  for (const day of days) {
    for (const { task } of tasksPerDay) {
      const id = `${day}_${task}`.replace(/\s+/g, "_");
      const checked = document.getElementById(id).checked;
      await sendToSheet({ date, day, time: task, type: "Aufgabe", value: checked ? "ja" : "nein" });
    }

    const meals = ["Frühstück", "Mittagessen", "Abendessen"];
    for (const meal of meals) {
      const id = `${day}_${meal}`;
      const value = document.getElementById(id).value;
      if (value.trim()) {
        await sendToSheet({ date, day, time: meal, type: meal, value });
        document.getElementById(`${id}_last`).textContent = value;
      }
    }
  }

  alert("✅ Erfolgreich gespeichert!");
});

// Sheets schreiben
async function sendToSheet(data) {
  await fetch(scriptURL, {
    method: "POST",
    body: JSON.stringify(data),
  });
}

// 📊 Auswertung anzeigen
document.getElementById("loadReport").addEventListener("click", async () => {
  const range = document.getElementById("reportRange").value;
  const data = await fetchReportData(range);
  drawChart(data);
});

// 📥 Daten abrufen von Apps Script
async function fetchReportData(range) {
  const response = await fetch(`${scriptURL}?range=${range}`);
  return await response.json();
}

// 📈 Chart.js Diagramm zeichnen
function drawChart(data) {
  const labels = Object.keys(data);
  const values = Object.values(data);

  const ctx = document.getElementById("reportChart").getContext("2d");
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Anzahl erledigt',
        data: values,
        backgroundColor: '#4caf50'
      }]
    },
    options: {
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
}
