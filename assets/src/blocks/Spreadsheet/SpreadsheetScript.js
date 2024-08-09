import {SpreadsheetFrontend} from './SpreadsheetFrontend';
import {hydrateBlock} from '../../functions/hydrateBlock';

hydrateBlock('planet4-blocks/spreadsheet', SpreadsheetFrontend);

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll('[data-render="planet4-blocks/spreadsheet"]').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = wp.element.createRoot(blockNode);
    rootElement.render(<SpreadsheetFrontend {...attributes.attributes} />);
  }
);
