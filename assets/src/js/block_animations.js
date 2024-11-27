export const blockAnimations = () => {
  document.addEventListener('DOMContentLoaded', () => {
    const animatedElements = document.querySelectorAll('.animate__animated');

    // IntersectionObserver to detect when the element is in view
    const observer = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          // Get all classes from the element
          const classes = Array.from(entry.target.classList);

          // Find the first class with the prefix "animate__" but not "animate__animated"
          const animationClass = classes.find(className => className.startsWith('animate__') && className !== 'animate__animated');
          console.log(animationClass)

          if (true) {
            // Add the animation class to trigger the animation
            entry.target.classList.add('animate__shakeX');
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
