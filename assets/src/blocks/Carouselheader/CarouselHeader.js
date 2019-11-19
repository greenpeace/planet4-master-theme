import React, {Component, Fragment} from 'react';
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
    this.refs = [];
    this.ssrRef = React.createRef();
    this.firstRender = true;
  }

  componentDidMount() {
    this.collapseSlides();
    if (this.ssrRef.current && this.ssrRef.current.currentFetchRequest) {
      // Get ServerSideRender component fetch request and attach resolve function.
      this.ssrRef.current.currentFetchRequest.then(function () {
        setTimeout(function () {
          initializeCarouselHeader();
        }, 1000);
      });
    }
  }

  componentDidUpdate() {
    if (this.firstRender) {
      this.collapseSlides();
      this.firstRender = false;
    }
  }

  getSnapshotBeforeUpdate(prevProps, prevState) {
    if (this.ssrRef.current && this.ssrRef.current.currentFetchRequest) {
      this.ssrRef.current.currentFetchRequest.then(function() {
        setTimeout(function () {
          initializeCarouselHeader();
        }, 1000);
      });
    }
  }

  /**
   * Collapse all active slides.
   */
  collapseSlides() {
    let refs = this.refs;
    Object.keys(this.refs).forEach(function (index) {
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
        <h3>{__('What style of carousel do you need?', 'p4ge')}</h3>

        <div>
          <LayoutSelector
            selectedOption={this.props.block_style}
            onSelectedLayoutChange={this.props.onBlockStyleChange}
            options={[
              {
                label: __('Zoom and slide to gray', 'p4ge'),
                image: window.p4ge_vars.home + 'images/carousel-with-preview.png',
                value: 'zoom-and-slide-to-gray',
                help: __('This carousel provides a fancy transition, and a preview for the next slide in an oblique shape.')
              }, {
                label: __('Full width classic', 'p4ge'),
                image: window.p4ge_vars.home + 'images/carousel-classic.png',
                value: 'full-width-classic',
                help: __('This is a full width slider with a classic look: big slides, fade transition, and no subheaders.'),
              },
            ]}
          />
        </div>

        <div>
          <CheckboxControl
            label={__('Carousel Autoplay', 'p4ge')}
            help={__('Select to trigger images autoslide', 'p4ge')}
            value={this.props.carousel_autoplay}
            checked={this.props.carousel_autoplay}
            onChange={(e) => this.props.onCarouselAutoplayChange(e)}
          />
        </div>

        {this.props.slides.map((slide, i) => {
          return (
            <Fragment>
              <CarouselHeaderSlide
                {...slide}
                onImageChange={this.props.onImageChange}
                onHeaderChange={this.props.onHeaderChange}
                onHeaderSizeChange={this.props.onHeaderSizeChange}
                onSubheaderChange={this.props.onSubheaderChange}
                onDescriptionChange={this.props.onDescriptionChange}
                onLinkTextChange={this.props.onLinkTextChange}
                onLinkUrlChange={this.props.onLinkUrlChange}
                onLinkNewTabChange={this.props.onLinkNewTabChange}
                onStyleChange={this.props.onStyleChange}
                onFocalPointsChange={this.props.onFocalPointsChange}
                hasSubheader={this.props.block_style !== 'full-width-classic'}
                index={i}
                key={i}
                ref={(instance) => {
                  this.refs[i] = instance
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
          <Button isDefault
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
                  block_style: this.props.block_style,
                  carousel_autoplay: this.props.carousel_autoplay,
                  slides: this.props.slides,
                }}
                ref={this.ssrRef}
              >
              </ServerSideRender>
            : <div>Not enough data available to render the block yet.</div>
          }

        </Preview>
      </Fragment>
    );
  };
}
