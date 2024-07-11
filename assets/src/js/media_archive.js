import {createRoot} from 'react-dom/client';
import ArchivePicker from './Components/ArchivePicker/ArchivePicker';

const rootElement = document.getElementById('archive-picker-root');

createRoot(rootElement).render(<ArchivePicker />);
