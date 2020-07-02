import { Component } from '@wordpress/element';
const { __ } = wp.i18n;

export class ArticlePreview extends Component {
  constructor(props) {
    super(props);
  }

  getPageTypesTags(pageType, id, link) {
    const { isCampaign } = this.props;
    // TODO get link from id
    if (isCampaign) {
      return <span className={`tag-item tag-item--main page-type page-type-${pageType.replace(' ', '_')}`}>{pageType}</span>;
    }
    return <a className={`tag-item tag-item--main page-type page-type-${pageType.replace(' ', '_')}`} href={link}>{pageType}</a>
  }

  getAuthorLink() {
    const { post, isCampaign } = this.props;

    if (post.author_name) {
      return (
        <span className="article-list-item-author">{__('by', 'planet4-blocks')}{' '}
          {(post.author_override || isCampaign) ?
            post.author_name
            :
            <a href={post.author_url}>{post.author_name}</a>
          }
        </span>
      )
    } else if (post.author) {
      return (
        <span className="article-list-item-author">{__('by', 'planet4-blocks')}{' '}
          {post.author.is_fake || isCampaign ?
            post.author.name
            :
            <a href={post.author.link}>{post.author.name}</a>
          }
        </span>
      )
    }
  }

  render() {
    const { post } = this.props;

    let articleClassName = "article-list-item";
    if (post.tags && post.tags.length > 0) {
      post.tags.forEach(tag => articleClassName += ` ${tag.slug}`);
    }

    return (
      <article className={articleClassName} >
        {post.thumbnail_ratio < 1 ?
          <div className="article-list-item-image">
            <div className="article-image-holder">
              <a href={post.link}>
                <img
                  className="d-flex topicwise-article-image lazyload"
                  // data-src={fn('get_the_post_thumbnail_url', post.ID, 'articles-medium-large')} // TODO
                  alt={post.alt_text}
                />
              </a>
            </div>
          </div>
          :
          <div className="article-list-item-image article-list-item-image-max-width">
            <a href={post.link}>
              <img
                className="d-flex topicwise-article-image lazyload"
                // data-src={fn('get_the_post_thumbnail_url', post.ID, 'articles-medium-large')} // TODO
                alt={post.alt_text}
              />
            </a>
          </div>
        }

        <div className="article-list-item-body">
          {(post.tags || post.page_type || post.page_types) &&
            <div className="article-list-item-tags top-page-tags">
              {post.page_type ?
                this.getPageTypesTags(post.page_type, post.page_type_id)
                :
                post.page_types.map(({ name, link }) => this.getPageTypesTags(name, null, link))
              }

              {post.tags &&
                <div className="tag-wrap tags">
                  {post.tags.map(tag =>
                    <a className="tag-item tag" href={tag.link}>{`#${tag.name}`}</a>
                  )}
                </div>
              }
            </div>
          }

          <header>
            {post.post_title &&
              <h4 className="article-list-item-headline">
                <a href={post.link}>{post.post_title}</a>
              </h4>
            }
            <p className="article-list-item-meta">
              {this.getAuthorLink()}
              {post.post_date &&
                <time className="article-list-item-date" datetime="">{post.post_date}</time>
              }
            </p>
          </header>

          {post.post_excerpt &&
            <p className="article-list-item-content">
              {post.post_excerpt}
            </p>
          }
        </div>
      </article>
    );
  }
}
