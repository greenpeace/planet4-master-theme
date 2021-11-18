import { GalleryCarousel } from './GalleryCarousel';
import { GalleryThreeColumns } from './GalleryThreeColumns';
import { GalleryGrid } from './GalleryGrid';
import { getGalleryLayout, GALLERY_BLOCK_CLASSES } from './getGalleryLayout';
import { getCaptionWithCredits } from './getCaptionWithCredits.js';
import { Lightbox } from '../../components/Lightbox/Lightbox';
import { useLightbox } from '../../components/Lightbox/useLightbox';

const imagesToItems = images => images.map(
  image => ({
    src: image.image_src,
    w: 0,
    h: 0,
    title: getCaptionWithCredits(image)
  })
);

export const GalleryFrontend = ({
  gallery_block_title,
  gallery_block_description,
  className,
  gallery_block_style,
  images,
}) => {
  const layout = getGalleryLayout(className, gallery_block_style);
  const postType = document.body.getAttribute('data-post-type');

  const { isOpen, index, openLightbox, closeLightbox } = useLightbox();

  const items = imagesToItems(images);

  return (
    <section className={`block ${GALLERY_BLOCK_CLASSES[layout]} ${className ?? ''}`}>
      <div className='container'>
        {gallery_block_title &&
          <header>
            <h2 className="page-section-header" dangerouslySetInnerHTML={{ __html: gallery_block_title }} />
          </header>
        }

        {gallery_block_description &&
          <div className="page-section-description" dangerouslySetInnerHTML={{ __html: gallery_block_description }} />
        }
        {layout === 'slider' && <GalleryCarousel onImageClick={openLightbox} images={images || []} />}
        {layout === 'three-columns' && <GalleryThreeColumns onImageClick={openLightbox} images={images || []} postType={postType} />}
        {layout === 'grid' && <GalleryGrid onImageClick={openLightbox} images={images || []} />}

        <Lightbox isOpen={isOpen} index={index} items={items} onClose={closeLightbox} />
      </div>
    </section>
  );
}
