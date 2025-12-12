import {getGalleryLayout, GALLERY_BLOCK_CLASSES} from './getGalleryLayout';
import {GalleryCarousel} from './GalleryCarousel';
import {GalleryThreeColumns} from './GalleryThreeColumns';
import {GalleryGrid} from './GalleryGrid';
import {useGalleryImages} from './useGalleryImages';

const {useSelect} = wp.data;
const {InspectorControls, MediaPlaceholder, MediaUploadCheck, RichText} = wp.blockEditor;
const {FocalPointPicker, PanelBody} = wp.components;
const {__} = wp.i18n;

const renderEdit = (attributes, setAttributes, isSelected) => {
  const {image_data, className, gallery_block_style} = attributes;

  const layout = getGalleryLayout(className, gallery_block_style);

  const dimensions = {width: 400, height: 100};

  const hasImages = !!image_data.length;

  const onSelectImage = value => {
    const image_ids = [];
    const imageData = [];
    for (const key in value) {
      image_ids.push(value[key].id);
      const img_id = value[key].id;
      imageData.push({url: value[key].url, focalPoint: {x: 0.5, y: 0.5}, id: img_id});
    }
    setAttributes({multiple_image: image_ids.join(',')});
    setAttributes({image_data: imageData});
  };

  const onFocalPointChange = (image_id, value) => {
    const updated_image_data = [];
    const gallery_block_focus_points = {};

    image_data.forEach(object => {
      if (object.id === image_id) {
        const x = parseFloat(value.x).toFixed(2);
        const y = parseFloat(value.y).toFixed(2);

        updated_image_data.push({url: object.url, focalPoint: {x, y}, id: image_id});
        gallery_block_focus_points[image_id] = `${x * 100}% ${y * 100}%`;
      } else {
        updated_image_data.push(object);
        const img_id = object.id;
        gallery_block_focus_points[img_id] = `${parseInt(object.focalPoint.x * 100)}% ${parseInt(object.focalPoint.y * 100)}%`;
      }
    });

    setAttributes({gallery_block_focus_points: JSON.stringify(gallery_block_focus_points)});
    setAttributes({image_data: updated_image_data});
  };

  const focalPointImages = layout === 'three-columns' ? image_data.slice(0, 3) : image_data;

  return (
    <>
      {(isSelected || !hasImages) &&
        <MediaUploadCheck>
          <MediaPlaceholder
            addToGallery={hasImages}
            labels={{
              title: __('Select Gallery Images', 'planet4-master-theme-backend'),
              instructions: __('Upload an JPEG image or select one from the media library.', 'planet4-master-theme-backend'),
            }}
            onSelect={onSelectImage}
            allowedTypes={['image']}
            accept={['image/jpg', 'image/jpeg']}
            multiple
            value={hasImages ? image_data : undefined}
          />
        </MediaUploadCheck>
      }
      {hasImages && (
        <InspectorControls>
          <PanelBody title={__('Settings', 'planet4-master-theme-backend')}>
            <div className="wp-block-master-theme-gallery__FocalPointPicker">
              <strong className="components-base-control__help">
                {__('Select gallery image focal point', 'planet4-master-theme-backend')}
              </strong>
              <p className="components-base-control__help">
                {__('Adjust the “Left” and “Top” fields to position the focal point of the image in percentage, where 0% is the top/left edge and 100% is the bottom/right edge.', 'planet4-master-theme-backend')}
              </p>
              <ul className="p-0">
                {focalPointImages.map((item, index) => (
                  <li key={index}>
                    <FocalPointPicker
                      __nextHasNoMarginBottom
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
    </ >
  );
};

const renderView = (attributes, setAttributes) => {
  const {
    gallery_block_title,
    gallery_block_description,
    gallery_block_style,
    className,
    multiple_image,
    gallery_block_focus_points,
  } = attributes;

  const layout = getGalleryLayout(className, gallery_block_style);

  const toAttribute = attributeName => value => setAttributes({[attributeName]: value});

  const {postType} = useSelect(select => ({
    postType: select('core/editor').getCurrentPostType(),
  }), []);

  const {images} = useGalleryImages({multiple_image, gallery_block_focus_points}, layout);

  return (
    <section className={`block ${GALLERY_BLOCK_CLASSES[layout]} ${className ?? ''}`}>
      <header>
        <RichText
          tagName="h2"
          className="page-section-header"
          placeholder={__('Enter title', 'planet4-master-theme-backend')}
          value={gallery_block_title}
          onChange={toAttribute('gallery_block_title')}
          withoutInteractiveFormatting
          allowedFormats={[]}
        />
      </header>
      <RichText
        tagName="p"
        className="page-section-description"
        placeholder={__('Enter description', 'planet4-master-theme-backend')}
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
};

export const GalleryEditor = ({isSelected, attributes, setAttributes}) => {
  const {multiple_image} = attributes;

  const {image_urls_array} = useSelect(select => {
    const imageUrlsArray = [];
    if (multiple_image) {
      const image_id_array = multiple_image.split(',');
      image_id_array.forEach(img_id => {
        const img_details = select('core').getMedia(img_id);
        if (img_details) {
          imageUrlsArray[img_id] = img_details.source_url;
        }
      });
    }
    return {image_urls_array: imageUrlsArray};
  }, []);

  const {image_data, gallery_block_focus_points} = attributes;

  // Prepare image_data array on edit gallery block.
  if (0 === image_data.length && 0 < image_urls_array.length) {
    const new_image_data = [];
    const focal_points_json = gallery_block_focus_points ? JSON.parse(gallery_block_focus_points) : {};

    image_urls_array.forEach(img_id => {
      let x,
        y;
      if (!focal_points_json[img_id]) {
        [x, y] = [50, 50];
      } else {
        [x, y] = focal_points_json[img_id].replace(/\%/g, '').split(' ');
      }

      new_image_data.push({
        url: image_urls_array[img_id],
        focalPoint: {
          x: parseInt(x) / 100,
          y: parseInt(y) / 100,
        },
        id: img_id,
      });
    });

    setAttributes({image_data: new_image_data});
  }

  return (
    <>
      {renderEdit(attributes, setAttributes, isSelected)}
      {renderView(attributes, setAttributes)}
    </>
  );
};

