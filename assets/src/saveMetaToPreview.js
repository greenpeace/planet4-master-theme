// Save the current state of the post meta fields to the user's preview, so that any old values are invalidated.
export const saveMetaToPreview = () => {
  document.addEventListener('DOMContentLoaded', (event) => {
    const { apiFetch } = wp;
    const { getEditedPostAttribute, getCurrentPostId } = wp.data.select( 'core/editor' );

    if ( !['draft', 'auto-draft'].includes( getEditedPostAttribute( 'status' ) ) ) {
      apiFetch( {
        path: `/planet4/v1/save-preview-meta`,
        method: 'POST',
        data: {
          post_id: getCurrentPostId(),
          meta: getEditedPostAttribute('meta')
        },
      } );
    }

  })
};
