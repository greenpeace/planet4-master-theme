import { Component, Fragment } from '@wordpress/element';
import { Preview } from '../../components/Preview';


import {
  BlockControls,
  MediaUpload,
  MediaUploadCheck
} from '@wordpress/editor';

import {
  TextControl,
  TextareaControl,
  ServerSideRender,
  Button,
  Toolbar,
  IconButton, Tooltip
} from '@wordpress/components';

export class SocialMediaCards extends Component {
  constructor(props) {
    super(props);
  }

  renderEdit() {
    const { __ } = wp.i18n;

    const dimensions = { width: 212, height: 212 };

    const { multiple_image, image_data, onDeleteImage } = this.props;

    const getImageOrButton = (openEvent) => {
      if (0 < this.props.image_data.length) {

        return (

          this.props.image_data.map((item, index) => {
            return (
              <span className="img-wrap">
                <Tooltip text={__('Remove Image', 'p4ge')}>
                  <span className="close" onClick={ev => {
                    onDeleteImage(item.id);
                    ev.stopPropagation()
                  }}>&times;</span>
                </Tooltip>
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
              + {__('Select Images', 'p4ge')}
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
            label={__('Title', 'p4ge')}
            placeholder={__('Enter title', 'p4ge')}
            help={__('Optional', 'p4ge')}
            value={this.props.title}
            onChange={this.props.onTitleChange}
          />
          <TextareaControl
            label={__('Description', 'p4ge')}
            placeholder={__('Enter description', 'p4ge')}
            help={__('Optional', 'p4ge')}
            value={this.props.description}
            onChange={this.props.onDescriptionChange}
          />
        </div>
        {__('Select Images', 'p4ge')}
        <div>
          <MediaUploadCheck>
            <MediaUpload
              title={__('Select Images', 'p4ge')}
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
                    <div className="row">
                      <div className="col-md-6">
                        <img
                          src={item.url}
                          width={212}
                          height={212}
                        />
                      </div>
                      <div className="col-md-6">
                        <TextareaControl
                          label={__('Social message', 'p4ge')}
                          placeholder={__('Enter message', 'p4ge')}
                          help={__('Optional. This message will be added as a quote on Facebook and Twitter shares.', 'p4ge')}
                          value={item.message}
                          onChange={this.props.onMessageChange.bind(this, item.id)}
                          key={item.id + 't1'}
                        />

                        <TextControl
                          label={__('Social URL', 'p4ge')}
                          placeholder={__('Enter URL to share', 'p4ge')}
                          help={__('Optional. If not specified then the url of the current page will be used.', 'p4ge')}
                          value={item.social_url}
                          onChange={this.props.onURLChange.bind(this, item.id)}
                          key={item.id + 't2'}
                        />
                      </div>
                    </div>
                    <hr/>
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
              title: this.props.title,
              description: this.props.description,
            }}>
          </ServerSideRender>
        </Preview>
      </div>
    );
  }
}
