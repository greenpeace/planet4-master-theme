import {ENFormFrontend} from './ENFormFrontend';
import {hydrateBlock} from '../../functions/hydrateBlock';

hydrateBlock('planet4-blocks/enform', ENFormFrontend);

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll('[data-render="planet4-blocks/enform"]').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = wp.element.createRoot(blockNode);
    rootElement.render(<ENFormFrontend {...attributes} />);
  }
);
