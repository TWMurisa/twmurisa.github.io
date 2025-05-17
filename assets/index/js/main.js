// Fade-in Effekt für sichtbare Elemente (falls gewünscht)
document.addEventListener('DOMContentLoaded', () => {
  const fadeInElements = document.querySelectorAll('.fade-in');

  if (fadeInElements.length > 0) {
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.2 });

    fadeInElements.forEach(el => observer.observe(el));

    
  }

  document.getElementById('menu-toggle').addEventListener('click', () => {
  const links = document.getElementById('nav-links');
  links.classList.toggle('active');
});

});
