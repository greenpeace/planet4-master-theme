import {createRoot} from 'react-dom/client';
import {TimelineFrontend} from './TimelineFrontend';
import {NewTimelineFrontend} from './NewTimelineFrontend';
import {hydrateBlock} from '../../functions/hydrateBlock';

const isNewTimelineEnabled = Boolean(window.p4_vars.features.new_timeline_block);

hydrateBlock('planet4-blocks/timeline', isNewTimelineEnabled ? NewTimelineFrontend : TimelineFrontend);

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll('[data-render="planet4-blocks/timeline"]').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(isNewTimelineEnabled ?
      <NewTimelineFrontend {...attributes} /> : <TimelineFrontend {...attributes} />
    );
  }
);
