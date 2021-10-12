import { hydrate } from 'react-dom';

export const hydrateBlock = (blockName, Component) => { // eslint-disable-line no-unused-vars
  const blocks = document.querySelectorAll( `[data-hydrate="${blockName}"]` );
  blocks.forEach(
    blockNode => {
      const attributes = JSON.parse( blockNode.dataset.attributes );
      hydrate(<Component { ...attributes } />, blockNode);
    }
  );
};
