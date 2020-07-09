import { Component, Fragment } from '@wordpress/element';
import { toSrcSet } from '../ImagePicker';

const { __ } = wp.i18n;
const wpImageLink = ( id ) => `${ window.location.href.split( '/wp-admin' )[ 0 ] }/wp-admin/post.php?post=${ id }&action=edit`;
const largestSize = ( image ) => !image ? null : image.original;
const renderDefinition = ( key, value ) => ( <div>
  <dt>{ key }</dt>
  <dd>{ value }</dd>
</div> );

export class SingleSidebar extends Component {
  constructor( props ) {
    super( props );
  }

  render() {
    const {
      parent,
      includeInWp = async () => null,
      processingError,
      processingImages,
    } = this.props;

    const image = parent.getSelectedImages()[ 0 ];

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
      <img
        srcSet={ toSrcSet( image.sizes, {maxWidth: 1300} ) }
        title={ image.title }
        alt={ image.title }
      />
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
