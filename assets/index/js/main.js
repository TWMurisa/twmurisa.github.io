 // Intersection Observer Optionen
 const appearOptions = {
    threshold: 0.2,
  };

  // Observer Funktion
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('appear', 'visible'); // beide Klassen auf einmal hinzufügen
        observer.unobserve(entry.target); // einmaliger Effekt
      }
    });
  }, appearOptions);

  // Alle Elemente mit .fade-in überwachen
  document.querySelectorAll('.fade-in').forEach(el => {
    observer.observe(el);
  });

  // Definiere die CSS-Styles direkt beim Laden
  document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.innerHTML = `
      .appear {
        opacity: 1 !important;
        transform: translateY(0px) !important;
      }
      .visible {
        box-shadow: 0 10px 30px rgba(168, 237, 121, 0.2); /* Grüner sanfter Glow */
        transition: all 1s ease-out;
      }
    `;
    document.head.appendChild(style);
  });

  document.querySelectorAll('.interactive-card').forEach(card => {
    card.addEventListener('click', () => {
      card.classList.toggle('active');
    });
  });