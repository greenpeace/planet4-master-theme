import {ArticlesList} from './ArticlesList';
import {useArticlesFetch} from './useArticlesFetch';

export const ArticlesFrontend = ({attributes}) => {
  const {
    article_heading,
    articles_description,
    read_more_text,
    read_more_link,
    button_link_new_tab,
    className,
  } = attributes;

  const postType = document.body.getAttribute('data-post-type');
  const postCategories = document.body.getAttribute('data-post-categories') ?
    document.body.getAttribute('data-post-categories').split(',') : [];

  const postIdClass = [...document.body.classList].find(classNameFound => /^postid-\d+$/.test(classNameFound));

  const postId = !postIdClass ? null : postIdClass.split('-')[1];

  const {posts, loadNextPage, hasMorePages, loading} = useArticlesFetch(attributes, postType, postId, postCategories, document.body.dataset.nro);

  if (!posts.length) {
    return null;
  }

  return (
    <section className={`block articles-block ${className ?? ''}`}>
      <header>
        <h2 className="page-section-header">{ article_heading }</h2>
      </header>
      { articles_description &&
        <p className="page-section-description" dangerouslySetInnerHTML={{__html: articles_description}} />
      }
      <ArticlesList posts={posts} postType={postType} />
      { hasMorePages &&
      <div className="row">
        { read_more_link ?
          <div className="col-md-12 col-lg-5 col-xl-5">
            <a
              className="btn btn-secondary article-load-more"
              href={read_more_link}
              {...button_link_new_tab && {rel: 'noreferrer', target: '_blank'}}
            >
              { read_more_text }
            </a>
          </div> :
          <div className="col-md-12 col-lg-5 col-xl-5">
            <button
              className="btn btn-secondary article-load-more"
              onClick={loadNextPage}
              disabled={loading}
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
    </section>
  );
};
