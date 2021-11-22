const { __ } = wp.i18n;

export const CoversGridLoadMoreButton = ({ showMoreCovers }) => (
  <button onClick={showMoreCovers} className='btn btn-block btn-secondary load-more-btn'>
    {__('Load more', 'planet4-blocks')}
  </button>
);
