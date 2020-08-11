import { Component, Fragment } from '@wordpress/element';
import { getSizesUnder, toSrcSet } from '../ImagePicker';

const { __ } = wp.i18n;

const PREVIEW_MAX_SIZE = 1300;

const wpImageLink = ( id ) => `${ window.location.href.split( '/wp-admin' )[ 0 ] }/wp-admin/post.php?post=${ id }&action=edit`;

const largestSize = ( image ) => !image ? null : image.original;

const renderDefinition = ( key, value ) => ( <div>
  <dt>{ key }</dt>
  <dd>{ value }</dd>
</div> );

export class SingleSidebar extends Component {
  constructor( props ) {
    super( props );
    this.state = {
      imageLoaded: false,
    };
    this.renderImage = this.renderImage.bind(this);
    this.preloadImage = this.preloadImage.bind(this);
  }

  componentDidMount() {
    this.preloadImage();
  }

  async componentDidUpdate( prevProps ) {
    if ( prevProps.image !== this.props.image ) {
      this.preloadImage();
    }
  }

  async preloadImage() {
    this.setState( { imageLoaded: false } );
    await fetch( largestSize( getSizesUnder( this.props.image.sizes, PREVIEW_MAX_SIZE ) ) );
    this.setState( { imageLoaded: true } );
  }

  renderImage () {

    const { image } = this.props;
    const { original } = image;
    const aspectRatio = original.height / original.width ;

    return <div
      style={ { maxHeight: '10vh', position: 'relative', width: '100%', paddingTop: (aspectRatio * 100).toString() + '%'} }
    >
      <div
        style={{ position: 'absolute', top: 0, bottom: 0, left: 0, right: 0}}
      >
        {! this.state.imageLoaded
          ?  __( 'Loading image', 'planet4-master-theme-backend' )
          : (
            <img
              // key={ largestSize( image ) }
              srcSet={ toSrcSet( image.sizes, {maxWidth: PREVIEW_MAX_SIZE} ) }
              title={ image.title }
              alt={ image.title }
            />
          )  }

      </div>
    </div>;
  }

  render() {
    const {
      image,
      includeInWp = async () => null,
      processingError,
      processingImages,
    } = this.props;

    const original = largestSize( image );

    return <Fragment>
      { !!processingError && (
        <div className={ 'error' }>Error: { processingError.message }</div>
      ) }
      { !!processingImages && (
        <div className={ 'info' }>Processing...</div>
      ) }
      { image.wordpress_id ? (
        <a
          target='_blank'
          href={ wpImageLink( image.wordpress_id ) }
        >Wordpress image #{ image.wordpress_id }</a>
      ) : (
        <button
          onClick={ async () => {
            await includeInWp( [ image.id ] );
          } }
        >
          { __( 'Include in WP', 'planet4-master-theme-backend' ) }
        </button>
      ) }
      { this.renderImage() }
      <dl className={ 'picker-sidebar-fields' }>
        { renderDefinition(
          __( 'URL', 'planet4-master-theme-backend' ),
          <a href={ original.url }>{ original.url } </a>
        ) }
        { renderDefinition(
          __( 'Dimensions', 'planet4-master-theme-backend' ),
          `${ original.width } x ${ original.height }`
        ) }
        { renderDefinition(
          __( 'Title', 'planet4-master-theme-backend' ),
          image.title
        ) }
        { renderDefinition(
          __( 'Caption', 'planet4-master-theme-backend' ),
          image.caption
        ) }
        { renderDefinition(
          __( 'Credit', 'planet4-master-theme-backend' ),
          image.credit
        ) }
        { renderDefinition(
          __( 'Original language title', 'planet4-master-theme-backend' ),
          image.original_language_title,
        ) }
        { renderDefinition(
          __( 'Original language description', 'planet4-master-theme-backend' ),
          image.original_language_description,
        ) }
      </dl>
    </Fragment>;
  }

}
