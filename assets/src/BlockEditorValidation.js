const { registerPlugin } = wp.plugins;
const { PluginPrePublishPanel } = wp.editPost;
const { select, dispatch } = wp.data;
const {__} = wp.i18n;

const blockValidations = {};

export const blockEditorValidation = () => {
  registerPlugin( 'pre-publish-checklist', { render: PrePublishCheckList } );
  wp.hooks.addFilter(
    'blocks.registerBlockType',
    'planet4-plugin-gutenberg-blocks',
    registerAttributeValidations
  );
};

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

const isValid = element => {
  const isCampaign = 'campaign' === document.getElementById('post_type').value;

  // No validation for non-campaign posts yet.
  if (!isCampaign) {
    return true;
  }

  if ('required' === element.dataset.validation) {
    const value = element.value;
    if (!value || 'not set' === value) {
      return false;
    }
  }

  return true;
};

const PrePublishCheckList = () => {
  let checkListMsg = [];

  // Filter CMB2 fields that have the 'data-validation' attribute set to 'required'.
  const elements = Array.from( document.querySelectorAll( '[data-validation]' ) );
  const invalidElements = elements.filter( element => !isValid( element ) );
  elements.forEach( element => { element.classList.remove( 'cmb2_required_field_error') } );
  invalidElements.forEach( element => { element.classList.toggle( 'cmb2_required_field_error') } );
  const hasInvalidMetas = invalidElements.length > 0

  if ( hasInvalidMetas ) {
    // Open "Analytics & Tracking" fields metabox, if closed.
    document.getElementById('p4_campaign_fields').classList.remove('closed');
    checkListMsg.push( __( 'Please check "Analytics & Tracking" section for required fields.', 'planet4-master-theme-backend' ) );

    const messages = invalidElements.map(element => {
      const fieldName = element.parentNode.querySelector('label').textContent;

      return ` - ${ fieldName } is a required field`;
    });

    checkListMsg.push(...messages);
  }

  const blocks = select( 'core/block-editor' ).getBlocks();
  const blockResults = blocks.map( ( block ) => {
    const validations = blockValidations[ block.name ];
    if ( !validations ) {
      return {block, invalids: []};
    }
    const results = Object.keys( validations ).map( attrName => {
      const validate = validations[ attrName ];

      return { attr: attrName, ...validate( block.attributes[ attrName ] ) };
    } );
    const invalids = results.filter( result => !result.isValid );

    return { block, invalids };
  } );

  const invalidBlocks = blockResults.filter( block => block.invalids.length > 0 );

  let classname = '';
  if ( hasInvalidMetas || invalidBlocks.length > 0) {
    dispatch( 'core/editor' ).lockPostSaving();
    classname = 'p4-plugin-pre-publish-panel-error';
    invalidBlocks.forEach( block => block.invalids.forEach( invalid => checkListMsg.push( ...invalid.messages ) ) );
  } else {
    dispatch( 'core/editor' ).unlockPostSaving();
    checkListMsg.push( __( 'All good.', 'planet4-master-theme-backend' ) );
  }

  return (
    <PluginPrePublishPanel
      title={ __( 'Publish Checklist', 'planet4-master-theme-backend' ) }
      initialOpen="true"
      className={ classname }
      icon="none">
        <ul>
          {checkListMsg.map((msg, index) => {
            return (
              <li key={index} >
                <p>{msg}</p>
              </li>
            );
          })}
        </ul>
    </PluginPrePublishPanel>
  )
};
