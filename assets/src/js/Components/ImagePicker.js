import { Component } from '@wordpress/element';
import classNames from 'classnames';

export const toSrcSet = sizes => sizes.map( size => `${ size.url } ${ size.width }w` ).join();

const isNearScrollEnd = ( event ) => {
  const { scrollHeight, scrollTop, clientHeight } = event.target;
  const tillEnd = ( scrollHeight - scrollTop - clientHeight ) / scrollHeight;

  return tillEnd < 0.1;
};

export class ImagePicker extends Component {

  constructor( props ) {
    super( props );
    this.state = {
      selectedIds: [],
    };
    this.isSelected = this.isSelected.bind( this );
    this.isOnlySelected = this.isOnlySelected.bind( this );
    this.toggleSingleSelection = this.toggleSingleSelection.bind( this );
    this.toggleMultiSelection = this.toggleMultiSelection.bind( this );
    this.getSelectedImages = this.getSelectedImages.bind( this );
  }

  isSelected( image ) {
    return this.state.selectedIds.includes( image.id );
  }

  isOnlySelected( image ) {
    return this.state.selectedIds.length === 1 && this.state.selectedIds.includes( image.id );
  }

  toggleSingleSelection( target ) {
    this.setState( {
      selectedIds: this.isOnlySelected( target ) ? [] : [ target.id ],
    } );
  }

  toggleMultiSelection( target ) {
    this.setState( {
      selectedIds:
        this.state.selectedIds.includes( target.id )
          ? this.state.selectedIds.filter( id => id !== target.id )
          : [ ...this.state.selectedIds, target.id ]
    } );
  }

  getSelectedImages() {
    return this.state.selectedIds.map( selected => this.props.images.find( image => image.id === selected ) );
  }

  render() {
    const {
      renderList = () => '',
      renderSidebar = () => '',
      onNearListBottom = async () => null,
    } = this.props;

    const selectedImages = this.getSelectedImages();

    return <div className={ 'image-picker' }>
      <ul
        className={ 'picker-list' }
        onScroll={ async ( event ) => {
          if ( isNearScrollEnd( event ) ) {
            await onNearListBottom();
          }
        } }
      >
        { renderList( this ) }
      </ul>
      { selectedImages.length > 0 && (
        <div className={ classNames( 'picker-sidebar', { 'picker-sidebar-single': selectedImages.length === 1 } ) }>
          { renderSidebar( this ) }
        </div>
      ) }
    </div>;
  }
}
