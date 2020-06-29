import ArchivePicker from './Components/ArchivePicker';
import { render } from '@wordpress/element';

const rootElement = document.getElementById( 'archive-picker-root' );

render( <ArchivePicker/>, rootElement );
