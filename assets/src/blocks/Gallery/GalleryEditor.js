import { Fragment } from "@wordpress/element";
import { FocalPointPicker, PanelBody } from '@wordpress/components';
import { getGalleryLayout, GALLERY_BLOCK_CLASSES } from './getGalleryLayout';
import { useSelect } from '@wordpress/data';
import { GalleryCarousel } from './GalleryCarousel';
import { GalleryThreeColumns } from './GalleryThreeColumns';
import { GalleryGrid } from './GalleryGrid';
import { useGalleryImages } from './useGalleryImages';
import { InspectorControls } from '@wordpress/block-editor';

import { MediaPlaceholder, MediaUploadCheck } from "@wordpress/blockEditor";

const { RichText } = wp.blockEditor;
const { __ } = wp.i18n;

const renderEdit = (attributes, setAttributes) => {
  const { image_data, className, gallery_block_style } = attributes;

  const layout = getGalleryLayout(className, gallery_block_style);

  const dimensions = { width: 400, height: 100 };

  const hasImages = !!image_data.length;

  const onSelectImage = value => {
    let image_ids = [];
    let image_data = [];
    for (const key in value) {
      image_ids.push(value[key].id);
      let img_id = value[key].id;
      image_data.push({ url: value[key].url, focalPoint: { x: 0.5, y: 0.5 }, id: img_id });
    }
    setAttributes({ multiple_image: image_ids.join(',') });
    setAttributes({ image_data });
  }

  const onFocalPointChange = (image_id, value) => {

    let updated_image_data = [];
    let gallery_block_focus_points = {};

    image_data.map(function (object) {
      if (object.id === image_id) {
        let x = parseFloat(value.x).toFixed(2);
        let y = parseFloat(value.y).toFixed(2);

        updated_image_data.push({ url: object.url, focalPoint: { x, y }, id: image_id });
        gallery_block_focus_points[image_id] = `${x * 100}% ${y * 100}%`;

      } else {
        updated_image_data.push(object);
        let img_id = object.id;
        gallery_block_focus_points[img_id] = `${parseInt(object.focalPoint.x * 100)}% ${parseInt(object.focalPoint.y * 100)}%`;
      }
    });

    setAttributes({ gallery_block_focus_points: JSON.stringify(gallery_block_focus_points) });
    setAttributes({ image_data: updated_image_data });
  }

  const focalPointImages = layout === 'three-columns' ? image_data.slice(0, 3) : image_data;

  return (
    <Fragment>
      <MediaUploadCheck>
        <MediaPlaceholder
          addToGallery={hasImages}
          labels={{
            title: __('Select Gallery Images', 'planet4-blocks-backend'),
            instructions: __('Upload an JPEG image or select one from the media library.', 'planet4-blocks-backend'),
          }}
          onSelect={onSelectImage}
          allowedTypes={["image"]}
          accept={['image/jpg','image/jpeg']}
          multiple
          value={hasImages ? image_data : undefined}
        />
      </MediaUploadCheck>
      {hasImages && (
        <InspectorControls>
          <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
            <div className="wp-block-master-theme-gallery__FocalPointPicker">
              <p>{__('Select gallery image focal point', 'planet4-blocks-backend')}</p>
              <ul>
                {focalPointImages.map((item, index) => (
                  <li key={index}>
                    <FocalPointPicker
                      url={item.url}
                      dimensions={dimensions}
                      value={item.focalPoint}
                      onChange={onFocalPointChange.bind(this, item.id)}
                      key={item.id}
                    />
                  </li>
                ))}
              </ul>
            </div>
          </PanelBody>
        </InspectorControls>
      )}
    </Fragment >
  );
}

const renderView = (attributes, setAttributes) => {
  const {
    gallery_block_title,
    gallery_block_description,
    gallery_block_style,
    className,
    multiple_image,
    gallery_block_focus_points
  } = attributes;

  const layout = getGalleryLayout(className, gallery_block_style);

  const toAttribute = attributeName => value => setAttributes({ [attributeName]: value });

  const { postType } = useSelect(select => ({
    postType: select('core/editor').getCurrentPostType()
  }), []);

  const { images } = useGalleryImages({ multiple_image, gallery_block_focus_points }, layout);

  return (
    <section className={`block ${GALLERY_BLOCK_CLASSES[layout]} ${className ?? ''}`}>
      <header className="articles-title-container">
        <RichText
          tagName="h2"
          className="page-section-header"
          placeholder={__('Enter title', 'planet4-blocks-backend')}
          value={gallery_block_title}
          onChange={toAttribute('gallery_block_title')}
          withoutInteractiveFormatting
          multiline="false"
          allowedFormats={[]}
        />
      </header>
      <RichText
        tagName="p"
        className="page-section-description"
        placeholder={__('Enter description', 'planet4-blocks-backend')}
        value={gallery_block_description}
        onChange={toAttribute('gallery_block_description')}
        withoutInteractiveFormatting
        allowedFormats={['core/bold', 'core/italic']}
      />
      {layout === 'slider' && <GalleryCarousel images={images || []} isEditing />}
      {layout === 'three-columns' && <GalleryThreeColumns images={images || []} postType={postType} />}
      {layout === 'grid' && <GalleryGrid images={images || []} />}
    </section>
  );
}

export const GalleryEditor = ({ isSelected, attributes, setAttributes }) => {
  const { multiple_image } = attributes;

  const { image_urls_array } = useSelect(select => {
    let image_urls_array = [];
    if (multiple_image) {
      let image_id_array = multiple_image.split(',');
      image_id_array.forEach(img_id => {
        let img_details = select('core').getMedia(img_id);
        if (img_details) {
          image_urls_array[img_id] = img_details.source_url;
        }
      });
    }
    return { image_urls_array };
  }, []);

  let { image_data, gallery_block_focus_points } = attributes;

  // Prepare image_data array on edit gallery block.
  if (0 === image_data.length && 0 < image_urls_array.length) {
    let new_image_data = [];
    let focal_points_json = gallery_block_focus_points ? JSON.parse(gallery_block_focus_points) : {};

    image_urls_array.forEach(img_id => {
      let x, y;
      if (!focal_points_json[img_id]) {
        [x, y] = [50, 50];
      } else {
        [x, y] = focal_points_json[img_id].replace(/\%/g, '').split(' ');
      }

      new_image_data.push({
        url: image_urls_array[img_id],
        focalPoint: {
          x: parseInt(x) / 100,
          y: parseInt(y) / 100
        },
        id: img_id
      });
    });

    setAttributes({ image_data: new_image_data });
  }

  return (
    <Fragment>
      {isSelected && renderEdit(attributes, setAttributes)}
      {renderView(attributes, setAttributes)}
    </Fragment>
  );
}

