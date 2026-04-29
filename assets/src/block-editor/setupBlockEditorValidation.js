const {__} = wp.i18n;
const {registerPlugin} = wp.plugins;
const {useSelect, dispatch} = wp.data;
const {useEffect, useRef} = wp.element;

/**
 * Setup the block editor validation.
 * It subscribes to changes in the editor and performs validation checks on title, featured image, and blocks.
 */
export const setupBlockEditorValidation = () => {
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
    const applyTooltip = () => {
      const publishBtn = document.querySelector('.editor-post-publish-button');
      if (!publishBtn) {return false;}

      const message = 'Button disabled: ' + buildValidationMessage(state);

      if (message) {
        publishBtn.title = message;
      } else {
        publishBtn.removeAttribute('title');
      }
      return true;
    };

    if (!applyTooltip()) {
      const observer = new MutationObserver(() => {
        if (applyTooltip()) {observer.disconnect();}
      });
      observer.observe(document.body, {childList: true, subtree: true});
      return () => observer.disconnect();
    }
  }, [state]);

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
    noticesDispatch.createErrorNotice(message, {
      id: NOTICE_ID,
      isDismissible: false,
    });
  }, [state, isValid]);

  return null;
};
