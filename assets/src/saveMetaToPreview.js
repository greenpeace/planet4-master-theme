export const savePreviewMeta = () => {
  const {apiFetch} = wp;
  const {getEditedPostAttribute, getCurrentPostId} = wp.data.select('core/editor');

  // eslint-disable-next-line no-console
  console.info('Saving preview meta...');

  if (!['draft', 'auto-draft'].includes(getEditedPostAttribute('status'))) {
    apiFetch({
      path: '/planet4/v1/save-preview-meta',
      method: 'POST',
      data: {
        post_id: getCurrentPostId(),
        meta: getEditedPostAttribute('meta'),
      },
    });
  }
};
