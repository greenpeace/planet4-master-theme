/**
 * Renders a skeleton with a pulse animation.
 *
 * @param {Object} props           Component props.
 * @param {string} props.className The className to be added to the skeleton block.
 * @param {string} props.width     The width of the skeleton block.
 * @param {string} props.height    The height of the skeleton block.
 * @param {string} props.marginTop The margin top of the skeleton block.
 *
 *
 * @return {JSX.Element} The rendered skeleton block.
 */
const SkeletonBlock = ({className = '', width, height, marginTop}) => {
  const baseStyle = {
    ...width ? {width} : {},
    ...height ? {height} : {},
    ...marginTop ? {marginTop} : {},
  };

  return <div className={`skeleton ${className}`} style={baseStyle} aria-hidden="true" />;
};

/**
 * Renders a list of placeholders while the actual posts are loading.
 *
 * @param {Object} props       Component props.
 * @param {number} props.items The amount of posts to be expected.
 *
 *
 * @return {Array} The placeholder list.
 */
const PostsPlaceholder = ({items}) => {
  const placeholders = [];
  for (let i = 0; i < items; i++) {
    placeholders.push(
      <li key={`placeholder-${i}`} className="wp-block-post query-list-item hentry">
        <SkeletonBlock className="query-list-item-image" height="210px" />
        <div className="query-list-item-body">
          <div className="query-list-item-post-terms">
            <SkeletonBlock className="wrapper-post-term" height="25px" width="80%" />
          </div>
          <SkeletonBlock height="25px" width="100%" marginTop="8px" />
          <div className="query-list-item-content wp-block-post-excerpt">
            <SkeletonBlock height="72px" width="100%" marginTop="8px" />
          </div>
          <div className="query-list-item-meta d-flex flex-wrap">
            <SkeletonBlock height="20px" width="100px" marginTop="8px" />
            <SkeletonBlock height="20px" width="100px" marginTop="8px" />
          </div>
        </div>
      </li>
    );
  }

  return placeholders;
};

export default PostsPlaceholder;
