import {CoversFrontend} from './CoversFrontend';
import {createRoot} from 'react-dom/client';

document.querySelectorAll('[data-render="planet4-blocks/covers"]').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<CoversFrontend {...attributes.attributes} />);
  }
);
