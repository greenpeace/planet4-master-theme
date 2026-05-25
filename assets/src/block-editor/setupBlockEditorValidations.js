const {__} = wp.i18n;
const {registerPlugin} = wp.plugins;
const {useSelect, dispatch} = wp.data;
const {useEffect, useRef} = wp.element;

/**
 * Setup the block editor validation.
 * It subscribes to changes in the editor and performs validation checks on title, featured image, and blocks.
 */
export const setupBlockEditorValidations = () => {
  registerPlugin('pre-publish-validation', {
    render: ValidationNotices,
  });
};

/**
 * Retrieves the current validation state from the block and post editor stores.
 * Intended to be used as a selector callback with `useSelect`.
 *
 * @param {Function} select - The WordPress data `select` function used to access store selectors.
 * @return {Object} - An object containing individual validation flags and a combined `isValid` flag.
 */
// Post types for which publish validation (title, featured image, topic link) is enforced.
const ALLOWED_POST_TYPES = ['post', 'page', 'p4_action', 'campaign'];

const getValidationState = select => {
  const {getEditedPostAttribute, getCurrentPostType} = select('core/editor');
  const {getBlocks} = select('core/block-editor');
  // skip=true for post types not in the whitelist (e.g. synced patterns, templates,
  // template parts) They are design artifacts, not editorial content, so all checks
  // short-circuit to valid. New checks added below automatically respect this flag.
  const skip = !ALLOWED_POST_TYPES.includes(getCurrentPostType());
  const allBlocks = getBlocks();
  const postTitle = skip || Boolean(getEditedPostAttribute('title'));
  const featuredImage = skip || Boolean(getEditedPostAttribute('featured_media'));
  const topicLink = skip || checkTopicLinks(allBlocks);
  // If there is a Gravity Forms block, we want to enforce setting the Global Project.
  const hasForm = !skip && hasGravityFormsBlock(allBlocks);
  const globalProject = getEditedPostAttribute('meta')?.p4_campaign_name;
  const validForms = skip || !hasForm || (hasForm && globalProject && globalProject !== 'not set');
  const imagesAlt = !skip && checkImageBlocksAltText(allBlocks);

  return {
    postTitle,
    featuredImage,
    topicLink,
    validForms,
    imagesAlt,
    isValid: postTitle && featuredImage && topicLink && validForms && imagesAlt,
  };
};

/**
 * Checks whether all `planet4-blocks/topic-link` blocks have a background image set.
 *
 * @param {Object[]} blocks - Array of block objects from the block editor.
 * @return {boolean} `true` if no topic-link blocks are missing an `imageUrl`; `false` otherwise.
 */
const checkTopicLinks = blocks => {
  return (
    blocks.filter(
      block =>
        block.name === 'planet4-blocks/topic-link' &&
        !block.attributes.imageUrl
    ).length === 0
  );
};

/**
 * Checks whether there is a Gravity Forms block.
 *
 * @param {Object[]} blocks - Array of block objects from the block editor.
 * @return {boolean} `true` when there is a Gravity Forms block, `false` otherwise.
 */
const hasGravityFormsBlock = blocks => Boolean(blocks.find(block => block.name === 'gravityforms/form'));

/**
 * Recursively checks whether every `core/image` block in the editor (including
 * nested image blocks inside Group / Columns / Cover / etc.) has a non-empty
 * `alt` attribute. Whitespace-only alt text is treated as missing.
 *
 * @param {Object[]} blocks - Array of block objects from the block editor.
 * @return {boolean} `true` if every core/image block has alt text; `false` otherwise.
 */
const checkImageBlocksAltText = blocks => {
  if (!Array.isArray(blocks) || blocks.length === 0) {
    return true;
  }

  for (const block of blocks) {
    if (!block) {
      continue;
    }

    if (block.name === 'core/image') {
      // Only flag image blocks that actually have media selected.
      // An empty placeholder block (no id/url yet) is not a publish-blocker.
      const hasMedia = Boolean(
        (block.attributes && (block.attributes.id || block.attributes.url))
      );
      const alt = block.attributes && typeof block.attributes.alt === 'string' ?
        block.attributes.alt.trim() :
        '';
      if (hasMedia && alt === '') {
        return false;
      }
    }

    if (block.innerBlocks && block.innerBlocks.length > 0) {
      if (!checkImageBlocksAltText(block.innerBlocks)) {
        return false;
      }
    }
  }

  return true;
};

/**
 * Builds a combined validation error message string based on the current validation state.
 * Returns `null` if all validations pass.
 *
 * @param {Object}  validationState               - The current validation state containing flags for each required field.
 * @param {boolean} validationState.postTitle     - Whether the post has a title.
 * @param {boolean} validationState.featuredImage - Whether the post has a featured image.
 * @param {boolean} validationState.topicLink     - Whether all Topic Link blocks have a background image.
 * @param {boolean} validationState.validForms    - If there is a Gravity Forms block on the page and a set Global Project.
 * @param {boolean} validationState.imagesAlt     - Whether every core/image block has non-empty alt text.
 * @return {string|null} A space-separated string of error messages, or `null` if there are no errors.
 */
const buildValidationMessage = ({postTitle, featuredImage, topicLink, validForms, imagesAlt}) => {
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

  if (!validForms) {
    errors.push(
      __(
        'You need to select a Global Project in the sidebar (Analytics & Tracking), because you are using a Gravity Forms block.',
        'planet4-master-theme-backend'
      )
    );
  }

  if (!imagesAlt) {
    errors.push(
      __(
        'Alt text is mandatory for all Image blocks.',
        'planet4-master-theme-backend'
      )
    );
  }

  if (errors.length === 0) {
    return null;
  }
  return errors.join(' ');
};

/**
 * React component that manages publish validation side effects in the block editor.
 *
 * @return {null} This component does not render any visible output.
 */
const ValidationNotices = () => {
  const state = useSelect(getValidationState);
  const {isValid} = state;
  const editorDispatch = dispatch('core/editor');
  const noticesDispatch = dispatch('core/notices');
  const previousMessageRef = useRef(null);
  const NOTICE_ID = 'pre-publish-validation';

  /**
   * Disables or enables the Gutenberg publish button based on validation state.
   */
  useEffect(() => {
    const applyDisabled = () => {
      const publishBtn = document.querySelector('.editor-post-publish-button__button');
      if (!publishBtn) {return false;}
      if (isValid) {
        publishBtn.removeAttribute('disabled');
        publishBtn.classList.remove('is-disabled');
      } else {
        publishBtn.setAttribute('disabled', 'disabled');
        publishBtn.classList.add('is-disabled');
      }
      return true;
    };

    if (!applyDisabled()) {
      const observer = new MutationObserver(() => {
        if (applyDisabled()) {observer.disconnect();}
      });
      observer.observe(document.body, {childList: true, subtree: true});
      return () => observer.disconnect();
    }
  }, [isValid]);
  /**
   * Applies a tooltip to the Gutenberg publish button.
   */
  useEffect(() => {
    /**
     * Attempts to find the publish button and set its `title` attribute to the current
     * validation error message. Removes the attribute if there are no errors.
     *
     * @return {boolean} `true` if the button was found and updated; `false` if not yet in the DOM.
     */
    const applyTooltip = () => {
      const publishBtn = document.querySelector('.editor-post-publish-button__button');
      if (!publishBtn) {return false;}
      const message = buildValidationMessage(state);
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

  /**
   * Manages post saving lock state and error notices based on validation.
   * Runs whenever `state` or `isValid` changes.
   */
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
  }, [state, isValid, noticesDispatch, editorDispatch]);

  return null;
};
