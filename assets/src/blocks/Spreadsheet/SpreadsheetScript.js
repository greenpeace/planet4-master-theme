import {SpreadsheetFrontend} from './SpreadsheetFrontend';

document.addEventListener('DOMContentLoaded', () => {
  const spreadsheetBlocks = [...document.querySelectorAll('[data-render="planet4-blocks/spreadsheet"]')];

  spreadsheetBlocks.forEach(blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    wp.element.render(<SpreadsheetFrontend {...attributes.attributes} />, blockNode);
  });
});
