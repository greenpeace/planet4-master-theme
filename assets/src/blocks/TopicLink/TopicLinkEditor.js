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
} = wp.components;
const {__} = wp.i18n;

/**
 * TopicLinkEditor component for managing the Topic Link block in the editor.
 *
 * @param {Object} props - Component properties.
 * @param {Object} props.attributes - Block attributes.
 * @param {boolean} props.isSelected - Indicates if the block is selected.
 * @param {Function} props.setAttributes - Function to update block attributes.
 * @return {JSX.Element} The Topic Link Editor component.
 */
export const TopicLinkEditor = ({
  attributes,
  isSelected,
  setAttributes,
}) => {
  const {
    categoryId,
    focal_points,
    imageId: customImageId,
    imageUrl: customImageFromId,
  } = attributes;

  const {
    categoriesList,
    imageId,
    imageUrl,
    imageAlt,
    currentPostCategories,
  } = useSelect(select => {
    const categoriesList = [].concat(
      select('core').getEntityRecords('taxonomy', 'category', {
        hide_empty: true,
        per_page: -1,
      }) || []
    );

    const customImage = customImageId && select('core').getMedia(customImageId);
    const customImageFromId = customImage?.source_url;

    const imageId = customImageId;
    const imageUrl = customImageFromId;
    const imageAlt = customImage?.alt_text;
    const currentPostCategories = select('core/editor').getCurrentPost().categories || [];

    return {
      categoriesList,
      imageId,
      imageUrl,
      imageAlt,
      currentPostCategories,
    };
  }, [categoryId, customImageId, customImageFromId]);

  if (!categoriesList.length) {
    return __('Populating block\'s fields…', 'planet4-blocks-backend');
  }

  /**
   * Sets the block's category based on available categories or current post categories.
   * @return {Object} The selected category object.
   */
  const setBlockCategory = () => {
    const postCategory = categoriesList.find(category => category.id === currentPostCategories[0]);
    const blockCategory = categoriesList.find(category => category.id === categoryId);

    let selectedCategory = null;

    if (blockCategory) {
      selectedCategory = blockCategory;
      setAttributes({categoryId: parseInt(blockCategory.id)});
    } else if (postCategory) {
      selectedCategory = postCategory;
      setAttributes({categoryId: parseInt(postCategory.id)});
    } else {
      selectedCategory = categoriesList[0];
      setAttributes({categoryId: parseInt(categoriesList[0].id)});
    }

    setAttributes({categoryLink: selectedCategory?.link || ''});
    setAttributes({selectedCategory: selectedCategory?.name || ''});
    return selectedCategory;
  };

  /**
   * Sets the object's focal position as a CSS-compatible value.
   * @param {Object} focalPoints - Object containing x and y focal point values.
   * @return {string} A default focal point.
   */
  const setObjectPosition = focalPoints => {
    if (focal_points === undefined) {
      return '50% 50%';
    }
    const floatX = parseFloat(focalPoints.x).toFixed(2);
    const floatY = parseFloat(focalPoints.y).toFixed(2);
    setAttributes({focal_points: `${floatX * 100}% ${floatY * 100}%`});
  };

  /**
   * Converts a focal point string into an object with x and y properties.
   * @param {string} focalPoints - The focal points string (e.g., "50% 50%").
   * @return {Object} An object with x and y properties as decimal values.
   */
  const setFocalPoint = focalPoints => {
    const [x, y] = focalPoints.split(' ').map(value => parseFloat(value) / 100);
    return {x, y};
  };

  const selectedCategory = setBlockCategory();

  // Update attributes with image data for frontend
  setAttributes({
    imageUrl: imageUrl || '',
    imageAlt: imageAlt || '',
  });

  /**
   * Renders the block preview in the editor.
   * @return {JSX.Element} The block preview element.
   */
  const renderEditInPlace = () => (
    <section className="topic-link-block">
      <div className="background-image">
        {imageUrl && (
          <img
            src={imageUrl}
            alt={imageAlt}
            style={{objectPosition: focal_points}}
          />
        )}
      </div>
      <div className="topic-link-content">
        <p>Learn more about {selectedCategory?.name || ''}</p>
        <div className="chevron-icon"></div>
      </div>
    </section>
  );

  /**
   * Renders the Inspector Controls for the block.
   * @return {JSX.Element} The Inspector Controls component.
   */
  const addInspectorControls = () => (
    <InspectorControls>
      <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
        <SelectControl
          label={__('Select Category:', 'planet4-blocks-backend')}
          value={categoryId}
          options={[...categoriesList.map(category => ({label: category.name, value: category.id}))]}
          onChange={id => setAttributes({categoryId: parseInt(id)})}
        />
        {imageUrl && (
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
              value={setFocalPoint(focal_points)}
              onChange={value => setObjectPosition(value)}
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

  /**
   * Renders the Block Controls toolbar.
   * @return {JSX.Element} The Block Controls component.
   */
  const addBlockControls = () => {
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
