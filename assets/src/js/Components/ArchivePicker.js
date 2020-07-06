import { Component, Fragment } from '@wordpress/element';
import { ImagePicker, toSrcSet } from './ImagePicker';
import { SingleSidebar } from './archivePicker/SingleSidebar';
import { MultiSidebar } from './archivePicker/MultiSidebar';

const { apiFetch } = wp;
const { addQueryArgs } = wp.url;

class ArchivePicker extends Component {

  constructor( props ) {
    super( props );
    this.state = {
      loading: true,
      error: false,
      images: [],
      currentPage: 1,
      filters: {},
      searchText: null,
    };
    this.loadNextPage = this.loadNextPage.bind( this );
    this.updateFromUploadedResponse = this.updateFromUploadedResponse.bind( this );
    this.renderSidebar = this.renderSidebar.bind( this );
  }

  async componentDidMount() {
    try {
      const images = await this.fetchImages( {} );
      this.setState( {
        loading: false,
        images: images,
      } );
    } catch ( error ) {
      console.log( error );
      this.setState( { error } );
    }
  }

  async fetchImages( args ) {
    return apiFetch( {
      path: addQueryArgs( '/planet4/v1/image-archive/fetch', args ),
    } );
  }

  async loadNextPage() {
    const nextPage = this.state.currentPage + 1;

    try {
      this.setState( { loading: true } );
      const nextImages = await this.fetchImages( {
        page: nextPage
      } );
      this.setState( {
        currentPage: nextPage,
        images: [ ...this.state.images, ...nextImages ]
      } );
    } catch ( e ) {
      this.setState( { error: true } );
    } finally {
      this.setState( { loading: false } );
    }
  }

  updateFromUploadedResponse( updatedImages ) {
    const newImages = this.state.images.map( stateImage => {
      const updated = updatedImages.find( updatedImage => updatedImage.id === stateImage.id );
      if ( updated ) {
        return updated;
      }
      return stateImage;
    } );
    this.setState( {
      images: newImages,
    } );
  }

  renderList( parent ) {
    const { props, isSelected, toggleMultiSelection, toggleSingleSelection } = parent;

    const { images } = props;

    return !images ? '' : images.map( image => {
      const {
        id,
        sizes,
        title,
        alt,
        wordpress_id,
        original,
      } = image;

      return <li
        key={ id }
        data-wordpress-id={ wordpress_id }
        className={ isSelected( image ) ? 'picker-selected' : '' }>
        <img
          srcSet={ toSrcSet( sizes ) }
          title={ title }
          alt={ alt }
          width={ 200 }
          height={ 200 * ( original.height / original.width ) }
          onClick={ ( event ) =>
            event.ctrlKey
              ? toggleMultiSelection( image )
              : toggleSingleSelection( image )
          }
        />
      </li>;
    } );
  }

  renderSidebar( parent ) {
    const selectedImages = parent.getSelectedImages();

    if ( selectedImages.length === 1 ) {
      return <SingleSidebar
        parent={ parent }
        onIncludeInWP={ this.updateFromUploadedResponse }
      />;
    }

    if ( selectedImages.length > 1 ) {
      return <MultiSidebar
        parent={ parent }
        onIncludeInWP={ this.updateFromUploadedResponse }
      />;
    }
  }

  render() {
    const {
      loading,
      error,
      images,
    } = this.state;

    return <Fragment>
      { loading && (
        <div className={ 'archive-picker-loading' }> loading...</div>
      ) }
      { !!error && (
        <div>
          <h3>API error:</h3>
          <p> error.message </p>
        </div>
      ) }
      <ImagePicker
        images={ images }
        renderList={ this.renderList }
        renderSidebar={ this.renderSidebar }
        onNearListBottom={ async () => {
          if ( !this.state.loading ) {
            await this.loadNextPage();
          }
        } }
      />
    </Fragment>;
  }
}

export default ArchivePicker;

