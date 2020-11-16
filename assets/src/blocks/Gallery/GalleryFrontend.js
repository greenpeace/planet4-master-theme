import { GalleryCarousel } from './GalleryCarousel';
import { GalleryThreeColumns } from './GalleryThreeColumns';
import { GalleryGrid } from './GalleryGrid';
import { getGalleryLayout, GALLERY_BLOCK_CLASSES } from './getGalleryLayout';

export const GalleryFrontend = ({
  gallery_block_title,
  gallery_block_description,
  className,
  gallery_block_style,
  images,
}) => {
  const layout = getGalleryLayout(className, gallery_block_style);
  const postType = document.body.getAttribute('data-post-type');

  return (
    <section className={`block ${GALLERY_BLOCK_CLASSES[layout]}`}>
      {gallery_block_title &&
        <header>
          <h2 className="page-section-header" dangerouslySetInnerHTML={{ __html: gallery_block_title }} />
        </header>
      }

      {gallery_block_description &&
        <div className="page-section-description" dangerouslySetInnerHTML={{ __html: gallery_block_description }} />
      }
      {layout === 'slider' && <GalleryCarousel images={images || []} />}
      {layout === 'three-columns' && <GalleryThreeColumns images={images || []} postType={postType} />}
      {layout === 'grid' && <GalleryGrid images={images || []} />}
    </section>
  );
}
