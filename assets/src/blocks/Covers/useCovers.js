import {useState, useEffect} from '@wordpress/element';
import {addQueryArgs} from '../../functions/addQueryArgs';
import {getAbortController} from '../../functions/getAbortController';
import {COVERS_TYPES, COVERS_LAYOUTS} from './CoversConstants';

const {apiFetch} = wp;

const isMobile = () => window.innerWidth < 768;
const isMediumWindow = () => window.innerWidth >= 768 && window.innerWidth < 992;

export const useCovers = ({post_types, tags, cover_type, initialRowsLimit, posts, layout}, noLoading = false) => {
  const [covers, setCovers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [row, setRow] = useState(initialRowsLimit);
  const [isSmallWindow, setIsSmallWindow] = useState(window.innerWidth < 992);
  const [amountOfCoversPerRow, setAmountOfCoversPerRow] = useState(null);
  const [error, setError] = useState(null);
  const [controller, setController] = useState();

  const updateRowCoversAmount = () => {
    setIsSmallWindow(window.innerWidth < 992);
    if (cover_type === COVERS_TYPES.takeAction) {
      if (layout === COVERS_LAYOUTS.carousel) {
        setAmountOfCoversPerRow(3);
      } else {
        setAmountOfCoversPerRow(isSmallWindow ? 2 : 3);
      }
    } else if (layout === COVERS_LAYOUTS.carousel) {
      setAmountOfCoversPerRow(4);
    } else if (isMobile()) {
      setAmountOfCoversPerRow(2);
    } else {
      setAmountOfCoversPerRow(isMediumWindow() ? 3 : 4);
    }
  };

  const loadCovers = async () => {
    if (loading) {
      return;
    }
    setLoading(true);

    const args = {
      post_types,
      cover_type,
      tags,
      posts,
      layout,
    };

    const path = addQueryArgs('planet4/v1/get-covers', args);

    try {
      const loadedCovers = await apiFetch({path, signal: controller.signal});

      if (loadedCovers) {
        setCovers(loadedCovers);
      }
    } catch (err) {
      setError(err.message);
    }

    setLoading(false);
  };

  useEffect(() => {
    if (!noLoading) {
      setController(getAbortController());
    }
  }, [cover_type, post_types, tags, posts, layout, noLoading]);

  useEffect(() => {
    if (controller) {
      loadCovers();
    }

    return () => {
      if (controller) {
        setLoading(false);
        setController(null);
      }
    };
  }, [controller]);

  useEffect(() => {
    updateRowCoversAmount();

    window.addEventListener('resize', updateRowCoversAmount);
    return () => window.removeEventListener('resize', updateRowCoversAmount);
  }, [layout, cover_type]);

  useEffect(() => {
    if (initialRowsLimit !== row) {
      setRow(initialRowsLimit);
    }
  }, [initialRowsLimit]);

  return {
    covers,
    loading,
    error,
    showMoreCovers: () => setRow(row + initialRowsLimit),
    row,
    amountOfCoversPerRow,
    isSmallWindow,
  };
};
