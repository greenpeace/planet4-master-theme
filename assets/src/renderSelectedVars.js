import { VarPicker } from './VarPicker';

export const renderSelectedVars = ( rootElement, cssVars = [], lastTarget ) => {

  wp.element.render(
    <VarPicker
      initialOpen={ false }
      selectedVars={ cssVars }
      onCloseClick={ close }
      lastTarget={ lastTarget }
    />,
    rootElement
  );
};
