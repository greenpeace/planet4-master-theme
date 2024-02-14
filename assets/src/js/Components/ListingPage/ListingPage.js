import {useState, useEffect, useCallback, useMemo} from '@wordpress/element';
import {fetchJson} from '../../../functions/fetchJson';
import {addQueryArgs} from '../../../functions/addQueryArgs';

const {apiFetch} = wp;

export const ListingPage = () => {
  const [posts, setPosts] = useState({recent_posts: [], total_posts: 0})
  const [filteredPosts, setFilteredPosts] = useState([]);
  const [filterBy, setFilterBy] = useState('post');
  const [sortByDate, setSortByDate] = useState('asc');

  const getPosts = useCallback(async () => {
    try {
      const args = {
        article_count: 100,
        ignore_categories: false,
        offset: 0,
      };

      const baseUrl = document.body.dataset.nro;

      const data = baseUrl ?
        await fetchJson(`${baseUrl}/wp-json/${addQueryArgs('planet4/v1/get-posts', args)}`) :
        await apiFetch({path: addQueryArgs('planet4/v1/get-posts', args)});

      setPosts(data);

    } catch (e) {
      // eslint-disable-next-line no-console
      console.log(e);
    }
  }, [
    filterBy,
    sortByDate,
  ]);

  const switchViews = () => {
    // TODO: Review this functionality
    listingPageContent.classList.toggle('wp-block-query--list');
    listingPageContent.classList.toggle('wp-block-query--grid');
    gridViewToggle.classList.toggle('d-none');
    listViewToggle.classList.toggle('d-none');
    // End TODO
  }

  const onChange = useCallback(evt => {
    setFilterBy(evt.currentTarget.value);
  }, []);

  useEffect(() => {
    setFilteredPosts(posts.recent_posts.filter(post => {
      console.log(post.post_type, filterBy)
      if(post.post_type === 'post' && filterBy === 'post') {
        return post;
      }

      return post;
    }));
  }, [posts.recent_posts, filterBy])

  useEffect(() => {
    getPosts();
  }, [
    filterBy,
    sortByDate,
  ]);

  useEffect(() => {
    // TODO: Review this functionality
    const listViewToggle = document.querySelector('.list-view-toggle');
    const gridViewToggle = document.querySelector('.grid-view-toggle');

    const listingPageContent = document.getElementById('listing-page-content');

    if (!listingPageContent || !listViewToggle || !gridViewToggle) {
      return;
    }

    listViewToggle.onclick = switchViews;
    gridViewToggle.onclick = switchViews;
    // End TODO
  }, []);

  return useMemo(() => (
    <section>
      <h1>All Articles</h1>
      <div>
        <select onChange={onChange}>
          <option value="post">Post Type</option>
          <option value="category">Category</option>
        </select>
      </div>
      <span>Total {posts.total_posts} posts.</span>
      <ul style={{listStyle: 'none'}}>
      {filteredPosts.map(post => post.post_type === filterBy ? (
        <li key={post.ID}>
          <figure>
            <img src='' />
          </figure>
          <div>
            <h3>{post.post_title}</h3>
            <p>{post.post_excerpt}</p>
          </div>
        </li>
      ) : null)}
      </ul>
    </section>
  ), [posts, filteredPosts, filterBy, sortByDate]);
};

export default ListingPage;
