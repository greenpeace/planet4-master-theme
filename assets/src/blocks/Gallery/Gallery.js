import {Component,Fragment} from "@wordpress/element";
import {
  TextControl as BaseTextControl,
  TextareaControl as BaseTextareaControl,
  FocalPointPicker,
  ServerSideRender,
  Toolbar,
  IconButton,
  Button, Tooltip
} from '@wordpress/components';
import {BlockControls,MediaUpload,MediaUploadCheck} from "@wordpress/editor";

import {LayoutSelector} from '../../components/LayoutSelector/LayoutSelector';
import {Preview} from '../../components/Preview';
import withCharacterCounter from '../../components/withCharacterCounter/withCharacterCounter';

const TextControl = withCharacterCounter( BaseTextControl );
const TextareaControl = withCharacterCounter( BaseTextareaControl );

export class Gallery extends Component {
    constructor(props) {
      super(props);
    }

    renderEdit() {
      const getImageOrButton = (openEvent) => {
        if ( 0 < this.props.image_data.length ) {

          return (

            this.props.image_data.map((item, index) => {
              return (
                <span key={index}>
                  <div className="img-wrap">
                    <Tooltip text={__('Remove Gallery Image', 'p4ge')}>
                      <span className="close" onClick={ev => {
                        onDeleteImage(item.id);
                        ev.stopPropagation()
                      }}>&times;</span>
                    </Tooltip>
                    <img
                      src={ item.url }
                      onClick={ openEvent }
                      className="gallery__imgs"
                      key={index}
                      width='150 px'
                      style={{padding: '10px 10px'}}
                    />
                  </div>
                </span>
              );
            })

          );
        }
        else {
          return (
            <div className="button-container">
              <Button
                onClick={ openEvent }
                className="button">
                + {__('Select Gallery Images', 'p4ge')}
              </Button>

              <div>{__('Select images in the order you want them to appear.', 'p4ge')}</div>
            </div>
          );
        }
      };

      const {__} = wp.i18n;

      const {gallery_block_style , gallery_block_title , gallery_block_description , multiple_image , image_data , onDeleteImage} = this.props;

      const dimensions = {width: 400, height: 100};

      let multiple_image_array = multiple_image ? multiple_image.split(',') : [];

      return (
        <Fragment>
          <BlockControls>
            { 0 < image_data.length && (
              <Toolbar>
                { 0 < multiple_image_array.length && (
                  <MediaUploadCheck>
                    <MediaUpload
                      onSelect={this.props.onSelectImage}
                      allowedTypes={["image"]}
                      value={multiple_image_array}
                      type="image"
                      multiple="true"
                      render={({ open }) => {
                        return (
                          <IconButton
                            className="components-icon-button components-toolbar__control"
                            label={__(
                              "Edit Images",
                              "mytheme-blocks"
                            )}
                            onClick={open}
                            icon="edit"
                          />
                        );
                      }}
                    />
                  </MediaUploadCheck>
                )}
                <IconButton
                  className="components-icon-button components-toolbar__control"
                  label={__("Remove Images", "mytheme-blocks")}
                  onClick={this.props.onRemoveImages}
                  icon="trash"
                />
              </Toolbar>
            )}
          </BlockControls>
          <h3>{__('What style of gallery do you need?', 'p4ge')}</h3>

          <div>
            <LayoutSelector
              selectedOption={gallery_block_style}
              onSelectedLayoutChange={this.props.onSelectedLayoutChange}
              options={[
                {
                  label: __('Slider', 'p4ge'),
                  image: window.p4ge_vars.home + 'images/gallery-slider.jpg',
                  value: 1,
                  help: __('The slider is a carousel of images. For more than 5 images, consider using a grid.', 'p4ge')
                },
                {
                  label: __('3 Column', 'p4ge'),
                  image: window.p4ge_vars.home + 'images/gallery-3-column.jpg',
                  value: 2,
                  help: __('The 3 column image display is great for accentuating text, and telling a visual story.', 'p4ge')
                },
                {
                  label: __('Grid', 'p4ge'),
                  image: window.p4ge_vars.home + 'images/gallery-grid.jpg',
                  value: 3,
                  help: __('The grid shows thumbnails of lots of images. Good to use when showing lots of activity.', 'p4ge')
                },
              ]}
            />
          </div>
          <div>
            <TextControl
              label={__('Title', 'p4ge')}
              placeholder={__('Enter Title', 'p4ge')}
              value={gallery_block_title}
              onChange={this.props.onTitleChange}
              characterLimit={40}
            />
            <TextareaControl
              label={__('Description', 'p4ge')}
              help={__('Please Enter Description', 'p4ge')}
              value={gallery_block_description}
              onChange={this.props.onDescriptionChange}
              characterLimit={400}
            />

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
                  render={ ({ open }) => getImageOrButton(open) }
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
                        onChange={this.props.onFocalPointChange.bind(this,item.id)}
                        key={item.id}
                      />
                    </li>
                  );
                })}
              </ul>
            </div>
            }
          </div>
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
                  block={'planet4-blocks/gallery'}
                  attributes={{
                    gallery_block_style: this.props.gallery_block_style,
                    gallery_block_title: this.props.gallery_block_title,
                    gallery_block_description: this.props.gallery_block_description,
                    multiple_image: this.props.multiple_image,
                    gallery_block_focus_points: this.props.gallery_block_focus_points,
                  }}>
                </ServerSideRender>
              </Preview>
          </div>
      );
    }
}
