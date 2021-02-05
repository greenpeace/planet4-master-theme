import { useState, useEffect } from '@wordpress/element';
import { addQueryArgs } from '../../functions/addQueryArgs';

const { apiFetch } = wp;

export const useCovers = ({ post_types, tags, cover_type, covers_view, posts }, noLoading) => {
  const [covers, setCovers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [row, setRow] = useState(parseInt(covers_view));
  const [error, setError] = useState(null);

  const loadCovers = async () => {
    if (loading) {
      return;
    }
    setLoading(true);

    if (parseInt(covers_view) !== row) {
      setRow(parseInt(covers_view));
    }

    const args = {
      post_types,
      cover_type,
      tags,
      covers_view,
      posts,
    };

    const path = addQueryArgs('planet4/v1/get-covers', args);

    try {
      const loadedCovers = await apiFetch({ path });

      if (loadedCovers) {
        setCovers(loadedCovers);
      }

    } catch (e) {
      console.log(e);
      setError(e.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (!noLoading) {
      loadCovers();
    }
  }, [ cover_type, post_types, covers_view, tags, posts ]);

  return {
    covers,
    loading,
    error,
    loadMoreCovers: () => setRow(row + parseInt(covers_view)),
    row,
  };
};
