import {createRoot} from 'react-dom/client';
import {ColumnsFrontend} from './ColumnsFrontend';

document.querySelectorAll('[data-render="planet4-blocks/columns"]').forEach(node => {
  const {attributes} = JSON.parse(node.dataset.attributes);
  createRoot(node).render(<ColumnsFrontend {...attributes} />);
});
