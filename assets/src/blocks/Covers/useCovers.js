import { useState, useEffect } from '@wordpress/element';
import { addQueryArgs } from '../../functions/addQueryArgs';
import { COVERS_TYPES, COVERS_LAYOUTS } from './CoversConstants';

const { apiFetch } = wp;

const isMobile = () => window.innerWidth < 768;
const isSmallWindow = () => window.innerWidth < 992;
const isMediumWindow = () => window.innerWidth >= 768 && window.innerWidth < 992;

export const useCovers = ({ post_types, tags, cover_type, initialRowsLimit, posts, layout }, noLoading, coversContainer) => {
  const [covers, setCovers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [row, setRow] = useState(initialRowsLimit);
  const [amountOfCoversPerRow, setAmountOfCoversPerRow] = useState(null);
  const [error, setError] = useState(null);

  const updateRowCoversAmount = () => {
    switch (cover_type) {
    case COVERS_TYPES.campaign:
      setAmountOfCoversPerRow(3);
      break;
    case COVERS_TYPES.content:
      if (layout === COVERS_LAYOUTS.carousel) {
        setAmountOfCoversPerRow(4);
      } else {
        if (isMobile()) {
          setAmountOfCoversPerRow(2);
        } else {
          setAmountOfCoversPerRow(isMediumWindow() ? 3 : 4);
        }
      }
      break;
    case COVERS_TYPES.takeAction:
      if (layout === COVERS_LAYOUTS.carousel) {
        setAmountOfCoversPerRow(4);
      } else {
        setAmountOfCoversPerRow(isSmallWindow() ? 2 : 3);
      }
      break;
    default:
      break;
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

  const slideCovers = (direction, rowNumber) => {
    if (!coversContainer.current) {
      return;
    }

    const covers = coversContainer.current.querySelector('.covers');

    if (!covers) {
      return;
    }

    const isRTL = document.querySelector('html').dir === 'rtl';
    const initialScrollPosition = covers.scrollLeft;
    // We need to account for the spacing between covers,
    // in all styles that spacing is 24px.
    const scrollOffset = (isRTL ? -1 : 1) * (covers.offsetWidth + 24);

    if (direction) {
      covers.scrollLeft = initialScrollPosition + (direction === 'next' ? scrollOffset : -scrollOffset);
      setRow(direction === 'next' ? row + 1 : row - 1);
    } else if (rowNumber) {
      const rowOffset = rowNumber > row ? 1 : -1;
      covers.scrollLeft = initialScrollPosition + rowOffset * rowNumber * scrollOffset;
      setRow(rowNumber);
    }
  };

  useEffect(() => {
    if (!noLoading) {
      loadCovers();
    }
  }, [cover_type, post_types, tags, posts, layout]);

  useEffect(() => {
    updateRowCoversAmount();

    if (layout !== COVERS_LAYOUTS.carousel && cover_type !== COVERS_TYPES.campaign) {
      window.addEventListener('resize', updateRowCoversAmount);
      return () => window.removeEventListener('resize', updateRowCoversAmount);
    }
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
    slideCovers,
    row,
    amountOfCoversPerRow,
  };
};
