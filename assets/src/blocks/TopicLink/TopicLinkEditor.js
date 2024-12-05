/* eslint-disable no-shadow */

const {useSelect} = wp.data;
const {
  BlockControls,
  MediaUpload,
  MediaUploadCheck,
  InspectorControls,
} = wp.blockEditor;
const {
  SelectControl,
  PanelBody,
  ToolbarGroup,
  ToolbarButton,
  FocalPointPicker,
  Button,
} = wp.components;
const {__} = wp.i18n;

export const TopicLinkEditor = ({
  attributes,
  isSelected,
  setAttributes,
}) => {
  const {
    categoryId,
    focal_points,
    title: customTitle,
    imageId: customImageId,
  } = attributes;

  const {
    loading,
    actPageList,
    imageId,
    imageUrl,
    imageAlt,
  } = useSelect(select => {
    const actPageList = [].concat(
      select('core').getEntityRecords('taxonomy', 'category', {
        hide_empty: true,
        per_page: -1,
      }) || []
    );

    const actPage = actPageList.find(actPageFound => categoryId === actPageFound.id);

    if (categoryId && !actPage) {
      return {loading: true};
    }
    const customImage = customImageId && select('core').getMedia(customImageId);
    const customImageFromId = customImage?.source_url;

    const title = customTitle;
    const imageId = customImageId;
    const imageUrl = customImageFromId;
    const imageAlt = customImage?.alt_text;

    return {
      actPageList,
      title,
      imageId,
      imageUrl,
      imageAlt,
    };
  }, [categoryId, customTitle, customImageId]);

  if (loading || !actPageList.length) {
    return __('Populating block\'s fields…', 'planet4-blocks-backend');
  }

  const setObjectPosition = () => {
    if (focal_points === undefined) {
      return '50% 50%';
    }
    const floatX = parseFloat(focal_points.x).toFixed(2);
    const floatY = parseFloat(focal_points.y).toFixed(2);
    return `${floatX * 100}% ${floatY * 100}%`;
  };

  const actPageOptions = actPageList.map(actPage => ({label: actPage.name, value: actPage.id}));

  const selectedCategory = actPageList.find(actPage => actPage.id === categoryId);

  const renderEditInPlace = () => {
    if (!selectedCategory) {
      return (
        <section className="topic-link-block">
          <div className="topic-link-content">
            <p>Select a category from the sidebar to render this block.</p>
          </div>
        </section>
      );
    }

    return (
      <section className="topic-link-block">
        <div className="background-image">
          {imageUrl &&
            <img
              src={imageUrl}
              alt={imageAlt}
              style={{objectPosition: setObjectPosition()}}
            />}
        </div>
        <div className="topic-link-content">
          <p>
            Learn more about {selectedCategory.name}
          </p>
        </div>
      </section>
    );
  };

  const addInspectorControls = () => (
    <InspectorControls>
      <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
        <SelectControl
          label={__('Select Category:', 'planet4-blocks-backend')}
          value={categoryId}
          options={[
            {label: __('None', 'planet4-blocks-backend'), value: 0},
            ...actPageOptions,
          ]}
          onChange={id => setAttributes({categoryId: parseInt(id)})}
        />
        {selectedCategory && (
          <MediaUploadCheck>
            <MediaUpload
              title={__('Select Background Image', 'planet4-blocks-backend')}
              type="image"
              onSelect={({id}) => setAttributes({imageId: id})}
              value={imageId}
              allowedTypes={['image']}
              render={({open}) => (
                <Button onClick={open} className="button">
                  { imageId ? __('Change Background Image', 'planet4-blocks-backend') : __('Select Background Image', 'planet4-blocks-backend') }
                </Button>
              )}
            />
          </MediaUploadCheck>
        )}
        {selectedCategory && imageUrl && (
          <div className="wp-block-master-theme-gallery__FocalPointPicker">
            <strong className="components-base-control__help">
              {__('Select image focal point', 'planet4-blocks-backend')}
            </strong>
            <p className="components-base-control__help">
              {__('Adjust the “Left” and “Top” fields to position the focal point of the image in percentage, where 0% is the top/left edge and 100% is the bottom/right edge.', 'planet4-blocks-backend')}
            </p>
            <FocalPointPicker
              url={imageUrl}
              dimensions={{width: 400, height: 100}}
              value={focal_points}
              onChange={value => setAttributes({focal_points: value})}
            />
          </div>
        )}
      </PanelBody>
      <PanelBody title={__('Learn more about this block', 'planet4-blocks-backend')} initialOpen={false}>
        <p className="components-base-control__help">
          <a target="_blank" href="https://planet4.greenpeace.org/content/blocks/" rel="noreferrer">
            P4 Handbook Topic Link
          </a>
          {' '} &#128499;&#65039;;
        </p>
      </PanelBody>
    </InspectorControls>
  );

  const addBlockControls = () => {
    if (!selectedCategory) {
      return;
    }

    return (
      <BlockControls>
        <ToolbarGroup>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={({id}) => setAttributes({imageId: id})}
              allowedTypes={['image']}
              value={imageId}
              type="image"
              render={({open}) => (
                <ToolbarButton
                  className="components-icon-button components-toolbar__control"
                  label={imageId ? __('Change Background Image', 'planet4-blocks-backend') : __('Select Background Image', 'planet4-blocks-backend')}
                  onClick={open}
                  icon={imageId ? 'edit' : 'upload'}
                />
              )}
            />
          </MediaUploadCheck>
          {imageId && (
            <ToolbarButton
              className="components-icon-button components-toolbar__control"
              label={__('Remove Image', 'planet4-blocks-backend')}
              onClick={() => setAttributes({imageId: null})}
              icon="trash"
            />
          )}
        </ToolbarGroup>
      </BlockControls>
    );
  };

  return (
    <>
      {isSelected && addInspectorControls()}
      {isSelected && addBlockControls()}
      {renderEditInPlace()}
    </>
  );
};
