import {TimelineFrontend} from './TimelineFrontend';
import {hydrateBlock} from '../../functions/hydrateBlock';

hydrateBlock('planet4-blocks/timeline', TimelineFrontend);

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll('[data-render="planet4-blocks/timeline"]').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = wp.element.createRoot(blockNode);
    rootElement.render(<TimelineFrontend {...attributes} />);
  }
);
