export const removeSlide = (slides, currentSlide, setAttributes, goToSlide) => {
  const newSlides = [
    ...slides.slice(0, currentSlide),
    ...slides.slice(currentSlide + 1)
  ];
  const lastSlide = newSlides.length - 1;
  setAttributes({ slides: newSlides });
  goToSlide(currentSlide > lastSlide ? 0 : currentSlide, true);
}
