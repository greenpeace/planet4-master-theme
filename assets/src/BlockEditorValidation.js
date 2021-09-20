const { registerPlugin } = wp.plugins;
const { PluginPrePublishPanel } = wp.editPost;
const { select, dispatch, subscribe, useSelect } = wp.data;
const {__} = wp.i18n;

const blockValidations = {};

let messages = [];
let canPublish = true;

export const blockEditorValidation = () => {
  subscribe(() => {
    const blocks = select( 'core/block-editor' ).getBlocks();

    const currentMessages = [];
    const invalidBlocks = blocks.reduce( (invalidBlocks, block ) => {
      // Normally `blocks` contains a valid list of blocks, however it can happen that one of them is `null` in rare
      // cases. It happened to me once while running with WordPress 5.8.1 and undoing multiple edits. This made the
      // editor crash while it's trying to access `block.name`.
      if (!block) {
        return;
      }
      const validations = blockValidations[ block.name ] || {};

      const results = Object.entries( validations ).reduce( (results, [attrName, validate]) => {
        const value = block.attributes[attrName];
        const result = validate(value);

        if (!result.isValid) {
          results.push(result);
        }

        return results;
      }, [] );

      invalidBlocks.push(...results);

      return invalidBlocks;
    }, []);
    invalidBlocks.forEach(block => currentMessages.push(...block.messages));

    const postType = wp.data.select('core/editor').getCurrentPostType();
    let currentlyValid = (0 === invalidBlocks.length);

    if ('campaign' === postType) {
      const meta = wp.data.select('core/editor').getEditedPostAttribute('meta');
      const metaValid = !!meta['p4_campaign_name'] && 'not set' !== meta['p4_campaign_name'];
      currentlyValid = currentlyValid && metaValid;
      if (!metaValid) {
        currentMessages.push(__('Please check "Analytics & Tracking" section for required fields.', 'planet4-master-theme-backend'));
        currentMessages.push('Global Project is a required field');
      }
    }
    messages = currentMessages;

    if (canPublish === currentlyValid) {
      return;
    }
    canPublish = currentlyValid;

    if (!canPublish) {
      dispatch( 'core/editor' ).lockPostSaving();
    } else {
      dispatch( 'core/editor' ).unlockPostSaving();
    }
  });

  registerPlugin( 'pre-publish-checklist', { render: PrePublishCheckList } );
  wp.hooks.addFilter(
    'blocks.registerBlockType',
    'planet4-plugin-gutenberg-blocks',
    registerAttributeValidations
  );
};

document.addEventListener('change', e=> {
  if (!e.target.matches('select[name="p4_campaign_name"]')) {
    return;
  }
  dispatch('core/editor').editPost({ meta: { p4_campaign_name: e.target.value } });
})

const registerAttributeValidations = ( settings, blockName ) => {
  const { attributes } = settings;

  Object.keys(settings.attributes).forEach( attrName => {
    const attr = attributes[ attrName ];

    if ( typeof attr.validation === 'function' ) {
      blockValidations[ blockName ] = blockValidations[ blockName ] || {};
      blockValidations[ blockName ][ attrName ] = attr.validation;
    }
  });

  return settings;
}


const PrePublishCheckList = () => {
  // This doesn't assign anything from useSelect, which is intended. We want to update the component whenever anything
  // that can affect validity changes. This could probably be done more properly by adding a store with `canPublish`.
  useSelect(select=>[select('core/editor').getEditedPostAttribute('meta'), select('core/block-editor').getBlocks()]);
  return (
    <PluginPrePublishPanel
      title={ __('Publish Checklist', 'planet4-master-theme-backend') }
      initialOpen="true"
      className={ !canPublish ? 'p4-plugin-pre-publish-panel-error' : '' }
      icon="none">
      { !!canPublish && <p>{ __('All good.', 'planet4-master-theme-backend') }</p> }
      { !canPublish && <ul>
        { messages.map(msg =>
          <li key={ msg }><p>{ msg }</p></li>
        ) }
      </ul> }

    </PluginPrePublishPanel>
  )
};
