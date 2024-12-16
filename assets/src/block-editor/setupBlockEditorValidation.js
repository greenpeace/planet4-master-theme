const {registerPlugin} = wp.plugins;
const {PluginPrePublishPanel} = wp.editor;
const {select, dispatch, subscribe, useSelect} = wp.data;
const {__} = wp.i18n;

const blockValidations = {};

let messages = [];
let canPublish = true;

const POST_TYPES_WITH_REQUIRED_FEATURED_IMAGE = ['p4_action', 'post', 'page', 'campaign'];

export const setupBlockEditorValidation = () => {
  subscribe(() => {
    const {getEditedPostAttribute, getCurrentPostType, getEditedPostContent} = select('core/editor');
    const {getBlocks} = select('core/block-editor');

    const title = getEditedPostAttribute('title');
    const featuredImage = getEditedPostAttribute('featured_media');
    const postType = getCurrentPostType();
    const postContent = getEditedPostContent();
    const blocks = getBlocks();
    const currentMessages = [];

    const invalidTitle = !title || title.trim().length <= 0;
    if (invalidTitle) {
      currentMessages.push('Title is required.');
    }

    const hasImageInContent = /<img.+wp-image-(\d+).*>/i.test(postContent);
    const needsFeaturedImage = POST_TYPES_WITH_REQUIRED_FEATURED_IMAGE.includes(postType) &&
      !featuredImage &&
      !hasImageInContent;

    if (needsFeaturedImage) {
      currentMessages.push('Featured image is required.');
    }

    // Get an array of Topic Link blocks without background image.
    const topicLinkBlocksNeedsImage = blocks
      .filter(block => block.name === 'planet4-blocks/topic-link')
      .filter(block => !block.attributes.imageUrl);

    // If there are Topic Link blocks without background image, push an error message.
    if (topicLinkBlocksNeedsImage.length > 0) {
      currentMessages.push('Background image in the Topic Link block is required.');
    }

    const invalidBlocks = blocks.reduce((invalidBlocksArray, block) => {
      // Normally `blocks` contains a valid list of blocks, however it can happen that one of them is `null` in rare
      // cases. It happened to me once while running with WordPress 5.8.1 and undoing multiple edits. This made the
      // editor crash while it's trying to access `block.name`.
      if (!block) {
        return; // eslint-disable-line array-callback-return
      }
      const validations = blockValidations[block.name] || {};

      const results = Object.entries(validations).reduce((resultsArray, [attrName, validate]) => {
        const value = block.attributes[attrName];
        const result = validate(value);

        if (!result.isValid) {
          resultsArray.push(result);
        }

        return resultsArray;
      }, []);

      invalidBlocksArray.push(...results);

      return invalidBlocksArray;
    }, []);
    invalidBlocks.forEach(block => currentMessages.push(...block.messages));

    const currentlyValid =
      (0 === invalidBlocks.length) &&
      (0 === topicLinkBlocksNeedsImage.length) &&
      !invalidTitle &&
      !needsFeaturedImage;

    messages = currentMessages;

    if (canPublish === currentlyValid) {
      return;
    }
    canPublish = currentlyValid;

    if (!canPublish) {
      dispatch('core/editor').lockPostSaving();
    } else {
      dispatch('core/editor').unlockPostSaving();
    }
  });

  registerPlugin('pre-publish-checklist', {render: PrePublishCheckList});
  wp.hooks.addFilter(
    'blocks.registerBlockType',
    'planet4-plugin-gutenberg-blocks',
    registerAttributeValidations
  );
};

document.addEventListener('change', e => {
  if (!e.target.matches('select[name="p4_campaign_name"]')) {
    return;
  }
  dispatch('core/editor').editPost({meta: {p4_campaign_name: e.target.value}});
});

const registerAttributeValidations = (settings, blockName) => {
  const {attributes} = settings;

  Object.keys(settings.attributes).forEach(attrName => {
    const attr = attributes[attrName];

    if (typeof attr.validation === 'function') {
      blockValidations[blockName] = blockValidations[blockName] || {};
      blockValidations[blockName][attrName] = attr.validation;
    }
  });

  return settings;
};

const PrePublishCheckList = () => {
  // This doesn't assign anything from useSelect, which is intended. We want to update the component whenever anything
  // that can affect validity changes. This could probably be done more properly by adding a store with `canPublish`.
  // eslint-disable-next-line no-shadow
  useSelect(select => [select('core/editor').getEditedPostAttribute('meta'), select('core/block-editor').getBlocks()]);
  return (
    <PluginPrePublishPanel
      title={__('Publish Checklist', 'planet4-blocks-backend')}
      initialOpen="true"
      className={!canPublish ? 'p4-plugin-pre-publish-panel-error' : ''}
      icon="none">
      { !!canPublish && <p>{ __('All good.', 'planet4-blocks-backend') }</p> }
      { !canPublish && <ul>
        { messages.map(msg =>
          <li key={msg}><p>{ msg }</p></li>
        ) }
      </ul> }

    </PluginPrePublishPanel>
  );
};
