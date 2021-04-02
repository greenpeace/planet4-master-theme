const { __ } = wp.i18n;

export const Arrows = ({ goToPrevSlide, goToNextSlide }) => {
  return <>
    <button className='carousel-control-prev' role='button' onClick={goToPrevSlide}>
      <span className='carousel-control-prev-icon' aria-hidden='true'><i></i></span>
      <span className='visually-hidden'>{__('Previous', 'planet4-blocks')}</span>
    </button>
    <button className='carousel-control-next' role='button' onClick={goToNextSlide}>
      <span className='carousel-control-next-icon' aria-hidden='true'><i></i></span>
      <span className='visually-hidden'>{__('Next', 'planet4-blocks')}</span>
    </button>
  </>;
}
