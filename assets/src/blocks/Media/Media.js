import {Component,Fragment} from "@wordpress/element";
import {
  TextControl as BaseTextControl,
  TextareaControl as BaseTextareaControl,
  ServerSideRender
} from '@wordpress/components';
import {MediaPlaceholder} from "@wordpress/editor";

import {Preview} from '../../components/Preview';
import withCharacterCounter from '../../components/withCharacterCounter/withCharacterCounter';
import {URLInput} from "../../components/URLInput/URLInput";

const TextControl = withCharacterCounter( BaseTextControl );
const TextareaControl = withCharacterCounter( BaseTextareaControl );

export class Media extends Component {
    constructor(props) {
      super(props);
    }

    renderEdit() {
      const {__} = wp.i18n;

      const {video_title,description,youtube_id} = this.props;

      return (
        <Fragment>
          <div>
            <TextControl
              label={__('Media Title', 'p4ge')}
              placeholder={__('Enter video title', 'p4ge')}
              value={video_title}
              onChange={this.props.onTitleChange}
              characterLimit={40}
            />
            <TextareaControl
              label={__('Description', 'p4ge')}
              help={__('(Optional)', 'p4ge')}
              value={description}
              onChange={this.props.onDescriptionChange}
              characterLimit={400}
            />
            <URLInput
              label={__('Media URL/ID', 'p4ge')}
              placeholder={__('Enter URL', 'p4ge')}
              value={youtube_id}
              onChange={this.props.onMediaUrlChange}
              help={__('Can be a YouTube, Vimeo or Soundcloud URL or an mp4, mp3 or wav file URL.', 'p4ge')}
            />
            <MediaPlaceholder
              labels={{ title: __('Video poster image [Optional]', 'p4ge'), instructions: __('Applicable for .mp4 video URLs only.', 'p4ge')}}
              icon="format-image"
              onSelect={this.props.onSelectImage}
              onSelectURL={this.props.onSelectURL}
              onError={this.props.onUploadError}
              accept="image/*"
              allowedTypes={["image"]}
            />
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
                { this.props.youtube_id &&
                  <ServerSideRender
                    block={'planet4-blocks/media-video'}
                    attributes={{
                      video_title: this.props.video_title,
                      description: this.props.description,
                      youtube_id: this.props.youtube_id,
                      video_poster_img: this.props.video_poster_img,
                    }}>
                  </ServerSideRender>
                }
              </Preview>
          </div>
      );
    }
}
