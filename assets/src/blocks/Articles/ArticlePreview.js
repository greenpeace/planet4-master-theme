import { Component } from '@wordpress/element';
const { __ } = wp.i18n;

export class ArticlePreview extends Component {
  constructor(props) {
    super(props);
  }

  getPageTypesTags(pageType, link) {
    const { isCampaign } = this.props;
    const className = `tag-item tag-item--main page-type page-type-${pageType.toLowerCase().replace(' ', '_')}`;
    if (isCampaign) {
      return <span className={className}>{pageType}</span>;
    }
    return <a className={className} href={link}>{pageType}</a>
  }

  getAuthorLink() {
    const {
      post: {
        author_name,
        author,
        author_override,
        author_url
      },
      isCampaign
    } = this.props;

    if (author_name) {
      return (
        <span className="article-list-item-author">{__('by', 'planet4-blocks')}{' '}
          {(author_override || isCampaign) ?
            author_name
            :
            <a href={author_url}>{author_name}</a>
          }
        </span>
      )
    } else if (author) {
      return (
        <span className="article-list-item-author">{__('by', 'planet4-blocks')}{' '}
          {author.is_fake || isCampaign ?
            author.name
            :
            <a href={author.link}>{author.name}</a>
          }
        </span>
      )
    }
  }

  render() {
    const {
      post: {
        tags,
        thumbnail_ratio,
        thumbnail_url,
        link,
        alt_text,
        page_type,
        page_type_link,
        post_title,
        post_date,
        post_excerpt
      },
      isEditing
    } = this.props;

    const date = new Date(post_date);

    let articleClassName = "article-list-item";
    if (tags && tags.length > 0) {
      tags.forEach(tag => articleClassName += ` ${tag.slug}`);
    }
    if (isEditing) articleClassName += ' editing';

    return (
      <article className={articleClassName} >
        {thumbnail_ratio < 1 ?
          <div className="article-list-item-image">
            <div className="article-image-holder">
              <a href={link}>
                <img
                  className="d-flex topicwise-article-image"
                  src={thumbnail_url}
                  alt={alt_text}
                />
              </a>
            </div>
          </div>
          :
          <div className="article-list-item-image article-list-item-image-max-width">
            <a href={link}>
              <img
                className="d-flex topicwise-article-image"
                src={thumbnail_url}
                alt={alt_text}
              />
            </a>
          </div>
        }

        <div className="article-list-item-body">
          {(tags || page_type) &&
            <div className="article-list-item-tags top-page-tags">
              {page_type &&
                this.getPageTypesTags(page_type, page_type_link)
              }

              {tags && tags.length > 0 &&
                <div className="tag-wrap tags">
                  {tags.map(tag =>
                    <a key={tag.name} className="tag-item tag" href={tag.link}>{`#${tag.name}`}</a>
                  )}
                </div>
              }
            </div>
          }

          <header>
            {post_title &&
              <h4 className="article-list-item-headline">
                <a href={link}>{post_title}</a>
              </h4>
            }
            <p className="article-list-item-meta">
              {this.getAuthorLink()}
              {post_date &&
                <time className="article-list-item-date" dateTime="">
                  {date.toLocaleDateString(undefined, { month: 'long', day: 'numeric', year: 'numeric' })}
                </time>
              }
            </p>
          </header>

          {post_excerpt &&
            <p className="article-list-item-content">
              {post_excerpt.replace('&hellip;', '...')}
            </p>
          }
        </div>
      </article>
    );
  }
}
