import { Component, Fragment } from '@wordpress/element';

export const toSrcSet = sizes => sizes.map( size => `${ size.url } ${ size.width }w` ).join();

export const largestSize = ( image ) => image.sizes.reduce(
  ( max, current ) => ( max === null || current.width > max.width ) ? current : max,
  null,
);

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

  renderImageList( images ) {
    return !images ? '' : images.map( image => {
      const {
        id,
        sizes,
        title,
        alt,
        wordpress_id,
      } = image;

      return <li
        key={ id }
        data-wordpress-id={ wordpress_id }
        className={ this.isSelected( image ) ? 'picker-selected' : '' }>
        <img
          srcSet={ toSrcSet( sizes ) }
          title={ title }
          alt={ alt }
          // width={ Math.min( 200, largestSize( image ).width / 12 ) }
          width={ 200 }
          onClick={ ( event ) =>
            event.ctrlKey
              ? this.toggleMultiSelection( image )
              : this.toggleSingleSelection( image )
          }
        />
      </li>;
    } );
  }

  render() {
    const {
      images,
      renderSidebar = () => '',
      onNearListBottom = async () => null,
    } = this.props;

    const selectedImages = this.getSelectedImages();

    return <div className={ 'image-picker' }>
      <ul
        className={ 'picker-list' }
        onScroll={ async ( event ) => {
          const target = event.target;
          const n = target.scrollHeight - target.scrollTop - target.clientHeight;
          const reachedThreshold = n < target.scrollHeight * 0.1;
          if ( reachedThreshold ) {
            await onNearListBottom();
          }
        } }
      >
        { this.renderImageList( images ) }
      </ul>
      { selectedImages.length > 0 && (
        <div className={ 'picker-sidebar picker-sidebar-single' }>
          { renderSidebar( { selectedImages } ) }
          {/* todo: extract multi sidebar */}
          { selectedImages.length > 1 && (
            <Fragment>
              <p>{ selectedImages.length } images selected</p>
              <ul
              >
                { selectedImages.map( selected => (
                  <li
                    key={ selected.id }
                  >
                    <img
                      srcSet={ toSrcSet( selected.sizes ) }
                      title={ selected.title }
                      alt={ selected.title }
                      width={ 80 }
                      onClick={ () => this.toggleMultiSelection( selected ) }
                    />
                  </li>
                ) ) }
              </ul>
            </Fragment>
          ) }
        </div>
      ) }
    </div>;
  }
}
