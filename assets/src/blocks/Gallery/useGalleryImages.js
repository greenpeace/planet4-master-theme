import { useState, useEffect } from '@wordpress/element';
import { fetchJson } from '../../functions/fetchJson';

const { apiFetch } = wp;
const { addQueryArgs } = wp.url;

const GALLERY_IMAGE_SIZES = {
  'slider': 'retina-large',
  'three-columns': 'medium_large',
  'grid': 'large'
};

export const useGalleryImages = ({ multiple_image, gallery_block_focus_points }, layout, baseUrl = null) => {
  const [images, setImages] = useState([]);

  const imageSize = GALLERY_IMAGE_SIZES[layout];

  const loadPage = async () => {

    const args = {
      image_size: imageSize,
      multiple_image,
      gallery_block_focus_points
    };

    try {
      const images = baseUrl
        ? await fetchJson(`${ baseUrl }/wp-json/${ addQueryArgs('planet4/v1/get-gallery-images', args) }`)
        : await apiFetch({ path: addQueryArgs('planet4/v1/get-gallery-images', args) });
      setImages(images);
    } catch (e) {
      console.log(e);
    }
  };

  useEffect(() => {
    setImages([]);
    loadPage();
  }, [multiple_image, gallery_block_focus_points]);

  return { images };
};
