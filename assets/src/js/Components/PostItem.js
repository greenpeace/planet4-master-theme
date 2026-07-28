/**
 * Renders a single post item within the listing page.
 *
 * @param {Object} props      Component props.
 * @param {Object} props.post WordPress REST API post object.
 *
 * @return {JSX.Element} The rendered post list item.
 */
function PostItem({post}) {
  const featuredMedia = post._embedded?.['wp:featuredmedia']?.[0];
  const author = post._embedded?.author?.[0];
  const terms = post._embedded?.['wp:term'] || [];
  const categories = terms.flat().filter(term => term.taxonomy === 'category');
  const tags = terms.flat().filter(term => term.taxonomy === 'post_tag');

  const formattedDate = new Date(post.date).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });

  return (
    <li className="wp-block-post query-list-item hentry">
      { featuredMedia && (
        <div className="query-list-item-image query-list-item-image-max-width">
          <a href={post.link}>
            <img
              width={featuredMedia.media_details?.width}
              height={featuredMedia.media_details?.height}
              src={featuredMedia.source_url}
              className="wp-post-image"
              alt={featuredMedia.alt_text || ''}
              srcSet={Object.values(featuredMedia.media_details?.sizes || {})
                .map(size => `${size.source_url} ${size.width}w`)
                .join(', ')}
              decoding="async"
            />
          </a>
        </div>
      ) }

      <div className="query-list-item-body">
        <div className="query-list-item-post-terms">
          { categories.length > 0 && (
            <div className="wrapper-post-term">
              <div className="wp-block-post-terms">
                { categories.map(category => (
                  <a key={category.id} href={category.link}>
                    { category.name }
                  </a>
                )) }
              </div>
            </div>
          ) }

          { tags.length > 0 && (
            <div className="wrapper-post-tag">
              <div className="taxonomy-post_tag wp-block-post-terms">
                { tags.map(tag => (
                  <a key={tag.id} href={tag.link} rel="tag">
                    { tag.name }
                  </a>
                )) }
              </div>
            </div>
          ) }
        </div>

        <header>
          <h4 className="query-list-item-headline wp-block-post-title">
            <a href={post.link} target="_self">
              { post.title.rendered }
            </a>
          </h4>
        </header>

        <div
          className="query-list-item-content wp-block-post-excerpt"
          dangerouslySetInnerHTML={{__html: post.excerpt.rendered}}
        />

        <div className="query-list-item-meta d-flex flex-wrap">
          { author && (
            <span className="article-list-item-author">
              <a href={author.link}>{ author.name }</a>
            </span>
          ) }
          <div className="query-list-meta-date-reading-time">
            <div className="wp-block-post-date">
              <time dateTime={post.date}>{ formattedDate }</time>
            </div>
          </div>
        </div>
      </div>
    </li>
  );
}

export default PostItem;
