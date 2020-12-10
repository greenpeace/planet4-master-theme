import { ArticlesList } from './ArticlesList';
import { useArticlesFetch } from './useArticlesFetch';

const { __ } = wp.i18n;

export const ArticlesFrontend = (props) => {
  const {
    article_heading,
    articles_description,
    read_more_text,
    read_more_link,
    button_link_new_tab,
  } = props;

  const postType = document.body.getAttribute('data-post-type');

  const postIdClass = [...document.body.classList].find(className => /^postid-\d+$/.test(className));

  const postId = !postIdClass ? null : postIdClass.split('-')[1];

  const postCategories = props.post_categories || [];

  const { posts, loadNextPage, hasMorePages, loading } = useArticlesFetch(props, postType, postId, document.body.dataset.nro, postCategories);

  if (!posts.length) {
    return null;
  }

  return (
    <section className="block articles-block">
      <div className="container">
        <header>
          <h2 className="page-section-header">{ article_heading }</h2>
        </header>
        { articles_description &&
          <div className="page-section-description" dangerouslySetInnerHTML={{ __html: articles_description }} />
        }
        <ArticlesList posts={ posts } postType={ postType }/>
        { hasMorePages &&
        <div className="row">
          { read_more_link ?
            <div className="col-md-12 col-lg-5 col-xl-5 mr-auto">
              <a
                className="btn btn-secondary btn-block article-load-more"
                href={ read_more_link }
                target={ button_link_new_tab ? '_blank' : '' }
              >
                { read_more_text }
              </a>
            </div> :
            <div className="col-md-12 col-lg-5 col-xl-5">
              <button
                className="btn btn-secondary btn-block article-load-more"
                onClick={ loadNextPage }
                disabled={ loading }
                data-ga-category="Articles Block"
                data-ga-action="Load More Button"
                data-ga-label="n/a"
              >
                { read_more_text }
              </button>
            </div>
          }
        </div>
        }
      </div>
    </section>
  );
};
