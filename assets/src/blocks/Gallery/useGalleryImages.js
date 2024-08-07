import {fetchJson} from '../../functions/fetchJson';
import {addQueryArgs} from '../../functions/addQueryArgs';
import {getAbortController} from '../../functions/getAbortController';

const {apiFetch} = wp;
const {useState, useEffect} = wp.element;

const GALLERY_IMAGE_SIZES = {
  slider: 'retina-large',
  'three-columns': 'medium_large',
  grid: 'large',
};

export const useGalleryImages = ({multiple_image, gallery_block_focus_points}, layout, baseUrl = null) => {
  const [images, setImages] = useState([]);
  const [controller, setController] = useState();

  const imageSize = GALLERY_IMAGE_SIZES[layout];

  const loadPage = async () => {
    const args = {
      image_size: imageSize,
      multiple_image,
      gallery_block_focus_points,
    };

    try {
      // eslint-disable-next-line no-shadow
      const images = baseUrl ?
        await fetchJson(`${baseUrl}/wp-json/${addQueryArgs('planet4/v1/gallery/images', args)}`) :
        await apiFetch({path: addQueryArgs('planet4/v1/gallery/images', args)});
      setImages(images);
    } catch (e) {
      // eslint-disable-next-line no-console
      console.log(e);
    }
  };

  useEffect(() => {
    setImages([]);
    setController(getAbortController());
  }, [multiple_image, gallery_block_focus_points]);

  useEffect(() => {
    if (controller) {
      loadPage();
    }

    return () => {
      if (controller) {
        controller.abort();
        setController(null);
      }
    };
  }, [controller]);

  return {images};
};
