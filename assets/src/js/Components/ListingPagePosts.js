import {useCallback, useState, useEffect} from '@wordpress/element';
import {fetchJson} from '../../functions/fetchJson';
import {addQueryArgs} from '../../functions/addQueryArgs';

const ListingPagePosts = () => {
  const [posts, setPosts] = useState([]);

  function PostItem({post}) {
    return (
      <li>
        <h3 dangerouslySetInnerHTML={{__html: post.title.rendered}} />
        <div dangerouslySetInnerHTML={{__html: post.excerpt.rendered}} />
      </li>
    );
  }

  const getPosts = useCallback(async () => {
    try {
      const args = {
        per_page: 3,
        ignore_categories: false,
        offset: 0,
      };

      const baseUrl = document.body.dataset.nro;

      const data = await fetchJson(`${baseUrl}/wp-json/${addQueryArgs('wp/v2/posts', args)}`);

      setPosts(data);

    } catch (e) {
      // eslint-disable-next-line no-console
      console.log(e);
    }
  }, []);

  useEffect(() => {
    getPosts();
  }, []);

  return (
    <ul>
      {posts.map(post => (
        <PostItem key={post.id} post={post} />
      ))}
    </ul>);
};

export default ListingPagePosts;
