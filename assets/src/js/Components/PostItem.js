// Decodes HTML entities in a string.
function decodeHtmlEntities(value) {
  const doc = new DOMParser().parseFromString(value, 'text/html');
  return doc.documentElement.textContent;
}

/**
 * Renders a single post item within the listing page.
 *
 * @param {Object} props      Component props.
 * @param {Object} props.post WordPress REST API post object, including the `listing_data` field.
 *
 * @return {JSX.Element} The rendered post list item.
 */
function PostItem({post}) {
  const {
    image,
    author,
    authorOverride,
    categories = [],
    tags = [],
  } = post.listing_data || {};

  const authorName = authorOverride || author?.name;
  const formattedDate = new Date(post.date).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });

  return (
    <li className="wp-block-post query-list-item hentry">
      { image?.src && (
        <div className="query-list-item-image query-list-item-image-max-width">
          <a href={post.link}>
            <img
              width={image.width}
              height={image.height}
              src={image.src}
              className="wp-post-image"
              alt={image.alt || ''}
              srcSet={image.srcset || undefined}
              sizes="(max-width: 768px) 100vw, 400px"
              decoding="async"
              loading="lazy"
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
              {decodeHtmlEntities(post.title.rendered)}
            </a>
          </h4>
        </header>

        <div
          className="query-list-item-content wp-block-post-excerpt"
          dangerouslySetInnerHTML={{__html: post.excerpt.rendered}}
        />

        <div className="query-list-item-meta d-flex flex-wrap">
          { authorName && (
            <span className="article-list-item-author">
              { authorOverride || !author?.link ? (
                authorName
              ) : (
                <a href={author.link}>{authorName}</a>
              ) }
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
