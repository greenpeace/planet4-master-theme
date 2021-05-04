import { VarPicker } from './VarPicker';

export const renderSelectedVars = (rootElement, cssVars = [], lastTarget, groups, rawGroups, allVars, config) => {
  wp.element.render(
    <VarPicker
      config={ config }
      initialOpen={ false }
      selectedVars={ cssVars }
      groups={ groups }
      rawGroups={rawGroups}
      lastTarget={ lastTarget }
      allVars={ allVars }
    />,
    rootElement
  );
};
