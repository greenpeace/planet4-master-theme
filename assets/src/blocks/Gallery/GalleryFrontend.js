import {GalleryCarousel} from './GalleryCarousel';
import {GalleryThreeColumns} from './GalleryThreeColumns';
import {GalleryGrid} from './GalleryGrid';
import {getGalleryLayout, GALLERY_BLOCK_CLASSES} from './getGalleryLayout';
import {getCaptionWithCredits} from './getCaptionWithCredits.js';
import {Lightbox} from '../components/Lightbox/Lightbox';
import {useLightbox} from '../components/Lightbox/useLightbox';

const {useEffect, useState} = wp.element;

const imagesToItems = images => images.map(
  image => ({
    src: image.image_src,
    w: 0,
    h: 0,
    title: getCaptionWithCredits(image),
  })
);

export const GalleryFrontend = ({
  attributes = {},
  renderLightbox = false,
}) => {
  const [images, setImages] = useState([]);
  const [items, setItems] = useState([]);
  const {isOpen, index, openLightbox, closeLightbox} = useLightbox();
  const className = attributes.className ?? '';
  const layout = getGalleryLayout(className, attributes.gallery_block_style ?? '');
  const postType = document.body.getAttribute('data-post-type');

  useEffect(() => {
    setItems(imagesToItems(images));
  }, [images]);

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
          {layout === 'slider' && <GalleryCarousel onImageClick={openLightbox} images={images} />}
          {layout === 'three-columns' && <GalleryThreeColumns onImageClick={openLightbox} images={images} postType={postType} />}
          {layout === 'grid' && <GalleryGrid onImageClick={openLightbox} images={images} />}
        </>
      ) : null}

      {(renderLightbox && items.length) ?
        <Lightbox isOpen={isOpen} index={index} items={items} onClose={closeLightbox} /> :
        null
      }
    </section>
  );
};
