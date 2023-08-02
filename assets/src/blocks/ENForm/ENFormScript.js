import {ENFormFrontend} from './ENFormFrontend';
import {createRoot} from 'react-dom/client';

document.querySelectorAll('[data-render=\'planet4-blocks/enform\']').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<ENFormFrontend {...attributes.attributes} />);
  }
);
