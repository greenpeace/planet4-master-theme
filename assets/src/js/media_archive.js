import ArchivePicker from './Components/ArchivePicker';
import {createRoot} from '@wordpress/element';
const rootElement = document.getElementById('archive-picker-root');

createRoot(rootElement).render(<ArchivePicker />);
