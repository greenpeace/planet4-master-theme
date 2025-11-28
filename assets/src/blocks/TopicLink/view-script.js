import {createRoot} from 'react-dom/client';
import metadata from './block.json';
import TopicLink from './topic-link';

document.querySelectorAll(`[data-render="${metadata.name}"]`).forEach(
  blockNode => {
    const rootElement = createRoot(blockNode);
    const attributes = JSON.parse(blockNode.dataset.attributes);
    rootElement.render(<TopicLink {...attributes.attributes} />);
  }
);
