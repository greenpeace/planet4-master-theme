import {useState, useEffect} from '@wordpress/element';
import {fetchJson} from '../../../functions/fetchJson';
import {GridView} from './GridView';
import {ListView} from './ListView';
import {PostTypesSelect} from './PostTypesSelect';

const {__} = wp.i18n;

const FilterIcon = () => (
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
    <path d="M487.976 0H24.028C2.71 0-8.047 25.866 7.058 40.971L192 225.941V432c0 7.831 3.821 15.17 10.237 19.662l80 55.98C298.02 518.69 320 507.493 320 487.98V225.941l184.947-184.97C520.021 25.896 509.338 0 487.976 0z" />
  </svg>
);

const CloseIcon = () => (
  <svg width={24} height={24} viewBox="0 0 24 24">
    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd">
      <g transform="translate(-418.000000, -93.000000)" fill="#1c1c1c" fillRule="nonzero">
        <g transform="translate(313.000000, 78.000000)">
          <g transform="translate(105.000000, 15.000000)">
            <path d="M9.39497475,0.205041759 L9.43963892,0.254266739 L9.47751275,0.305063403 C9.66577379,0.582273916 9.63454372,0.95542228 9.39497475,1.19499125 L5.69,4.89961651 L9.39497355,8.60504056 C9.66834175,8.87840876 9.66834175,9.32162425 9.39497475,9.59499125 C9.12160774,9.86835826 8.67839226,9.86835826 8.40502525,9.59499125 L4.8,5.989 L1.1949746,9.59499125 C0.948944293,9.84102156 0.56533672,9.86562459 0.291790962,9.66880034 L0.205025103,9.59499125 C-0.068341901,9.32162425 -0.068341901,8.87840876 0.205026303,8.60504056 L0.205026303,8.60504056 L3.90999985,4.89961651 L0.205025103,1.19499125 C-0.0345438696,0.95542228 -0.0657739389,0.582273916 0.122487104,0.305063403 L0.122487104,0.305063403 L0.160360933,0.254266739 L0.205025103,0.205041759 C0.459567137,-0.0495002749 0.86490778,-0.0688472182 1.14189948,0.157071178 L1.14189948,0.157071178 L1.1949746,0.205041759 L4.8,3.811 L8.40502525,0.205041759 L8.45810037,0.157071178 C8.73509207,-0.0688472182 9.14043271,-0.0495002749 9.39497475,0.205041759 Z" />
          </g>
        </g>
      </g>
    </g>
  </svg>
);

const ListingPage = () => {
  const [posts, setPosts] = useState([]);
  const [filteredPosts, setFilteredPosts] = useState([]);
  const [layout, setLayout] = useState('list');
  const [postTypes, setPostTypes] = useState([]);
  const [selectedPostType, setSelectedPostType] = useState('');

  const baseUrl = document.body.dataset.nro;

  const switchViews = () => setLayout(layout === 'grid' ? 'list' : 'grid');

  const updateFilteredPosts = postType => {
    if (!postType) {
      setFilteredPosts(posts);
    } else {
      setFilteredPosts(posts.filter(post => post.page_type_id === postType));
    }
  };

  const onChangePostType = (evt, immediate) => {
    const newPostType = Number(evt.currentTarget.value);
    if (immediate) {
      updateFilteredPosts(newPostType);
    } else {
      setSelectedPostType(newPostType);
    }
  };

  useEffect(() => {
    const getPosts = async () => {
      try {
        const data = await fetchJson(`${baseUrl}/wp-json/planet4/v1/listing-page/posts`);
        setFilteredPosts(data);
        setPosts(data);
      } catch (e) {
        // eslint-disable-next-line no-console
        console.log(e);
      }
    };

    const getPostTypes = async () => {
      try {
        const path = '/wp-json/wp/v2/p4-page-type?_fields=id,name';

        const data = await fetchJson(`${baseUrl}/${path}`);
        setPostTypes(data);
      } catch (e) {
        // eslint-disable-next-line no-console
        console.log(e);
      }
    };

    getPosts();
    getPostTypes();
  }, []);

  return (
    <>
      <div id="filtersModal" className="modal fade" tabIndex={-1} role="dialog" aria-hidden="true">
        <div className="modal-dialog">
          <div className="modal-content">
            <div className="modal-header">
              <h5 className="modal-title">{__('Filter', 'planet4-master-theme')}</h5>
              <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close">
                <CloseIcon />
              </button>
            </div>
            <div className="modal-body">
              <PostTypesSelect postTypes={postTypes} onChangePostType={onChangePostType} />
            </div>
            <div className="modal-footer">
              <button type="button" className="btn btn-secondary" data-bs-dismiss="modal">
                {__('Cancel', 'planet4-master-theme')}
              </button>
              <button className="btn btn-primary" onClick={() => updateFilteredPosts(selectedPostType)} data-bs-dismiss="modal">
                {__('Apply filter', 'planet4-master-theme')}
              </button>
            </div>
          </div>
        </div>
      </div>
      <div className="d-flex align-items-center justify-content-between listing-page-title">
        <button data-bs-toggle="modal" data-bs-target="#filtersModal" className="btn-filters btn btn-secondary">
          <FilterIcon />
          {__('Filters', 'planet4-master-theme')}
        </button>
        <PostTypesSelect postTypes={postTypes} onChangePostType={onChangePostType} immediate />
        <div className="layout-toggle">
          {layout === 'grid' ? <ListView onClick={switchViews} /> : <GridView onClick={switchViews} />}
        </div>
      </div>
      <div className={`wp-block-query wp-block-query--${layout} is-layout-flow wp-block-query-is-layout-flow`}>
        <ul className="wp-block-post-template">
          {filteredPosts.map(({
            ID,
            link,
            thumbnail_srcset,
            thumbnail_url,
            page_type_link,
            page_type, tags,
            alt_text,
            post_title,
            post_excerpt,
            author_name,
            author_url,
            post_date,
            date_formatted,
            reading_time,
          }) => (
            <li className="wp-block-post" key={ID}>
              <div className="query-list-item-image query-list-item-image-max-width">
                <a href={link}>
                  {thumbnail_url && <img className="wp-post-image" src={thumbnail_url} srcSet={thumbnail_srcset} alt={alt_text} decoding="async" />}
                </a>
              </div>
              <div className="query-list-item-body">
                <div className="query-list-item-post-terms">
                  <div className="wrapper-post-term">
                    <div className="taxonomy-p4-page-type wp-block-post-terms">
                      <a href={page_type_link} rel="tag">{page_type}</a>
                    </div>
                  </div>
                  {!!tags.length &&
                    <div className="wrapper-post-tag">
                      <div className="taxonomy-post_tag wp-block-post-terms">
                        {tags.map(tag => <a href={tag.link} key={tag.id}>{tag.name}</a>)}
                      </div>
                    </div>
                  }
                </div>
                <header>
                  <h4 className="query-list-item-headline wp-block-post-title">
                    <a href={link}>{post_title}</a>
                  </h4>
                </header>
                <div className="query-list-item-content wp-block-post-excerpt">
                  <p className="wp-block-post-excerpt__excerpt">{post_excerpt}</p>
                </div>
                <div className="query-list-item-meta d-flex flex-wrap">
                  <span className="article-list-item-author">
                    <a href={author_url}>{author_name}</a>
                  </span>
                  <span className="query-list-item-bullet" aria-hidden="true">•</span>
                  <div className="wp-block-post-date">
                    <time dateTime={post_date}>{date_formatted}</time>
                  </div>
                  {reading_time &&
                    <span className="article-list-item-readtime">
                      { __('%d min read', 'planet4-blocks').replace('%d', reading_time) }
                    </span>
                  }
                </div>
              </div>
            </li>
          ))}
        </ul>
      </div>
    </>
  );
};

export default ListingPage;
