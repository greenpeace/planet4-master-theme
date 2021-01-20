export const Arrows = ({ goToPrevSlide, goToNextSlide }) => {
  return <>
    <a onClick={goToPrevSlide} className="carousel-control-prev" on="#carousel-wrapper-header" role="button">
      <span className="carousel-control-prev-icon" aria-hidden="true"><i></i></span>
      <span className="visually-hidden">Prev</span>
    </a>
    <a onClick={goToNextSlide} className="carousel-control-next" href="#carousel-wrapper-header" role="button">
      <span className="carousel-control-next-icon" aria-hidden="true"><i></i></span>
      <span className="visually-hidden">Next</span>
    </a>
  </>;
}
