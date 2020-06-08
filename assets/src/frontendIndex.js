import { SpreadsheetFrontend } from './blocks/Spreadsheet/SpreadsheetFrontend';

const COMPONENTS = {
  'planet4-blocks/spreadsheet': SpreadsheetFrontend
};

document.querySelectorAll( `[data-render]` ).forEach(
  blockNode => {
    const blockName = blockNode.dataset.render;
    const BlockFrontend = COMPONENTS[ blockName ];
    const attributes = JSON.parse( blockNode.dataset.attributes );
    wp.element.render( <BlockFrontend { ...attributes.attributes } />, blockNode );
  }
);
