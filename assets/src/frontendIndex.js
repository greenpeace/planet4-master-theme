import { SpreadsheetFrontend } from './blocks/Spreadsheet/SpreadsheetFrontend';
import { CounterFrontend } from './blocks/Counter/CounterFrontend';

const COMPONENTS = {
  'planet4-blocks/spreadsheet': SpreadsheetFrontend,
  'planet4-blocks/counter': CounterFrontend
};

document.querySelectorAll( `[data-render]` ).forEach(
  blockNode => {
    const blockName = blockNode.dataset.render;
    const BlockFrontend = COMPONENTS[ blockName ];
    const attributes = JSON.parse( blockNode.dataset.attributes );
    wp.element.render( <BlockFrontend { ...attributes.attributes } />, blockNode );
  }
);
