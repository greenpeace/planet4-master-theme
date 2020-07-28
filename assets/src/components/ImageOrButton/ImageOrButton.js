import {Component} from '@wordpress/element';
import {Button} from '@wordpress/components';
import {MediaUpload,MediaUploadCheck} from '@wordpress/block-editor';

export class ImageOrButton extends Component {
  constructor(props) {
    super(props);
  }

  render() {
    const {__} = wp.i18n;

    if ( typeof this.props.disabled == 'undefined' ) {
      // this.props.disabled = false;
    }

    const getImageOrButton = (openEvent) => {
      if ( this.props.imageId ) {

        return (

          <img
            src={ this.props.imageUrl }
            onClick={ openEvent }
            className={ this.props.imgClass }
          />

        );
      }
      else {
        return (
          <div className='button-container'>
            <Button
              onClick={ openEvent }
              className='button'
              disabled={ this.props.disabled }>
              { this.props.buttonLabel }
            </Button>

            <div>{ this.props.help }</div>
          </div>
        );
      }
    };

    return <div className='ImageOrButton'>
      <MediaUploadCheck>
        <MediaUpload
          title={this.props.title}
          type='image'
          onSelect={this.props.onSelectImage}
          value={this.props.imageId}
          allowedTypes={['image']}
          render={ ({ open }) => getImageOrButton(open) }
        />
      </MediaUploadCheck>
    </div>;
  }
}
