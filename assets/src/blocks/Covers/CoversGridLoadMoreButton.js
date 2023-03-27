export const CoversGridLoadMoreButton = ({showMoreCovers, readMoreText}) => (
  <button onClick={showMoreCovers} className="btn btn-block btn-secondary load-more-btn">
    {readMoreText}
  </button>
);
