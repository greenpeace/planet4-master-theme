const { __ } = wp.i18n;

export const CoversGridLoadMoreButton = ({ showMoreCovers, readMoreText }) => (
  <button onClick={showMoreCovers} className='btn btn-block btn-secondary load-more-btn'>
    {readMoreText}
  </button>
);
