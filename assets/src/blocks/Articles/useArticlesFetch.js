import { useState, useEffect } from '@wordpress/element';

const { apiFetch } = wp;
const { addQueryArgs } = wp.url;

export const useArticlesFetch = (attributes, postType, postId) => {
  const [totalPosts, setTotalPosts] = useState(null);
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const loadPage = async (reset = false) => {
    if (loading) {
      return;
    }
    setLoading(true);

    const prevPosts = reset ? [] : posts;

    const args = {
      article_count: attributes.article_count,
      post_types: attributes.post_types,
      posts: attributes.posts,
      tags: attributes.tags,
      ignore_categories: attributes.ignore_categories,
      offset: prevPosts.length,
    };

    if (postType === 'post') {
      args.exclude_post_id = postId;
    }

    try {
      const response = await apiFetch({ path: addQueryArgs('planet4/v1/get-posts', args) });

      const newPosts = [...prevPosts, ...response.recent_posts];

      setPosts(newPosts);

      if (null === totalPosts) {
        setTotalPosts(response.total_posts);
      }

    } catch (e) {
      console.log(e);
      setError(e.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    setPosts([]);
    loadPage(true);
  }, [attributes]);

  return {
    posts,
    setPosts,
    totalPosts,
    loading,
    error,
    hasMorePages: totalPosts > posts.length,
    loadNextPage: () => {
      loadPage();
    },
  };
};
