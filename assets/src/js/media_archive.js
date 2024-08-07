import ArchivePicker from './Components/ArchivePicker/ArchivePicker';

const {createRoot} = wp.element;
const rootElement = document.getElementById('archive-picker-root');

createRoot(rootElement).render(<ArchivePicker />);
