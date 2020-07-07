import { Component, Fragment } from '@wordpress/element';
import { TextControl } from '@wordpress/components';
import { ImagePicker, toSrcSet } from './ImagePicker';
import { SingleSidebar } from './archivePicker/SingleSidebar';
import { MultiSidebar } from './archivePicker/MultiSidebar';
import classNames from 'classnames';
import debounce from 'debounce';

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
    this.renderSidebar = this.renderSidebar.bind( this );
    this.search = this.search.bind( this );
    this.includeInWp = this.includeInWp.bind( this );
  }

  async componentDidMount() {
    await this.loadNextPage( {} );
  }

  async fetchImages( args ) {
    return apiFetch( {
      path: addQueryArgs( '/planet4/v1/image-archive/fetch', args ),
    } );
  }

  async loadNextPage() {
    const pageIndex = this.state.currentPage + 1;

    try {
      this.setState( { loading: true } );
      const nextImages = await this.fetchImages( {
        page: pageIndex,
        search_text: this.state.searchText,
      } );
      const withPageLabel = nextImages.map(image => ({...image, title: `${pageIndex} -- ${image.title}`}));
      this.setState( {
        currentPage: pageIndex,
        images: [ ...this.state.images, ...withPageLabel ]
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

  async search( text ) {
    if ( text.length < 2 ) {
      return;
    }
    this.setState( { images: [], searchText: text, currentPage: 0 } );
    await this.loadNextPage();
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

      try {

        return <li
          key={ id }
          data-wordpress-id={ wordpress_id }
          className={ classNames( { 'picker-selected': isSelected( image ) } ) }>
          <img
            srcSet={ toSrcSet( sizes, { maxWidth: 900 } ) }
            title={ `${title}` }
            alt={ alt }
            width={ 200 * ( original.width / original.height ) }
            height={ 200  }
            onClick={ ( event ) =>
              event.ctrlKey
                ? toggleMultiSelection( image )
                : toggleSingleSelection( image )
            }
          />
        </li>;
      } catch ( exception ) {
        return <li>
          key={id}
          <span>{image.title}</span>
          <span>No image available.</span>
        </li>;
      }
    } );
  }

  renderSidebar( parent ) {
    const selectedImages = parent.getSelectedImages();

    if ( selectedImages.length === 1 ) {
      return <SingleSidebar
        parent={ parent }
        includeInWp={ this.includeInWp }
        processingError={ this.state.processingError }
        processingImages={ this.state.processingImages }
      />;
    }

    if ( selectedImages.length > 1 ) {
      return <MultiSidebar
        parent={ parent }
        includeInWp={ this.includeInWp }
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
      <TextControl
        onChange={ debounce( this.search, 500 ) }
        disabled={ this.state.loading }
      />
      { loading && (
        <div className={ 'archive-picker-loading' }> loading...</div>
      ) }
      { !!error && (
        <div>
          <h3>API error:</h3>
          <p> { error } </p>
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

