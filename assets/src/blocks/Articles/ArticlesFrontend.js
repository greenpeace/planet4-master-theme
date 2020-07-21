import { Component, Fragment } from '@wordpress/element';
import { ArticlesList } from './ArticlesList';

const { __ } = wp.i18n;

export class ArticlesFrontend extends Component {
  constructor(props) {
    super(props);
    this.state = {
      totalPages: 0,
      page: 0
    };
  }

  render() {
    const {
      article_heading,
      articles_description,
      read_more_text,
      read_more_link,
      button_link_new_tab,
    } = this.props;

    const { totalPages, page } = this.state;

    return (
      <Fragment>
        <section className="block articles-block">
          <div className="container">
            <header>
              <h2 className="page-section-header">{article_heading || __('Latest Articles', 'planet4-blocks')}</h2>
            </header>
            {articles_description &&
              <div className="page-section-description">{articles_description}</div>
            }
            <ArticlesList
              page={page}
              setTotalPages={totalPages => this.setState({ totalPages })}
              {...this.props}
            />
            {totalPages > 1 && page < (totalPages - 1) &&
              <div className="row">
                {read_more_link ?
                  <div className="col-md-12 col-lg-5 col-xl-5 mr-auto">
                    <a
                      className="btn btn-secondary btn-block article-load-more"
                      href={read_more_link}
                      target={button_link_new_tab ? '_blank' : ''}
                    >
                      {read_more_text || __('Load More', 'planet4-blocks')}
                    </a>
                  </div> :
                  <div className="col-md-12 col-lg-5 col-xl-5">
                    <button
                      className="btn btn-secondary btn-block article-load-more"
                      onClick={() => this.setState({ page: page + 1 })}
                    >
                      {read_more_text || __('Load More', 'planet4-blocks')}
                    </button>
                  </div>
                }
              </div>
            }
          </div>
        </section>
      </Fragment >
    )
  }
}
