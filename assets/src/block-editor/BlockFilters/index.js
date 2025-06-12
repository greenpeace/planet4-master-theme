const {addFilter} = wp.hooks;
const {__} = wp.i18n;

export const addBlockFilters = () => {
  addFileBlockFilter();
  addImageBlockFilter();
  addGravityFormsBlockFilter();
  addQuoteBlockExtraDescription();
};

// Hide download button by default in the File block.
const addFileBlockFilter = () => addFilter('blocks.registerBlockType', 'planet4-blocks/filters/file', (settings, name) => {
  if ('core/file' !== name) {
    return settings;
  }

  settings.attributes.showDownloadButton.default = false;

  return settings;
});

// Add caption setting behaviour for Image block.
const addImageBlockFilter = () => addFilter('editor.BlockEdit', 'core/image/edit', BlockEdit => props => {
  if ('core/image' !== props.name) {
    return <BlockEdit {...props} />;
  }

  const {attributes, clientId} = props;
  const {id, className = ''} = attributes;

  // Get image data
  const image = wp.data.useSelect(select => id ? select('core').getMedia(id) : null);
  const credits = image?.meta?._credit_text;
  const captionText = image?.caption?.raw;
  // Compile data for insertion
  let image_credits = null;
  if (credits && credits.length > 0 && (!captionText || !captionText.includes(credits))) {
    image_credits = credits.includes('©') ? credits : `© ${credits}`;
  }

  const block_id = clientId ? `block-${clientId}` : null;

  // Force to use square images when the class `square-*` is added
  if (className.includes('square-')) {
    const size = className.slice(className.search('square-') + 'square-'.length).split(' ')[0] || 180;
    attributes.width = parseInt(size);
    attributes.height = parseInt(size);
  }

  return (<>
    <BlockEdit {...props} />
    {block_id && image_credits && (
      captionText ?
        <style dangerouslySetInnerHTML={{
          __html: `#${block_id} figcaption::after { content: " ${image_credits}"; }`,
        }}>
        </style> :
        <figcaption style={{marginTop: -24}}>{image_credits}</figcaption>
    )}
  </>);
});

// Enforce "AJAX" toggle setting enabled by default, on Gravity form block.
const addGravityFormsBlockFilter = () => addFilter('blocks.registerBlockType', 'planet4-blocks/filters/gravity-form', (settings, name) => {
  if ('gravityforms/form' !== name) {
    return settings;
  }

  settings.attributes.ajax.default = true;

  return settings;
});

// Add more details to Quote block description.
const addQuoteBlockExtraDescription = () => addFilter('blocks.registerBlockType', 'planet4-blocks/filters/quote', (settings, name) => {
  if ('core/quote' !== name) {
    return settings;
  }

  const {description} = settings;
  settings.description = `${description}. ${__('Try to keep your Quote short and concise, so that it stands out effectively.', 'planet4-blocks-backend')}`;

  return settings;
});
