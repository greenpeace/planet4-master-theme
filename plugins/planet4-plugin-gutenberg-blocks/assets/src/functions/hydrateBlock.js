export const hydrateBlock = (blockName, Component) => {
  document.querySelectorAll( `[data-hydrate="${blockName}"]` ).forEach(
    blockNode => {
      const attributes = JSON.parse( blockNode.dataset.attributes );
      ReactDOM.hydrate( <Component attributes={ attributes.attributes } />, blockNode );
    }
  );
}
