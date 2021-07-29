import { Component, Fragment } from '@wordpress/element';
import { Preview } from '../../components/Preview';
import {
  MediaUpload,
  MediaUploadCheck
} from '@wordpress/editor';

import {
  TextControl,
  TextareaControl,
  ServerSideRender,
  Button,
  Tooltip
} from '@wordpress/components';

export class SocialMediaCards extends Component {
  constructor( props ) {
    super( props );
  }

  renderEdit() {
    const { __ } = wp.i18n;

    const { cards, onDeleteImage } = this.props;

    const getImageOrButton = ( openEvent ) => {
      if ( cards.length > 0 ) {

        return cards.map( ( card, index ) => (
          <span className="img-wrap">
                <Tooltip text={ __( 'Remove Image', 'planet4-blocks-backend' ) }>
                  <span className="close" onClick={ ev => {
                    onDeleteImage( card.image_id );
                    ev.stopPropagation();
                  } }>&times;</span>
                </Tooltip>
                <img
                  src={ card.image_url }
                  onClick={ openEvent }
                  className="gallery__imgs"
                  key={ index }
                  width='150 px'
                  style={ { padding: '10px 10px' } }
                />
              </span>
        ) );
      }

      return (
        <div className="button-container">
          <Button
            onClick={ openEvent }
            className="button">
            + { __( 'Select Images', 'planet4-blocks-backend' ) }
          </Button>

          <div>{ __( 'Select images in the order you want them to appear.', 'planet4-blocks-backend' ) }</div>
        </div>
      );
    };

    return (
      <Fragment>
        <div>
          <TextControl
            label={ __( 'Title', 'planet4-blocks-backend' ) }
            placeholder={ __( 'Enter title', 'planet4-blocks-backend' ) }
            help={ __( 'Optional', 'planet4-blocks-backend' ) }
            value={ this.props.attributes.title }
            onChange={ this.props.onTitleChange }
          />
          <TextareaControl
            label={ __( 'Description', 'planet4-blocks-backend' ) }
            placeholder={ __( 'Enter description', 'planet4-blocks-backend' ) }
            help={ __( 'Optional', 'planet4-blocks-backend' ) }
            value={ this.props.attributes.description }
            onChange={ this.props.onDescriptionChange }
          />
        </div>
        { __( 'Select Images', 'planet4-blocks-backend' ) }
        <div>
          <MediaUploadCheck>
            <MediaUpload
              title={ __( 'Select Images', 'planet4-blocks-backend' ) }
              type="image"
              onSelect={ this.props.onSelectImages }
              value={ cards.map( card => card.image_id ) }
              allowedTypes={ ["image"] }
              multiple="true"
              render={ ( { open } ) => getImageOrButton( open ) }
            />
          </MediaUploadCheck>
        </div>

        <div>
          <ul>
            { cards.map( ( card, index ) => {
              return (
                <li key={ index.toString() }>
                  <div className="row">
                    <div className="col-md-6">
                      <img
                        src={ card.image_url }
                        width={ 212 }
                        height={ 212 }
                      />
                    </div>
                    <div className="col-md-6">
                      <TextareaControl
                        label={ __( 'Social message', 'planet4-blocks-backend' ) }
                        placeholder={ __( 'Enter message', 'planet4-blocks-backend' ) }
                        help={ __( 'Optional. This message will be added as a quote on Facebook and Twitter shares.', 'planet4-blocks-backend' ) }
                        value={ card.message }
                        onChange={ this.props.onMessageChange.bind( this, index ) }
                      />

                      <TextControl
                        label={ __( 'Social URL', 'planet4-blocks-backend' ) }
                        placeholder={ __( 'Enter URL to share', 'planet4-blocks-backend' ) }
                        help={ __( 'Optional. If not specified then the url of the current page will be used.', 'planet4-blocks-backend' ) }
                        value={ card.social_url }
                        onChange={ this.props.onURLChange.bind( this, index ) }
                      />
                    </div>
                  </div>
                  <hr/>
                </li>
              );
            } ) }
          </ul>
        </div>
      </Fragment>
    );
  }

  render() {
    return (
      <div>
        { this.props.isSelected &&
        this.renderEdit()
        }
        <Preview showBar={ this.props.isSelected }>
          <ServerSideRender
            block={ 'planet4-blocks/social-media-cards' }
            attributes={ this.props.attributes }>
          </ServerSideRender>
        </Preview>
      </div>
    );
  }
}
