export const hydrateBlock = (blockName, Component) => {
  const blocks = document.querySelectorAll( `[data-hydrate="${blockName}"]` );
  blocks.forEach(
    blockNode => {
      const attributes = JSON.parse( blockNode.dataset.attributes );
      ReactDOM.hydrate( <Component { ...attributes } />, blockNode );
    }
  );
}
