const {__} = wp.i18n;
import { Component, Fragment } from '@wordpress/element';
import { ImagePicker } from './ImagePicker';
import { archivePickerSidebar } from './archivePicker/archivePickerSidebar';
import { archivePickerList } from './archivePicker/archivePickerList';

const { apiFetch } = wp;
const { addQueryArgs } = wp.url;

class ArchivePicker extends Component {

  constructor( props ) {
    super( props );
    this.state = {
      loading: true,
      error: null,
      images: [],
      currentPage: 0,
      filters: {},
      searchText: null,
    };
    this.loadNextPage = this.loadNextPage.bind( this );
    this.updateFromUploadedResponse = this.updateFromUploadedResponse.bind( this );
    this.search = this.search.bind( this );
    this.includeInWp = this.includeInWp.bind( this );
  }

  async componentDidMount() {
    await this.loadNextPage();
  }

  async fetchImages( args ) {
    return apiFetch( {
      path: addQueryArgs( '/planet4/v1/image-archive/fetch', args ),
    } );
  }

  async loadNextPage( newSearch = false ) {
    this.setState( { loading: true } );

    const pageIndex = newSearch ? 1 : this.state.currentPage + 1;
    const searchedText = newSearch ? this.state.enteredSearch : this.state.searchText;

    try {
      const nextImages = await this.fetchImages( {
        page: pageIndex,
        search_text: searchedText,
      } );
      const withPageLabel = nextImages.map( image => ( {
        ...image,
        pagedTitle: `${ pageIndex } -- ${ image.title }`
      } ) );
      this.setState( {
        currentPage: pageIndex,
        images: [
          ...( newSearch ? [] : this.state.images ),
          ...withPageLabel
        ],
        searchText: searchedText,
      } );
    } catch ( error ) {
      this.setState( { error } );
    } finally {
      this.setState( { loading: false } );
    }
  }

  async includeInWp( ids ) {
    try {
      this.setState( { processingImages: true } );
      const updatedImages = await apiFetch( {
        method: 'POST',
        path: '/planet4/v1/image-archive/transfer',
        data: {
          ids: ids,
          use_original_language: false,
        }
      } );
      this.updateFromUploadedResponse( updatedImages );
    } catch ( e ) {
      console.log( e );
      this.setState( { processingError: e } );
    } finally {
      this.setState( { processingImages: false } );
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

  async search() {
    await this.loadNextPage( true );
  }

  render() {
    const {
      loading,
      error,
      images,
    } = this.state;

    return <Fragment>
      <form
        className={ 'archive-picker-search' }
        onSubmit={ event => {
          event.preventDefault();
          this.search();
        } }
        onChange={ event => this.setState( { enteredSearch: event.target.value } ) }
      >
        <input
          type={ 'text' }
          disabled={ this.state.loading }
        />
        <input type={ 'submit' } value={ __( 'Search', 'planet4-master-theme-backend' ) }/>
      </form>
      { loading && (
        <div className={ 'archive-picker-loading' }> loading...</div>
      ) }
      { !!error && (
        <div>
          <h3>API error:</h3>
          <p> { error.message } </p>
        </div>
      ) }
      <ImagePicker
        images={ images }
        renderList={ archivePickerList( this ) }
        renderSidebar={ archivePickerSidebar( this ) }
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

