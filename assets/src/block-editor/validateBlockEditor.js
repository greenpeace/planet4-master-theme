const {registerPlugin} = wp.plugins;
const {useSelect, dispatch} = wp.data;
const {__} = wp.i18n;
const {useEffect, useRef} = wp.element;

export const validateBlockEditor = () => {
  registerPlugin('pre-publish-validation', {
    render: ValidationNotices,
  });
};

const getValidationState = select => {
  const {getEditedPostAttribute} = select('core/editor');
  const {getBlocks} = select('core/block-editor');

  const postTitle = Boolean(getEditedPostAttribute('title'));
  const featuredImage = Boolean(getEditedPostAttribute('featured_media'));
  const topicLink = checkTopicLinks(getBlocks());

  return {
    postTitle,
    featuredImage,
    topicLink,
    isValid: postTitle && featuredImage && topicLink,
  };
};

const checkTopicLinks = blocks => {
  return (
    blocks.filter(
      block =>
        block.name === 'planet4-blocks/topic-link' &&
        !block.attributes.imageUrl
    ).length === 0
  );
};

const buildValidationMessage = ({postTitle, featuredImage, topicLink}) => {
  const errors = [];

  if (!postTitle) {
    errors.push(__('Title is mandatory.', 'planet4-master-theme-backend'));
  }

  if (!featuredImage) {
    errors.push(__('Featured image is mandatory.', 'planet4-master-theme-backend'));
  }

  if (!topicLink) {
    errors.push(
      __(
        'A background image for all Topic Link blocks is mandatory.',
        'planet4-master-theme-backend'
      )
    );
  }

  if (errors.length === 0) {
    return null;
  }

  return errors.join(' ');
};

const ValidationNotices = () => {
  const state = useSelect(getValidationState);
  const {isValid} = state;

  const editorDispatch = dispatch('core/editor');
  const noticesDispatch = dispatch('core/notices');

  const previousMessageRef = useRef(null);
  const NOTICE_ID = 'pre-publish-validation';

  useEffect(() => {
    const message = buildValidationMessage(state);

    if (isValid) {
      editorDispatch.unlockPostSaving(NOTICE_ID);
    } else {
      editorDispatch.lockPostSaving(NOTICE_ID);
    }

    if (!message) {
      noticesDispatch.removeNotice(NOTICE_ID);
      previousMessageRef.current = null;
      return;
    }

    if (previousMessageRef.current === message) {
      return;
    }

    previousMessageRef.current = message;

    noticesDispatch.removeNotice(NOTICE_ID);
    noticesDispatch.createWarningNotice(message, {
      id: NOTICE_ID,
      isDismissible: true,
    });
  }, [state, isValid]);

  return null;
};
