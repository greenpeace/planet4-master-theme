import {SpreadsheetFrontend} from './SpreadsheetFrontend';
import {createRoot} from 'react-dom/client';

document.addEventListener('DOMContentLoaded', () => {
  const spreadsheetBlocks = [...document.querySelectorAll('[data-render="planet4-blocks/spreadsheet"]')];

  spreadsheetBlocks.forEach(blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<SpreadsheetFrontend {...attributes.attributes} />);
  });
});
