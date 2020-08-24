const { registerPlugin } = wp.plugins;
const { PluginPrePublishPanel } = wp.editPost;
const { dispatch } = wp.data;
const {__} = wp.i18n;

export const blockEditorValidation = () => {
  registerPlugin( 'pre-publish-checklist', { render: PrePublishCheckList } );
};

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
  const postIsValid = invalidElements.length > 0

  if ( postIsValid ) {
    // Open "Analytics & Tracking" fields metabox, if closed.
    document.getElementById('p4_campaign_fields').classList.remove('closed');
    checkListMsg.push( __( 'Please check "Analytics & Tracking" section for required fields.', 'planet4-master-theme-backend' ) );

    invalidElements.forEach(element => {
      const fieldName = element.parentNode.querySelector('label').textContent;
      const message = ` - ${ fieldName } is a required field`;
      checkListMsg.push(message);
    });
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
