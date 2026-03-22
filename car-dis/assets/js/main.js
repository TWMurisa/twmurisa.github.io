const menuToggle = document.getElementById("menuToggle");
const mainNav = document.getElementById("mainNav");

if (menuToggle && mainNav) {
  menuToggle.addEventListener("click", () => {
    mainNav.classList.toggle("active");
  });
}

const faqItems = document.querySelectorAll(".faq-item");

faqItems.forEach((item) => {
  const button = item.querySelector(".faq-question");

  button.addEventListener("click", () => {
    item.classList.toggle("active");
  });
});

const fadeElements = document.querySelectorAll(".fade-up");

const revealOnScroll = () => {
  fadeElements.forEach((element) => {
    const top = element.getBoundingClientRect().top;
    if (top < window.innerHeight - 80) {
      element.classList.add("visible");
    }
  });
};

window.addEventListener("scroll", revealOnScroll);
window.addEventListener("load", revealOnScroll);