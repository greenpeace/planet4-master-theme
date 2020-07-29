import { useState, useEffect } from '@wordpress/element';

const { apiFetch } = wp;
const { addQueryArgs } = wp.url;

const fetchJson = async(url) => {
  const response = await fetch(url);
  return response.json();
};

export const useArticlesFetch = (attributes, postType, postId, baseUrl = null) => {
  const { article_count, post_types, posts, tags, ignore_categories } = attributes;

  const [totalPosts, setTotalPosts] = useState(null);
  const [displayedPosts, setDisplayedPosts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

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

    if (postType === 'post') {
      args.exclude_post_id = postId;
    }


    try {
      const response = baseUrl
        ? await fetchJson(`${ baseUrl }/wp-json/${ addQueryArgs('planet4/v1/get-posts', args) }`)
        : await apiFetch({ path: addQueryArgs('planet4/v1/get-posts', args) });

      const newPosts = [...prevPosts, ...response.recent_posts];

      setDisplayedPosts(newPosts);

      if (response.total_posts !== undefined && response.total_posts !== totalPosts) {
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
    setDisplayedPosts([]);
    loadPage(true);
  }, [ article_count, post_types, posts, tags, ignore_categories ]);

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
