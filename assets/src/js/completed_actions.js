export const markActionsAsCompleted = () => {
  // Make sure that we are in a Resistance Hub template
  if (!document.body.classList.contains('p4_action-template-single-p4_action-resistance_hub')) {
    return;
  }

  // Make sure customization is enabled in the settings
  const isCustomizationEnabled = Boolean(window.p4_vars.features.actions_user_personalization);
  if (!isCustomizationEnabled) {
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

  // Mark completed actions. For non-completed ones, update onclick function.
  actions.forEach(action => {
    const actionId = action.classList[1].replace('post-', '');
    if (!actionId) { // This can happen for custom Taxe Action Boxout blocks.
      return;
    }
    if (completedActionsIds.includes(actionId)) {
      action.classList.add('completed');
    } else {
      action.onclick = () => localStorage.completedActions += `,${actionId}`;
    }
  });
};
