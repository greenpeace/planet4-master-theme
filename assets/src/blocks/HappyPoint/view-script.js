import {createRoot} from 'react-dom/client';
import metadata from './block.json';
import {HappyPointFrontend} from './HappyPointFrontend';

document.querySelectorAll(`[data-render="${metadata.name}"]`).forEach(
  blockNode => {
    const rootElement = createRoot(blockNode);
    const attributes = JSON.parse(blockNode.dataset.attributes);
    rootElement.render(<HappyPointFrontend {...attributes.attributes} />);
  }
);
