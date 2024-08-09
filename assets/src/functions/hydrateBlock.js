/**
 * This function hydrate a SSR Component
 * More info: https://reactjs.org/docs/react-dom.html#hydrate
 *
 * @param {*} blockName     refers to the block name
 * @param {*} Component     It's the component that is going to be rendered
 * @param {*} csrAttributes pass some attrs only to CSR (client-side rendering)
 */
export const hydrateBlock = (blockName, Component, csrAttributes = {}) => { // eslint-disable-line no-unused-vars
  const blocks = document.querySelectorAll(`[data-hydrate="${blockName}"]`);
  blocks.forEach(
    blockNode => {
      if (blockNode) {
        const attributes = JSON.parse(blockNode.dataset.attributes);
        wp.element.hydrateRoot(blockNode, <Component {...attributes} {...csrAttributes} />);
      }
    }
  );
};
