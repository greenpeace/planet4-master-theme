import { useSelect } from '@wordpress/data';

export const useCarouselHeaderImages = slides => useSelect(
  select => slides.map(
    slide => {
      const image = select('core').getMedia(slide.image);
      return ({
        ...slide,
        image_url: (!slide.image || !image) ? null : image.source_url,
      })
    }
  ),
  [slides]
);
