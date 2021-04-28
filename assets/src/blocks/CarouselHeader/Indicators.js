export const Indicators = ({ slides, currentSlide, goToSlide }) => (
  slides?.length > 1 &&
  <div className='carousel-indicators-wrapper'>
    <div className='container'>
      <div className='row'>
        <div className='col'>
          <ol className='carousel-indicators'>
            {
              slides.map((slide, index) =>
                <li
                  onClick={() => goToSlide(index)}
                  key={index}
                  className={index === currentSlide ? 'active' : ''}
                ></li>
              )
            }
          </ol>
        </div>
      </div>
    </div>
  </div>
);
