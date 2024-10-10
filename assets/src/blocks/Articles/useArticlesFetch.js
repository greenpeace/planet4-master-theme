import {useState, useEffect} from '@wordpress/element';
import {fetchJson} from '../../functions/fetchJson';
import {addQueryArgs} from '../../functions/addQueryArgs';
import {getAbortController} from '../../functions/getAbortController';

const {apiFetch} = wp;

export const useArticlesFetch = (attributes, postType, postId, postCategories = [], baseUrl = null) => {
  const {article_count, post_types, posts, tags, ignore_categories} = attributes;

  const [totalPosts, setTotalPosts] = useState(null);
  const [displayedPosts, setDisplayedPosts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [controller, setController] = useState();

  const loadPage = async (reset = false) => {
    if (loading) {
      return;
    }
    setLoading(true);

    const prevPosts = reset ? [] : displayedPosts;

    const args = {
      article_count,
      post_types,
      posts,
      tags,
      ignore_categories,
      offset: prevPosts.length,
    };

    if (!ignore_categories) {
      args.categories = postCategories;
    }

    if (postType === 'post') {
      args.exclude_post_id = postId;
    }

    const path = addQueryArgs('planet4/v1/get-posts', args);

    try {
      const response = baseUrl ?
        await fetchJson(`${baseUrl}/wp-json/${path}`) :
        await apiFetch({path});

      const newPosts = [...prevPosts, ...response.recent_posts];

      setDisplayedPosts(newPosts);

      if (response.total_posts !== undefined && response.total_posts !== totalPosts) {
        setTotalPosts(response.total_posts);
      }
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    setDisplayedPosts([]);
    setController(getAbortController());
  }, [article_count, post_types, posts, tags, ignore_categories]);

  useEffect(() => {
    if (controller) {
      loadPage(true);
    }

    return () => {
      if (controller) {
        setLoading(false);
        controller.abort();
        setController(null);
      }
    };
  }, [controller]);

  return {
    posts: displayedPosts,
    totalPosts,
    loading,
    error,
    hasMorePages: totalPosts > displayedPosts.length,
    loadNextPage: () => {
      loadPage();
    },
  };
};
