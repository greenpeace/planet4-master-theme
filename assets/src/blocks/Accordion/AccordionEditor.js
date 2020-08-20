/* eslint-disable no-unused-vars */
import { Component, Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import withCharacterCounter from '../../components/withCharacterCounter/withCharacterCounter';
import { URLInput } from '../../components/URLInput/URLInput';
import {
  PanelBody,
  CheckboxControl,
  TextControl as BaseTextControl,
  Tooltip,
  Button,
  Dashicon
} from '@wordpress/components';

const { RichText } = wp.blockEditor;
const { __ } = wp.i18n;
const TextControl = withCharacterCounter(BaseTextControl);
export class AccordionEditor extends Component {
  constructor () {
    super();
    this.state = { isToggleOn: false };

    this.handleErrors = this.handleErrors.bind(this);
    this.toAttribute = this.toAttribute.bind(this);
    this.handleCollapseClick = this.handleCollapseClick.bind(this);
  }

  handleCollapseClick () {
    this.setState(state => ({
      isToggleOn: !state.isToggleOn
    }));

    this.state.isToggleOn === true ? $('.panel').removeClass('visibility') : $('.panel').addClass('visibility');
  }

  toAttribute (attributeName) {
    const { setAttributes } = this.props;
    return value => {
      setAttributes({ [attributeName]: value });
    };
  }

  handleErrors (errors) {
    this.setState(errors);
  }

  // renders the settings
  renderEdit () {
    const { attributes } = this.props;

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title={__('Setting', 'planet4-blocks-backend')}>
            <TextControl
              label={__('Button Text', 'planet4-blocks-backend')}
              placeholder={__('Override button text', 'planet4-blocks-backend')}
              help={__('Leave the button text empty to hide the button on the front page.', 'planet4-blocks-backend')}
              value={attributes.accordion_btn_text}
              onChange={this.toAttribute('accordion_btn_text')}
            />
            <URLInput
              label={__('Button Link', 'planet4-blocks-backend')}
              value={attributes.accordion_btn_url}
              onChange={this.toAttribute('accordion_btn_url')}
            />
            <CheckboxControl
              label={__('Open in a new Tab', 'planet4-blocks-backend')}
              help={__('Open button link in new tab', 'planet4-blocks-backend')}
              value={attributes.button_link_new_tab}
              checked={attributes.button_link_new_tab}
              onChange={this.toAttribute('button_link_new_tab')}
            />
          </PanelBody>
        </InspectorControls>
      </Fragment>
    );
  }

  // renders the editor view
  renderView () {
    const { attributes } = this.props;

    return <Fragment>
      <div className="block accordion-block my-3">
        <Tooltip text={__('Leave empty the title and/or description of this block if you want to hide them on the front page.', 'planet4-blocks-backend')}>
          <header>
            <RichText
              tagName="h2"
              className="page-section-header mt-3"
              placeholder={__('Enter title', 'planet4-blocks-backend')}
              value={attributes.accordion_title}
              onChange={this.toAttribute('accordion_title')}
              keepPlaceholderOnFocus={true}
              withoutInteractiveFormatting
              characterLimit={60}
              multiline="false"
            />
          </header>
        </Tooltip>
        <RichText
          tagName="p"
          className="page-section-description"
          placeholder={__('Enter description', 'planet4-blocks-backend')}
          value={attributes.accordion_description}
          onChange={this.toAttribute('accordion_description')}
          keepPlaceholderOnFocus={true}
          withoutInteractiveFormatting
          characterLimit={200}
          multiline="true"
        />
        <div className="accordion-content my-2">
          <div className="accordion card-header"
            onClick={this.handleCollapseClick}
            // id={attributes.accordion_id, index+1}
          >
            <RichText
              tagName="h4"
              className="accordion-headline"
              placeholder={__('Enter headline', 'planet4-blocks-backend')}
              value={attributes.accordion_headline}
              onChange={this.toAttribute('accordion_headline')}
              keepPlaceholderOnFocus={true}
              withoutInteractiveFormatting
              // characterLimit={100}
              multiline="false"
            />
          </div>
          <div className="panel">
            <RichText
              tagName="p"
              className="accordion-text"
              placeholder={__('Enter text', 'planet4-blocks-backend')}
              value={attributes.accordion_text}
              onChange={this.toAttribute('accordion_text')}
              keepPlaceholderOnFocus={true}
            />
            <Tooltip text={__('Leave the button text empty to hide the button on the front page.', 'planet4-blocks-backend')}>
              <div className="btn btn-secondary btn-accordion">
                <RichText
                  tagName="div"
                  placeholder={__('Optional button', 'planet4-blocks-backend')}
                  value={ attributes.accordion_btn_text }
                  onChange={ this.toAttribute('accordion_btn_text') }
                  keepPlaceholderOnFocus={true}
                  withoutInteractiveFormatting
                  multiline="false"
                />
              </div>
            </Tooltip>
          </div>
        </div>
      </div>
      {/* <AccordionFrontend isEditing { ...attributes } handleErrors={ this.handleErrors } /> */}
    </Fragment>;
  }

  render () {
    return (
      <Fragment>
        {
          this.props.isSelected
            ? this.renderEdit()
            : null
        }
        {this.renderView()}
      </Fragment>
    );
  }
}
