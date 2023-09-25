import {ArticlesFrontend} from './ArticlesFrontend';
import {createRoot} from 'react-dom/client';

document.querySelectorAll('[data-render="planet4-blocks/articles"]').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<ArticlesFrontend {...attributes.attributes} />);
  }
);
