import {Component,Fragment} from '@wordpress/element';
import {Preview} from '../../components/Preview';

import {
  MediaPlaceholder,
  InspectorControls,
  BlockControls,
  MediaUpload,
  MediaUploadCheck
} from '@wordpress/editor';

import {
  TextControl,
  ServerSideRender,
  FocalPointPicker,
  ToggleControl,
  RangeControl,
  PanelBody,
  Button,
  Toolbar,
  IconButton
} from '@wordpress/components';

export class Happypoint extends Component {
    constructor(props) {
      super(props);
    }

    renderEdit() {
      const {__} = wp.i18n;

      const dimensions = {width: 400, height: 100};

      const {focus_image, opacity, mailing_list_iframe, iframe_url, url, id} = this.props;

      let focal_point_params = {x:'',y:''};

      if (focus_image) {
        let focus_image_str = focus_image.replace(/%/g, '');
        let [x, y] = focus_image_str.split(' ');
        focal_point_params = {x: x/100,y: y/100};
      } else {
        focal_point_params = {x:0.5, y:0.5};
      }

      const getImageOrButton = (openEvent) => {
        if ( this.props.id && ( 0 < this.props.id ) ) {

          return (

            <div align='center'>
              <img
                src={ this.props.url }
                onClick={ openEvent }
                className='happypoint__imgs'
                width={'400px'}
                style={{padding: '10px 10px'}}
              />
            </div>

          );
        }
        else {
          return (
            <div className='button-container'>
              <Button
                onClick={ openEvent }
                className='button'>
                + {__('Select Background Image', 'p4ge')}
              </Button>
            </div>
          );
        }
      };

      return (
        <Fragment>
          <InspectorControls>
            <PanelBody title={__('Setting', 'p4ge')}>
              <RangeControl
                label={__('Opacity', 'p4ge')}
                value={opacity}
                onChange={this.props.onOpacityChange}
                min={1}
                max={100}
                initialPosition={opacity}
                help={__('We use an overlay to fade the image back. Use a number between 1 and 100, the higher the number, the more faded the image will look. If you leave this empty, the default of 30 will be used.', 'p4ge')}
              />
              <ToggleControl
                label={__('Use mailing list iframe', 'p4ge')}
                help={__('Use mailing list iframe', 'p4ge')}
                value={mailing_list_iframe}
                checked={this.props.mailing_list_iframe}
                onChange={this.props.onMailingListIframeChange}
              />
              <TextControl
                label={__('Iframe url', 'p4ge')}
                placeholder={__('Enter Iframe url', 'p4ge')}
                help={__('If a url is set in this field and the \'mailing list iframe\' option is enabled, it will override the planet4 engaging network setting.', 'p4ge')}
                value={iframe_url}
                onChange={this.props.onIframeUrlChange}
              />
            </PanelBody>
          </InspectorControls>
          <BlockControls>
            { this.props.id && ( 0 < this.props.id ) && (
              <Toolbar>
                <MediaUploadCheck>
                  <MediaUpload
                    onSelect={this.props.onSelectImage}
                    allowedTypes={['image']}
                    value={id}
                    type='image'
                    render={({ open }) => {
                      return (
                        <IconButton
                          className='components-icon-button components-toolbar__control'
                          label={__('Edit Image', 'p4ge')}
                          onClick={open}
                          icon='edit'
                        />
                      );
                    }}
                  />
                </MediaUploadCheck>
                <IconButton
                  className='components-icon-button components-toolbar__control'
                  label={__('Remove Image', 'p4ge')}
                  onClick={this.props.onRemoveImages}
                  icon='trash'
                />
              </Toolbar>
            )}
          </BlockControls>
          {__('Select Background Image', 'p4ge')}
          <div>
            <MediaUploadCheck>
              <MediaUpload
                title={__('Select Background Image', 'p4ge')}
                type='image'
                onSelect={this.props.onSelectImage}
                value={id}
                allowedTypes={['image']}
                render={ ({ open }) => getImageOrButton(open) }
              />
            </MediaUploadCheck>
          </div>
          {id && 0 < id &&
            <div>
              {__('Select focus point for background image', 'p4ge')}
              <FocalPointPicker
                url={url}
                dimensions={dimensions}
                value={focal_point_params}
                onChange={this.props.onFocalPointChange}
              />
            </div>
          }
        </Fragment>
      );
    }

    render() {
      return (
          <div>
              {
                this.props.isSelected
                ? this.renderEdit()
                : null
              }
              <Preview showBar={this.props.isSelected}>
                <ServerSideRender
                  block={'planet4-blocks/happypoint'}
                  attributes={{
                    id: this.props.id,
                    focus_image: this.props.focus_image,
                    opacity: this.props.opacity,
                    mailing_list_iframe: this.props.mailing_list_iframe,
                    iframe_url: this.props.iframe_url,
                    load_iframe: true,
                  }}>
                </ServerSideRender>
              </Preview>
          </div>
      );
    }
}
