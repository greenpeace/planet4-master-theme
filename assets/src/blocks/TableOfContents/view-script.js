import {TableOfContentsFrontend} from './TableOfContentsFrontend';
import {createRoot} from 'react-dom/client';

document.querySelectorAll('[data-render="planet4-blocks/submenu"]').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<TableOfContentsFrontend {...attributes.attributes} />);
  }
);
