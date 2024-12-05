const {useSelect} = wp.data;
const {
  RichText,
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
    take_action_page,
    focal_points,
    title: customTitle,
    imageId: customImageId,
  } = attributes;

  const {options: p4_options} = window.p4_vars;
  const isNewIA = p4_options.new_ia;

  const {
    loading,
    actPageList,
    title,
    imageId,
    imageUrl,
    imageAlt,
  } = useSelect(select => {
    const postId = select('core/editor').getCurrentPostId();
    const args = {
      per_page: -1,
      sort_order: 'asc',
      sort_column: 'post_title',
      post_status: 'publish',
    };

    // eslint-disable-next-line no-shadow
    const actPageList = [].concat(
      select('core').getEntityRecords('postType', 'page', {
        ...args,
        parent: isNewIA ? p4_options.take_action_page : p4_options.act_page,
      }) || [],
      ...(isNewIA ? (select('core').getEntityRecords('postType', 'p4_action', args) || []) : [])
    ).filter(a => parseInt(a.id) !== postId).sort((a, b) => {
      if (a.title.raw === b.title.raw) {
        return 0;
      }
      return a.title.raw > b.title.raw ? 1 : -1;
    });

    // eslint-disable-next-line no-shadow
    // const actPageList = [].concat(
    //   // Fetch the terms for the main taxonomy (e.g., 'category')
    //   select('core').getEntityRecords('taxonomy', 'category', {
    //     hide_empty: false, // Include terms with no posts
    //     per_page: -1, // Fetch all terms
    //   }) || [],
    //   // Conditionally fetch terms from another taxonomy if needed
    //   ...(isNewIA ?
    //     (select('core').getEntityRecords('taxonomy', 'custom_taxonomy', {per_page: -1}) || []) :
    //     [])
    // )
    //   .sort((a, b) => {
    //   // Alphabetical sort by term name
    //     if (a.name === b.name) {
    //       return 0;
    //     }
    //     return a.name > b.name ? 1 : -1;
    //   });

    const actPage = actPageList.find(actPageFound => take_action_page === actPageFound.id);

    // Because `useSelect` does an API call to fetch data, the actPageList will be empty the first time it's called.
    // Or first few times.
    if (take_action_page && !actPage) {
      return {loading: true};
    }
    const actPageImageId = actPage?.featured_media;

    const customImage = customImageId && select('core').getMedia(customImageId);
    const customImageFromId = customImage?.source_url;

    const title = !take_action_page ? customTitle : actPage.title.raw; // eslint-disable-line no-shadow
    const imageId = !take_action_page ? customImageId : actPageImageId; // eslint-disable-line no-shadow
    const imageUrl = !take_action_page ? customImageFromId : select('core').getMedia(actPageImageId)?.source_url; // eslint-disable-line no-shadow
    const imageAlt = !take_action_page ? customImage?.alt_text : ''; // eslint-disable-line no-shadow

    return {
      actPageList,
      title,
      imageId,
      imageUrl,
      imageAlt,
    };
  }, [take_action_page, customTitle, customImageId]);

  if (loading || !actPageList.length) {
    return __('Populating block\'s fields…', 'planet4-blocks-backend');
  }

  const toAttribute = attributeName => value => setAttributes({
    [attributeName]: value,
  });

  const onFocalChange = (focal_name, {x, y}) => {
    setAttributes({[focal_name]: `${parseInt(x * 100)}% ${parseInt(y * 100)}%`});
  };

  const removeImage = () => setAttributes({imageId: null});

  const selectImage = ({id}) => setAttributes({imageId: id});

  const actPageOptions = actPageList.map(actPage => ({label: actPage.title.raw, value: actPage.id}));

  const renderEditInPlace = () => (
    <section className="topic-link-block">
      <div className="background-image">
        {imageUrl && <img src={imageUrl} alt={imageAlt} />}
      </div>
      <div className="topic-link-content">
        <RichText
          tagName="div"
          placeholder={__('Learn more about', 'planet4-blocks-backend')}
          value={title}
          onChange={toAttribute('title')}
          disabled={true}
          withoutInteractiveFormatting
          allowedFormats={[]}
        />
      </div>
    </section>
  );

  const addInspectorControls = () => (
    <InspectorControls>
      <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
        <SelectControl
          label={__('Select Category:', 'planet4-blocks-backend')}
          value={take_action_page}
          options={[
            {label: __('None (custom)', 'planet4-blocks-backend'), value: 0},
            ...actPageOptions,
          ]}
          onChange={page => setAttributes({take_action_page: parseInt(page)})}
        />
        <MediaUploadCheck>
          <MediaUpload
            title={__('Select Background Image', 'planet4-blocks-backend')}
            type="image"
            onSelect={selectImage}
            value={imageId}
            allowedTypes={['image']}
            render={({open}) => (
              <Button onClick={open} className="button">
                { imageId ? __('Change Background Image', 'planet4-blocks-backend') : __('Select Background Image', 'planet4-blocks-backend') }
              </Button>
            )}
          />
        </MediaUploadCheck>
        {imageUrl && (
          <div className="wp-block-master-theme-gallery__FocalPointPicker">
            <strong className="components-base-control__help">
              {__('Select gallery image focal point', 'planet4-blocks-backend')}
            </strong>
            <p className="components-base-control__help">
              {__('Adjust the “Left” and “Top” fields to position the focal point of the image in percentage, where 0% is the top/left edge and 100% is the bottom/right edge.', 'planet4-blocks-backend')}
            </p>
            <FocalPointPicker
              url={imageUrl}
              dimensions={{width: 400, height: 100}}
              value={focal_points?.x && focal_points?.y ? focal_points : {x: .5, y: .5}}
              onChange={focus => onFocalChange('background_image_focus', focus)}
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
    return (
      <BlockControls>
        <ToolbarGroup>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={selectImage}
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
          <ToolbarButton
            className="components-icon-button components-toolbar__control"
            label={__('Remove Image', 'planet4-blocks-backend')}
            onClick={removeImage}
            icon="trash"
          />
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
