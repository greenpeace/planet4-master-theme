import { useState, useEffect } from '@wordpress/element';
import { fetchJson } from '../../functions/fetchJson';
import { addQueryArgs } from '../../functions/addQueryArgs';

const { apiFetch } = wp;

export const useCovers = ({ post_types, tags, cover_type, covers_view }, baseUrl) => {
  const [covers, setCovers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const loadCovers = async () => {
    if (loading) {
      return;
    }
    setLoading(true);

    const args = {
      post_types,
      cover_type: cover_type || '3',
      tags,
      covers_view,
    };

    const path = addQueryArgs('planet4/v1/get-covers', args);

    try {
      const response = baseUrl
        ? await fetchJson(`${ baseUrl }/wp-json/${ path }`)
        : await apiFetch({ path });

      if (response.covers) {
        setCovers(response.covers);
      }

    } catch (e) {
      console.log(e);
      setError(e.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadCovers();
  }, [ cover_type, post_types, covers_view, tags ]);

  return {
    covers,
    loading,
    error,
  };
};
