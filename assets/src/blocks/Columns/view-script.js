import {createRoot} from 'react-dom/client';
import {ColumnsFrontend} from './ColumnsFrontend';
import metadata from './block.json';

document.querySelectorAll(`[data-render="${metadata.name}"]`).forEach(node => {
  const {attributes} = JSON.parse(node.dataset.attributes);
  createRoot(node).render(<ColumnsFrontend {...attributes} />);
});
