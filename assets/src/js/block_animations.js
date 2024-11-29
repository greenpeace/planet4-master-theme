export const blockAnimations = () => {
  document.addEventListener('DOMContentLoaded', () => {
    const animatedElements = document.querySelectorAll('[data-animation]');


    // IntersectionObserver to detect when the element is in view
    const observer = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          // Get all classes from the element
          const animation = entry.target.getAttribute('data-animation');
          if (true) {
            // Add the animation class to trigger the animation
            entry.target.classList.add(animation);
            entry.target.classList.add('animate__animated'); // Ensure it runs the animation
          }

          observer.unobserve(entry.target); // Stop observing after the animation is triggered
        }
      });
    }, {
      threshold: 0.5, // Trigger when 10% of the element is visible
    });

    animatedElements.forEach(el => observer.observe(el));
  });
};
