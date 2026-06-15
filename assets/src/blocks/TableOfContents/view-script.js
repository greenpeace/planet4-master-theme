import {TableOfContentsFrontend} from './TableOfContentsFrontend';
import {createRoot} from 'react-dom/client';
import metadata from './block.json';

document.querySelectorAll(`[data-render="${metadata.name}"]`).forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<TableOfContentsFrontend {...attributes.attributes} />);
  }
);
