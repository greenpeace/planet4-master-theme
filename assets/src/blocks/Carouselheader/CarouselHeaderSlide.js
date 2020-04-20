import {Component, Fragment} from '@wordpress/element';
import {
  Button,
  Dashicon,
  SelectControl,
  TextareaControl as BaseTextareaControl,
  TextControl as BaseTextControl,
  ToggleControl
} from '@wordpress/components';
import {URLInput} from '@wordpress/block-editor'
import {CarouselHeaderImage} from "./CarouselHeaderImage";
import withCharacterCounter from '../../components/withCharacterCounter/withCharacterCounter';

const {apiFetch} = wp;
const TextControl = withCharacterCounter(BaseTextControl);
const TextareaControl = withCharacterCounter(BaseTextareaControl);

export class CarouselHeaderSlide extends Component {
  constructor(props) {
    super(props);

    this.state = {
      image_id: null,
      image_url: null,
      focal_points: props.focal_points,
      isHidden: false,
    };
    this.getMedia();
  }

  /**
   * Set component's state for existing blocks.
   */
  getMedia() {

    if (this.props.image) {

      apiFetch(
        {
          path: `/wp/v2/media/${this.props.image}`
        }
      ).then(media => {
        this.setState({
          image_id: media.id,
          image_url: media.source_url
        });
      });
    }
  }

  onImageChange(image) {
    this.props.onImageChange(this.props.index, image.id);
    this.setState({image_id: image.id, image_url: image.url});
  }

  onImageRemove() {
    this.props.onImageChange(this.props.index, null);
    this.setState({image_id: null, image_url: null});
  }

  onFocalPointsChange(value) {
    this.props.onFocalPointsChange(this.props.index, value);
    this.setState({focal_points: value});
  }

  collapseSlide() {
    this.setState({isHidden: true});
  }

  openSlide() {
    this.setState({isHidden: false});
  }

  toggleSlide() {
    this.setState({isHidden: !this.state.isHidden});
  }

  render() {
    const {__} = wp.i18n;

    return (
      <Fragment>
        <div className={this.state.isHidden ? '' : 'carousel-header-slide-container'}>
          <div onClick={ev => this.toggleSlide()} className='slide-number-row'>
            <span>
              {__('Slide', 'p4ge')} {this.props.index + 1}
            </span>
            <span className={this.state.isHidden ? 'slide-arrow' : 'slide-arrow slide-open'}>
              <Dashicon icon="arrow-down-alt2"/>
            </span>
          </div>

          {!this.state.isHidden &&

          <Fragment>
            <div className='carousel-header-slide-options-wrapper'>
              <div>{__('Select image and focal point', 'p4ge')}</div>
              <CarouselHeaderImage
                image_id={this.state.image_id}
                image_url={this.state.image_url}
                focal_points={this.state.focal_points}
                onRemove={() => this.onImageRemove()}
                onChange={(image) => this.onImageChange(image)}
                onFocalPointsChange={(f) => this.onFocalPointsChange(f)}
              />
              <div className="row">
                <div className="col">
                  <TextControl
                    className="carouselh-header-input"
                    label={__('Header', 'p4ge')}
                    placeholder={__('Enter header', 'p4ge')}
                    value={this.props.header}
                    onChange={(e) => this.props.onHeaderChange(this.props.index, e)}
                    characterLimit={40}
                  />
                </div>
                <div className="col">
                  <SelectControl
                    label={__('Header text size', 'p4ge')}
                    value={this.props.header_size}
                    options={[
                      {label: 'h1', value: 'h1'},
                      {label: 'h2', value: 'h2'},
                      {label: 'h3', value: 'h3'},
                    ]}
                    onChange={(e) => this.props.onHeaderSizeChange(this.props.index, e)}
                  />
                </div>
              </div>
              {this.props.hasSubheader &&
              <TextControl
                label={__('Subheader', 'p4ge')}
                placeholder={__('Enter subheader', 'p4ge')}
                value={this.props.subheader}
                onChange={(e) => this.props.onSubheaderChange(this.props.index, e)}
                characterLimit={80}
              />
              }
              <TextareaControl
                label={__('Description', 'p4ge')}
                placeholder={__('Enter description of image', 'p4ge')}
                value={this.props.description}
                onChange={(e) => this.props.onDescriptionChange(this.props.index, e)}
                characterLimit={200}
              />
              <div className="row">
                <div className="col">
                  <TextControl
                    label={__('Link text and url', 'p4ge')}
                    placeholder={__('Enter link text for image', 'p4ge')}
                    value={this.props.link_text}
                    onChange={(e) => this.props.onLinkTextChange(this.props.index, e)}
                  />
                </div>
                <div className="col">
                  <URLInput
                    label={__('Url for link', 'p4ge')}
                    className="ch-url-input-control__input"
                    value={this.props.link_url}
                    onChange={(e) => this.props.onLinkUrlChange(this.props.index, e)}
                    autoFocus={false}
                  />
                </div>
              </div>
              <div className="row">
                <div className="col">
                  <div className="InlineToggleControl">
                    <ToggleControl
                      help={__('Open link in a new tab', 'p4ge')}
                      checked={this.props.link_url_new_tab}
                      onChange={(e) => this.props.onLinkNewTabChange(this.props.index, e)}
                    />
                  </div>
                </div>
              </div>
            </div>
          </Fragment>
          }
        </div>
      </Fragment>
    );
  };
}
