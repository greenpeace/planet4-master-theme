import { VarPicker } from './VarPicker';

export const renderSelectedVars = ( rootElement, cssVars = [], lastTarget, groups, allVars ) => {

  wp.element.render(
    <VarPicker
      initialOpen={ false }
      selectedVars={ cssVars }
      groups={groups}
      onCloseClick={ close }
      lastTarget={ lastTarget }
      allVars={allVars}
    />,
    rootElement
  );
};
