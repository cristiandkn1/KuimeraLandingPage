//fotos de nosotros
const cards = document.querySelectorAll('.testimonial-card');
const dots = document.querySelectorAll('.dot');
let currentIndex = 1; // el del medio inicialmente
let interval = null;

function updateActive(index) {
  cards.forEach((card, i) => {
    card.classList.toggle('active', i === index);
    dots[i].classList.toggle('active', i === index);
  });
  currentIndex = index;
}

function nextTestimonial() {
  const nextIndex = (currentIndex + 1) % cards.length;
  updateActive(nextIndex);
}

dots.forEach(dot => {
  dot.addEventListener('click', () => {
    const index = parseInt(dot.getAttribute('data-index'));
    updateActive(index);
    resetInterval();
  });
});

function startAutoSlide() {
  interval = setInterval(nextTestimonial, 3000);
}

function resetInterval() {
  clearInterval(interval);
  startAutoSlide();
}

startAutoSlide();
