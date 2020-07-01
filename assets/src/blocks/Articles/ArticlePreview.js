import { Component } from '@wordpress/element';

export class ArticlePreview extends Component {
  constructor(props) {
    super(props);

    this.getPageTypesTags = this.getPageTypesTags.bind(this);
  }

  getPageTypesTags(pageType, id, link) {
    const isCampaign = fn('get_post_type') === 'campaign';
    if (isCampaign) {
      return <span className={`tag-item tag-item--main page-type page-type-${pageType.replace(' ', '_')}`}>{pageType}</span>;
    }
    return <a className={`tag-item tag-item--main page-type page-type-${pageType.replace(' ', '_')}`} href={link || fn('get_term_link', id)}>{pageType}</a>
  }

  getAuthorLink() {
    const { recent_post } = this.props;
    const isCampaign = fn('get_post_type') === 'campaign';

    if (recent_post.author_name) {
      return (
        <span className="article-list-item-author">{__('by', 'planet4-blocks')}
          {(recent_post.author_override || isCampaign) ?
            recent_post.author_name
            :
            <a href={recent_post.author_url}>{recent_post.author_name}</a>
          }
        </span>
      )
    } else if (recent_post.author) {
      return (
        <span className="article-list-item-author">{__('by', 'planet4-blocks')}
          {recent_post.author.is_fake || isCampaign ?
            recent_post.author.name
            :
            <a href={recent_post.author.link}>{recent_post.author.name}</a>
          }
        </span>
      )
    }
  }

  render() {
    const { recent_post } = this.props;

    let articleClassName = "article-list-item";
    if (recent_post.tags && recent_post.tags.length > 0) {
      recent_post.tags.forEach(tag => articleClassName += ` ${tag.slug}`);
    }

    const isCampaign = fn('get_post_type') === 'campaign';

    return (
      <article className={articleClassName} >
        {recent_post.thumbnail_ratio < 1 ?
          <div className="article-list-item-image">
            <div className="article-image-holder">
              <a href={recent_post.link}>
                <img
                  className="d-flex topicwise-article-image lazyload"
                  data-src={fn('get_the_post_thumbnail_url', recent_post.ID, 'articles-medium-large')}
                  alt={recent_post.alt_text}
                />
              </a>
            </div>
          </div>
          :
          <div className="article-list-item-image article-list-item-image-max-width">
            <a href={recent_post.link}>
              <img
                className="d-flex topicwise-article-image lazyload"
                data-src={fn('get_the_post_thumbnail_url', recent_post.ID, 'articles-medium-large')}
                alt={recent_post.alt_text}
              />
            </a>
          </div>
        }

        <div className="article-list-item-body">
          {(recent_post.tags || recent_post.page_type || recent_post.page_types) &&
            <div className="article-list-item-tags top-page-tags">
              {recent_post.page_type ?
                this.getPageTypesTags(recent_post.page_type, recent_post.page_type_id)
                :
                recent_post.page_types.map(({ name, link }) => this.getPageTypesTags(name, null, link))
              }

              {recent_post.tags &&
                <div className="tag-wrap tags">
                  {recent_post.tags.map(tag =>
                    <a className="tag-item tag" href={tag.link}>{`#${tag.name}`}</a>
                  )}
                </div>
              }
            </div>
          }

          <header>
            {recent_post.post_title &&
              <h4 className="article-list-item-headline">
                <a href={recent_post.link}>{recent_post.post_title}</a>
              </h4>
            }
            <p className="article-list-item-meta">
              {this.getAuthorLink()}
              {recent_post.post_date &&
                <time className="article-list-item-date" datetime="">{recent_post.post_date}</time>
              }
            </p>
          </header>

          {recent_post.post_excerpt &&
            <p className="article-list-item-content">
              {recent_post.post_excerpt}
            </p>
          }
        </div>
      </article>
    );
  }
}
