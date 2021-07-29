import {Component, Fragment} from '@wordpress/element';
import {
  Button,
  CheckboxControl,
  ServerSideRender
} from '@wordpress/components';
import {LayoutSelector} from '../../components/LayoutSelector/LayoutSelector';
import {Preview} from '../../components/Preview';
import {CarouselHeaderSlide} from "./CarouselHeaderSlide";
import {initializeCarouselHeader} from "./CarouselHeaderFront";

export class CarouselHeader extends Component {
  constructor(props) {
    super(props);
    this.references = [];
    this.firstRender = true;
  }

  setDOMListener() {
    const carouselInterval = window.setInterval(
      () => {
        if (document.getElementById('carousel-wrapper-header')) {
          window.clearInterval(carouselInterval);
          initializeCarouselHeader();
        }
      },
      500
    );
  }

  componentDidMount() {
    this.collapseSlides();
    this.setDOMListener();
  }

  componentDidUpdate() {
    if (this.firstRender) {
      this.collapseSlides();
      this.firstRender = false;
    }
    this.setDOMListener();
  }

  getSnapshotBeforeUpdate(prevProps, prevState) {
    this.setDOMListener();

    return null;
  }

  /**
   * Collapse all active slides.
   */
  collapseSlides() {
    let refs = this.references;
    Object.keys(this.references).forEach(function (index) {
      if (null !== refs[index]) {
        refs[index].collapseSlide();
      }
    }.bind(refs));
  }

  /**
   * Add new slide to carousel header.
   */
  addNewSlide() {
    this.collapseSlides();
    this.props.addSlide();
  }

  /**
   * Remove slide to carousel header.
   */
  removeSlide() {
    this.props.removeSlide();
  }

  renderEdit() {
    const {__} = wp.i18n;

    return (

      <div>
        <h3>{__('Configure your carousel', 'p4ge')}</h3>

        <div>
          <div className='LayoutSelector'>
          <label>
            <img src={`${window.p4ge_vars.home}images/carousel-classic.png`}/>
            <p className="help">{__('This is a full width slider with a classic look: big slides and fade transition.')}</p>
          </label>
          </div>
        </div>

        <div>
          <CheckboxControl
            label={__('Carousel Autoplay', 'p4ge')}
            help={__('Select to trigger images autoslide', 'p4ge')}
            value={this.props.carousel_autoplay}
            checked={this.props.carousel_autoplay === true}
            onChange={(e) => this.props.onCarouselAutoplayChange(e)}
          />
        </div>

        {this.props.slides.map((slide, i) => {
          return (
            <Fragment key={i}>
              <CarouselHeaderSlide
                {...slide}
                onImageChange={this.props.onImageChange}
                onHeaderChange={this.props.onHeaderChange}
                onHeaderSizeChange={this.props.onHeaderSizeChange}
                onDescriptionChange={this.props.onDescriptionChange}
                onLinkTextChange={this.props.onLinkTextChange}
                onLinkUrlChange={this.props.onLinkUrlChange}
                onLinkNewTabChange={this.props.onLinkNewTabChange}
                onFocalPointsChange={this.props.onFocalPointsChange}
                index={i}
                key={i}
                ref={(instance) => {
                  this.references[i] = instance
                }}
              />
            </Fragment>
          );
        })}

        <div className='carousel-header-add-remove-slide'>
          <Button isPrimary
                  onClick={this.addNewSlide.bind(this)}
                  disabled={this.props.slides.length >= 4}
          >
            Add Slide
          </Button>
          <Button isSecondary
                  onClick={this.removeSlide.bind(this)} disabled={this.props.slides.length <= 1}
          >
            Remove Slide
          </Button>
        </div>
      </div>
    );
  }

  render() {
    return (
      <Fragment>
        {
          this.props.isSelected
            ? this.renderEdit()
            : null
        }
        <Preview showBar={this.props.isSelected}>
          {
            this.props.slides.length && this.props.slides[0].image > 0
            ? <ServerSideRender
                block={'planet4-blocks/carousel-header'}
                attributes={{
                  carousel_autoplay: this.props.carousel_autoplay,
                  slides: this.props.slides,
                }}
              >
              </ServerSideRender>
            : <div>Not enough data available to render the block yet.</div>
          }

        </Preview>
      </Fragment>
    );
  };
}
