<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <title>BetterAdmin – Admincenter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-dark text-white p-4">
  <h2 class="text-center text-success mb-4">BetterAdmin – Admincenter</h2>

  <div class="row mb-5 text-dark">
    <div class="col-md-4"><canvas id="chartAktuell"></canvas></div>
    <div class="col-md-4"><canvas id="chartVergleich"></canvas></div>
    <div class="col-md-4"><canvas id="chartVerlauf"></canvas></div>
  </div>

  <div class="row g-4">
    <div class="col-md-4">
      <h4 class="text-center">Neu</h4>
      <div id="neu" class="bg-secondary p-2 rounded kanban-column"></div>
    </div>
    <div class="col-md-4">
      <h4 class="text-center">In Bearbeitung</h4>
      <div id="in_bearbeitung" class="bg-secondary p-2 rounded kanban-column"></div>
    </div>
    <div class="col-md-4">
      <h4 class="text-center">Erledigt</h4>
      <div id="erledigt" class="bg-secondary p-2 rounded kanban-column"></div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="ticketModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content bg-dark text-white">
        <div class="modal-header">
          <h5 class="modal-title">Anfrage Details</h5>
          <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="modalContent">Lade...</div>
      </div>
    </div>
  </div>

  <script>
    // Drag & Drop
    ["neu", "in_bearbeitung", "erledigt"].forEach(id => {
      new Sortable(document.getElementById(id), {
        group: "kanban",
        animation: 150,
        onEnd: e => {
          const id = e.item.dataset.id;
          const status = e.to.id;
          fetch('assets/Kontaktphp/update-status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id, status })
          }).then(() => loadTickets());
        }
      });
    });

    async function loadTickets() {
      const res = await fetch('assets/Kontaktphp/tickets.php');
      const data = await res.json();

      const counts = { neu: 0, in_bearbeitung: 0, erledigt: 0 };
      let stats = {
        aktueller: { count: 0, gesamt: 0 },
        letzter: { count: 0, gesamt: 0 },
        monate: {}
      };

      ["neu", "in_bearbeitung", "erledigt"].forEach(s => document.getElementById(s).innerHTML = "");

      data.forEach(t => {
        const erstellt = new Date(t.erstellt_am);
        const geaendert = new Date(t.status_geaendert_am);
        const dauerMin = Math.round((geaendert - erstellt) / 60000);
        const monat = erstellt.toISOString().substring(0, 7);
        const now = new Date();
        const thisMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`;
        const lastMonth = `${now.getFullYear()}-${String(now.getMonth()).padStart(2, "0")}`;

        // Zähler & Statistik
        counts[t.status]++;
        stats.monate[monat] = stats.monate[monat] || { count: 0, gesamt: 0 };
        stats.monate[monat].count++;
        stats.monate[monat].gesamt += dauerMin;
        if (monat === thisMonth) {
          stats.aktueller.count++;
          stats.aktueller.gesamt += dauerMin;
        }
        if (monat === lastMonth) {
          stats.letzter.count++;
          stats.letzter.gesamt += dauerMin;
        }

        const div = document.createElement('div');
        div.className = "p-2 my-2 bg-dark text-white rounded border ticket";
        div.dataset.id = t.id;
        div.dataset.timestamp = t.status_geaendert_am;
        div.innerHTML = `
          <strong>${t.vorname} ${t.nachname}</strong><br>
          <small>${t.email}</small><br>
          <span class="badge bg-secondary mt-1 live-timer">${formatDauer(dauerMin)}</span>
        `;
        div.onclick = () => showDetails(t);
        document.getElementById(t.status).appendChild(div);
      });

      renderCharts(stats);
    }

    function formatDauer(min) {
      if (min < 60) return `${min}m`;
      if (min < 1440) return `${Math.round(min/60)}h`;
      return `${Math.round(min/1440)}d`;
    }

    function showDetails(t) {
      const antwortMail = `\n\n--- Ihre zuvor gesendete Mail ---\n${t.nachricht}`;
      const modalHtml = `
        <p><strong>Name:</strong> ${t.vorname} ${t.nachname}</p>
        <p><strong>E-Mail:</strong> ${t.email}</p>
        <p><strong>Telefon:</strong> ${t.telefon}</p>
        <p><strong>Budget:</strong> ${t.budget}</p>
        <p><strong>Nachricht:</strong><br>${t.nachricht}</p>
        <p><strong>Gesendet:</strong> ${t.erstellt_am}</p>
        <p><strong>Status seit:</strong> ${t.status_geaendert_am}</p>
        <p><strong>Dauer im Status:</strong> ${t.dauer_min} Minuten</p>
        <textarea class="form-control mt-3 mb-2" rows="4" id="antwortText" placeholder="Antwort schreiben..."></textarea>
        <a class="btn btn-success" href="mailto:${t.email}?subject=Antwort auf Ihre Anfrage&body=${encodeURIComponent(antwortMail)}" id="sendLink" target="_blank">Antwort senden</a>
      `;
      document.getElementById('modalContent').innerHTML = modalHtml;
      setTimeout(() => {
        const input = document.getElementById('antwortText');
        const link = document.getElementById('sendLink');
        if (input && link) {
          input.addEventListener('input', () => {
            link.href = `mailto:${t.email}?subject=Antwort auf Ihre Anfrage&body=${encodeURIComponent(input.value + antwortMail)}`;
          });
        }
      }, 100);
      new bootstrap.Modal(document.getElementById('ticketModal')).show();
    }

    function renderCharts(stats) {
      const ctx1 = document.getElementById('chartAktuell');
      const ctx2 = document.getElementById('chartVergleich');
      const ctx3 = document.getElementById('chartVerlauf');

      new Chart(ctx1, {
        type: 'bar',
        data: {
          labels: ['Aktueller Monat'],
          datasets: [{
            label: 'Ø Bearbeitungszeit (Minuten)',
            data: [Math.round(stats.aktueller.gesamt / (stats.aktueller.count || 1))],
            backgroundColor: 'rgba(168,237,121,0.8)'
          }]
        }
      });

      new Chart(ctx2, {
        type: 'bar',
        data: {
          labels: ['Letzter Monat', 'Aktuell'],
          datasets: [{
            label: 'Ø Bearbeitungszeit (Minuten)',
            data: [
              Math.round(stats.letzter.gesamt / (stats.letzter.count || 1)),
              Math.round(stats.aktueller.gesamt / (stats.aktueller.count || 1))
            ],
            backgroundColor: ['#999', '#A8ED79']
          }]
        }
      });

      const labels = Object.keys(stats.monate).sort();
      const daten = labels.map(m => Math.round(stats.monate[m].gesamt / stats.monate[m].count));
      new Chart(ctx3, {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Ø Dauer pro Monat (Minuten)',
            data: daten,
            borderColor: '#A8ED79',
            backgroundColor: 'rgba(168,237,121,0.2)',
            fill: true
          }]
        }
      });
    }

    // Echtzeit-Aktualisierung alle 60 Sekunden
    setInterval(() => {
      document.querySelectorAll('.ticket').forEach(ticket => {
        const ts = ticket.dataset.timestamp;
        if (ts) {
          const min = Math.floor((new Date() - new Date(ts)) / 60000);
          const span = ticket.querySelector('.live-timer');
          if (span) span.textContent = formatDauer(min);
        }
      });
    }, 60000);

    loadTickets();
  </script>

  <style>
    .kanban-column { min-height: 300px; }
    .ticket:hover { background-color: #343a40; cursor: pointer; }
    .ticket .live-timer { display: inline-block; margin-top: 5px; }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
