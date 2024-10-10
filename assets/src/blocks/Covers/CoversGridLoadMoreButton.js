export const CoversGridLoadMoreButton = ({showMoreCovers, readMoreText}) => (
  <button onClick={showMoreCovers} className="btn btn-secondary load-more-btn">
    {readMoreText}
  </button>
);
