import { Component } from '@wordpress/element';
import { dateI18n } from '@wordpress/date';
import { unescape } from '../../functions/unescape';
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
    return <a
              className={className}
              href={link}
              data-ga-category="Articles Block"
              data-ga-action="Post Type Tag"
              data-ga-label="n/a">
                {unescape(pageType)}
           </a>
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

    const authorName = author_name || (author && author.name) || '';
    const authorLink = author_url || (author && author.link) || '';

    const isLink = !isCampaign && !author_override && (!author || !author.is_fake) && authorLink;

    if (authorName) {
      return (
        <span className="article-list-item-author">
          {!isLink ?
            authorName
            :
            <a href={authorLink}>{authorName}</a>
          }
        </span>
      );
    }
  }

  getImage() {
    const {
      post: {
        thumbnail_ratio,
        thumbnail_url,
        link,
        alt_text
      }
    } = this.props;

    const image = (
      <a
        href={link}
        data-ga-category="Articles Block"
        data-ga-action="Image"
        data-ga-label="n/a">
          <img
            className="d-flex topicwise-article-image"
            src={thumbnail_url}
            alt={alt_text}
            loading="lazy"
          />
      </a>
    );

    return (
      <div className={`article-list-item-image ${thumbnail_ratio < 1 ? '' : 'article-list-item-image-max-width'}`}>
        {thumbnail_ratio < 1 ?
          <div className="article-image-holder">
            {image}
          </div>
          :
          image
        }
      </div>
    );
  }

  render() {
    const {
      post: {
        tags,
        link,
        page_type,
        page_type_link,
        post_title,
        post_excerpt,
        post_date
      }
    } = this.props;

    const articleClassName = tags.reduce((classname, tag) => classname + ` tag-${tag.slug}`, 'article-list-item');

    return (
      <article className={articleClassName} >
        {this.getImage()}
        <div className="article-list-item-body">
          {(tags || page_type) &&
            <div className="article-list-item-tags top-page-tags">
              {page_type &&
                this.getPageTypesTags(page_type, page_type_link)
              }

              {tags.length > 0 &&
                <div className="tag-wrap tags">
                  {tags.map(tag =>
                    <a
                      key={tag.name}
                      className="tag-item tag"
                      href={tag.link}
                      data-ga-category="Articles Block"
                      data-ga-action="Navigation Tag"
                      data-ga-label="n/a">
                        <span aria-label="hashtag">#</span>
                        {unescape(tag.name)}
                    </a>
                  )}
                </div>
              }
            </div>
          }

          <header>
            {post_title &&
              <h4 className="article-list-item-headline">
                <a
                  href={link}
                  data-ga-category="Articles Block"
                  data-ga-action="Title"
                  data-ga-label="n/a">
                    {post_title}
                </a>
              </h4>
            }
            <p className="article-list-item-meta">
              {this.getAuthorLink()}
              {post_date &&
                <time className="article-list-item-date" dateTime="">
                  {dateI18n(window.p4bk_vars.dateFormat, post_date)}
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
