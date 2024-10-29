import {GalleryCarousel} from './GalleryCarousel';
import {GalleryThreeColumns} from './GalleryThreeColumns';
import {GalleryGrid} from './GalleryGrid';
import {getGalleryLayout, GALLERY_BLOCK_CLASSES} from './getGalleryLayout';

const {useEffect, useState} = wp.element;

export const GalleryFrontend = ({
  attributes = {},
}) => {
  const [images, setImages] = useState([]);
  const className = attributes.className ?? '';
  const expand = attributes.expand_on_click;
  const layout = getGalleryLayout(className, attributes.gallery_block_style ?? '');
  const postType = document.body.getAttribute('data-post-type');

  useEffect(() => {
    if (attributes.image_data.length && attributes.images.length) {
      setImages(attributes.images);
    }

    if (attributes.image_data.length && !attributes.images.length) {
      setImages(attributes.image_data);
    }
  }, [attributes]);

  return (
    <section className={`block ${GALLERY_BLOCK_CLASSES[layout]} ${className}`}>
      {attributes.gallery_block_title &&
        <header>
          <h2 className="page-section-header" dangerouslySetInnerHTML={{__html: attributes.gallery_block_title}} />
        </header>
      }
      {attributes.gallery_block_description &&
        <p className="page-section-description" dangerouslySetInnerHTML={{__html: attributes.gallery_block_description}} />
      }

      {images.length ? (
        <>
          {layout === 'slider' && <GalleryCarousel images={images} />}
          {layout === 'three-columns' && <GalleryThreeColumns images={images} postType={postType} />}
          {layout === 'grid' && <GalleryGrid images={images} expand={expand} />}
        </>
      ) : null}
    </section>
  );
};
