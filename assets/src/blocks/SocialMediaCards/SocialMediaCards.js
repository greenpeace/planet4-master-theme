import { Component, Fragment } from '@wordpress/element';
import { Preview } from '../../components/Preview';


import {
  MediaPlaceholder,
  InspectorControls,
  BlockControls,
  MediaUpload,
  MediaUploadCheck
} from '@wordpress/editor';

import {
  TextControl,
  TextareaControl,
  ServerSideRender,
  FocalPointPicker,
  ToggleControl,
  RangeControl,
  PanelBody,
  Button,
  Toolbar,
  IconButton
} from '@wordpress/components';

export class SocialMediaCards extends Component {
  constructor(props) {
    super(props);
  }

  renderEdit() {
    const { __ } = wp.i18n;

    const dimensions = { width: 212, height: 212 };

    const { focus_image, id, multiple_image, image_data } = this.props;

    let focal_point_params = { x: '', y: '' };

    if (focus_image) {
      let focus_image_str = focus_image.replace(/%/g, '');
      let [x, y] = focus_image_str.split(' ');
      focal_point_params = { x: x / 100, y: y / 100 };
    } else {
      focal_point_params = { x: 0.5, y: 0.5 };
    }

    const getImageOrButton = (openEvent) => {
      if (0 < this.props.image_data.length) {

        return (

          this.props.image_data.map((item, index) => {
            return (
              <span>
                <img
                  src={item.url}
                  onClick={openEvent}
                  className="gallery__imgs"
                  key={index}
                  width='150 px'
                  style={{ padding: '10px 10px' }}
                />
              </span>
            );
          })

        );
      }
      else {
        return (
          <div className="button-container">
            <Button
              onClick={openEvent}
              className="button">
              + {__('Select Gallery Images', 'p4ge')}
            </Button>

            <div>{__('Select images in the order you want them to appear.', 'p4ge')}</div>
          </div>
        );
      }
    };



    let multiple_image_array = multiple_image ? multiple_image.split(',') : [];
    return (
      <Fragment>
        <div>
          <TextControl
            label={__('Title', 'planet4-blocks-backend')}
            placeholder={__('Enter title', 'planet4-blocks-backend')}
            help={__('Optional', 'planet4-blocks-backend')}
            value={this.props.title}
            onChange={this.props.onTitleChange}
          />
          <TextareaControl
            label={__('Description', 'planet4-blocks-backend')}
            placeholder={__('Enter description', 'planet4-blocks-backend')}
            help={__('Optional', 'planet4-blocks-backend')}
            value={this.props.description}
            onChange={this.props.onDescriptionChange}
          />
        </div>
        <BlockControls>
          {this.props.id && (0 < this.props.id) && (
            <Toolbar>

              {__('Select Gallery Images', 'p4ge')}
              <div>
                <MediaUploadCheck>
                  <MediaUpload
                    title={__('Select Gallery Images', 'p4ge')}
                    type="image"
                    onSelect={this.props.onSelectImage}
                    value={multiple_image_array}
                    allowedTypes={["image"]}
                    multiple="true"
                    render={({ open }) => getImageOrButton(open)}
                  />
                </MediaUploadCheck>
              </div>
              <IconButton
                className='components-icon-button components-toolbar__control'
                label={__('Remove Image', 'p4ge')}
                onClick={this.props.onRemoveImages}
                icon='trash'
              />
            </Toolbar>
          )}
        </BlockControls>
        {__('Select Gallery Images', 'p4ge')}
        <div>
          <MediaUploadCheck>
            <MediaUpload
              title={__('Select Gallery Images', 'p4ge')}
              type="image"
              onSelect={this.props.onSelectImage}
              value={multiple_image_array}
              allowedTypes={["image"]}
              multiple="true"
              render={({ open }) => getImageOrButton(open)}
            />
          </MediaUploadCheck>
        </div>

        {image_data &&
          <div
            className={
              "wp-block-master-theme-gallery__FocalPointPicker"
            }
          >
            <ul>
              {image_data.map((item, index) => {
                return (
                  <li
                    key={index}
                  >
                    {__('Select gallery image focal point', 'p4ge')}
                    <FocalPointPicker
                      url={item.url}
                      dimensions={dimensions}
                      value={item.focalPoint}
                      onChange={this.props.onFocalPointChange.bind(this, item.id)}
                      key={item.id}
                    />
                    {__('Enter message for social media', 'p4ge')}
                    <TextControl
                      label={__('Social message', 'planet4-blocks-backend')}
                      placeholder={__('Enter message', 'planet4-blocks-backend')}
                      help={__('Optional', 'planet4-blocks-backend')}
                      value={item.message}
                      onChange={this.props.onMessageChange.bind(this, item.id)}
                      key={item.id + 't1'}

                    />

                    {__('Enter URL to share', 'p4ge')}
                    <TextControl
                      label={__('Social URL', 'planet4-blocks-backend')}
                      placeholder={__('Enter URL to share', 'planet4-blocks-backend')}
                      help={__('Optional', 'planet4-blocks-backend')}
                      value={item.social_url}
                      onChange={this.props.onURLChange.bind(this, item.id)}
                      key={item.id + 't2'}
                    />
                  </li>
                );
              })}
            </ul>
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
            block={'planet4-blocks/social-media-cards'}
            attributes={{
              multiple_image: this.props.multiple_image,
              gallery_block_focus_points: this.props.gallery_block_focus_points,
            }}>
          </ServerSideRender>
        </Preview>
      </div>
    );
  }
}
