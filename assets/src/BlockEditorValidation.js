const { registerPlugin } = wp.plugins;
const { PluginPrePublishPanel } = wp.editPost;
const { dispatch } = wp.data;
const {__} = wp.i18n;

export const blockEditorValidation = () => {
  registerPlugin( 'pre-publish-checklist', { render: PrePublishCheckList } );
};

const isValid = element => {
  // Apply validation only for campaign post types.
  if ('campaign' === $('#post_type').val() && 'required' === $(element).data('validation')) {
    if (!$(element).val() || 'not set' === $(element).val()) {
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
  const postIsValid = invalidElements.length > 0

  if ( postIsValid ) {
    // Open "Analytics & Tracking" fields metabox, if closed.
    $('#p4_campaign_fields').removeClass('closed');
    checkListMsg.push( __( 'Please check "Analytics & Tracking" section for required fields.', 'planet4-master-theme-backend' ) );
    invalidElements.forEach( element => { checkListMsg.push( ' - ' + $(element).parent().find('label').text() + ' is a required field' ) } );
  }

  let classname = '';
  if ( postIsValid ) {
    dispatch( 'core/editor' ).lockPostSaving();
    classname = 'p4-plugin-pre-publish-panel-error';
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
