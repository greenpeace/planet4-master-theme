import { Component, Fragment } from '@wordpress/element';
import { ImagePicker } from './ImagePicker';
import { SingleSidebar } from './archivePicker/SingleSidebar';

const { apiFetch } = wp;
const { addQueryArgs } = wp.url;

class ArchivePicker extends Component {

  constructor( props ) {
    super( props );
    this.state = {
      loading: true,
      error: false,
      images: [],
      selectedImage: null,
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
    } catch (error) {
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
        images: [...this.state.images, ...nextImages]
      } );
    } catch (e) {
      this.setState( {
        error: true,
      } );
    } finally {
      this.setState( {
        loading: false,
      })
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

  renderSidebar( { selectedImages } ) {
    if ( selectedImages.length === 1 ) {
      return <SingleSidebar
        image={ selectedImages[ 0 ] }
        // todo: clean up state management.
        onIncludeInWP={ this.updateFromUploadedResponse }
      />;
    }
  }

  render() {

    return <Fragment>
      { this.state.loading && (
        <div className={ 'archive-picker-loading' }> loading...</div>
      ) }
      { !!this.state.error && (
        <Fragment>
          <h3>API error:</h3>
          <p> error.message </p>
        </Fragment>
      ) }
      { this.state.images.length > 0 && (
        <ImagePicker
          images={ this.state.images }
          renderSidebar={ this.renderSidebar }
          onNearListBottom={ async () => {
            if ( !this.state.loading ) {
              await this.loadNextPage();
            }
          } }
        />
      ) }
    </Fragment>;
  }
}

export default ArchivePicker;

