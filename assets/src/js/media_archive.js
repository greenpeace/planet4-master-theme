import ArchivePicker from './Components/ArchivePicker/ArchivePicker';
import {createRoot} from '@wordpress/element';
const rootElement = document.getElementById('archive-picker-root');

createRoot(rootElement).render(<ArchivePicker adminView={true} />);
