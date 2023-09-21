import {createRoot} from 'react-dom/client';
import {SplitTwoColumnsFrontend} from './SplitTwoColumnsFrontend';

document.querySelectorAll('[data-render="planet4-blocks/split-two-columns"]').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<SplitTwoColumnsFrontend {...attributes.attributes} />);
  }
);
