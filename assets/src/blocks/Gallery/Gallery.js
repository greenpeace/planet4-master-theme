import {Component,Fragment} from "@wordpress/element";
import {
  TextControl as BaseTextControl,
  TextareaControl as BaseTextareaControl,
  FocalPointPicker,
  ServerSideRender,
} from '@wordpress/components';

import {MediaPlaceholder, MediaUploadCheck} from "@wordpress/blockEditor";

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
      const {__} = wp.i18n;

      const {gallery_block_style , gallery_block_title , gallery_block_description , image_data } = this.props;

      const dimensions = {width: 400, height: 100};

      const hasImages = !! image_data.length;

      return (
        <Fragment>
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
                <MediaPlaceholder
                  addToGallery={ hasImages }
                  disableMediaButtons={ hasImages  }
                  labels={ {
                    title: __( 'Select Gallery Images' ),
                    instructions: __('Upload an image or select from the media library.', 'p4ge'),
                  } }
                  onSelect={this.props.onSelectImage}
                  accept="image/*"
                  allowedTypes={["image"]}
                  multiple
                  value={ hasImages ? image_data : undefined }
                  onError={ this.onUploadError }
                  onFocus={ this.props.onFocus }
                />
              </MediaUploadCheck>
            </div>

            {image_data &&
            <div
              className={
                "wp-block-master-theme-gallery__FocalPointPicker"
              }
            >
              {__('Select gallery image focal point', 'p4ge')}
              <ul>
                {image_data.map((item, index) => {
                  return (
                    <li
                      key={index}
                    >
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
