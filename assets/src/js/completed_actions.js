const {__} = wp.i18n;

export const markActionsAsCompleted = () => {
  // Make sure customization is enabled in the settings
  const isCustomizationEnabled = Boolean(window.p4_vars.features.actions_user_personalization);
  if (!isCustomizationEnabled) {
    return;
  }

  // Make sure that we are in a Resistance Hub template
  if (!document.body.classList.contains('p4_action-template-single-p4_action-resistance_hub')) {
    return;
  }

  // Initialise completed actions in local storage if needed.
  const completedActions = localStorage.completedActions;
  let completedActionsIds = [];
  if (!completedActions) {
    localStorage.completedActions = '';
  } else {
    completedActionsIds = localStorage.completedActions.split(',');
  }

  const actions = document.querySelectorAll('.actions-list ul li, .boxout');
  if (!actions.length) {
    return;
  }

  // Mark completed actions and replace image with "completed" element.
  // For non-completed ones, update onclick function.
  actions.forEach(action => {
    const actionId = action.classList[1].replace('post-', '');
    if (!actionId) { // This can happen for custom Taxe Action Boxout blocks.
      return;
    }
    if (completedActionsIds.includes(actionId)) {
      action.classList.add('completed');
      const completedElement = document.createElement('div');
      const checkmark = document.createElement('span');
      completedElement.appendChild(checkmark);
      const text = document.createElement('span');
      text.textContent = __('Completed', 'planet4-blocks');
      completedElement.appendChild(text);
      completedElement.classList.add('completed-message');
      const figure = action.querySelector('.wp-block-post-featured-image'); // For Actions List blocks.
      const image = action.querySelector('img'); // For Take Action Boxout blocks.
      if (figure) {
        figure.replaceWith(completedElement);
      } else {
        image.replaceWith(completedElement);
      }
    } else {
      action.onclick = () => localStorage.completedActions += `,${actionId}`;
    }
  });
};
