import { Component, Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import {
  TextControl,
  TextareaControl,
  PanelBody
} from '@wordpress/components';

import { URLInput } from "../../components/URLInput/URLInput";

import { CounterFrontend } from './CounterFrontend';

const { RichText } = wp.editor;
const { __ } = wp.i18n;

export class CounterEditor extends Component {
  constructor(props) {
    super(props);

    this.toAttribute = this.toAttribute.bind(this);
  }

  toAttribute(attributeName) {
    const { setAttributes } = this.props;
    return value => {
      setAttributes({ [attributeName]: value });
    }
  }

  renderEdit() {
    const { attributes } = this.props;

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title={__('Setting', 'p4ge')}>
            <div>
              <TextControl
                label={__('Completed', 'p4ge')}
                placeholder={__('e.g. number of signatures', 'p4ge')}
                type="number"
                value={attributes.completed}
                onChange={value => this.toAttribute('completed')(Number(value))}
              />
            </div>

            <div>
              <URLInput
                label={__('Completed API URL', 'p4ge')}
                placeholder={__('API URL of completed number. If filled in will override the \'Completed\' field', 'p4ge')}
                value={attributes.completed_api}
                onChange={this.toAttribute('completed_api')}
              />
            </div>

            <div>
              <TextControl
                label={__('Target', 'p4ge')}
                placeholder={__('e.g. target no. of signatures', 'p4ge')}
                type="number"
                value={attributes.target}
                onChange={value => this.toAttribute('target')(Number(value))}
              />
            </div>

            <div>
              <TextareaControl
                label={__('Text', 'p4ge')}
                placeholder={__('e.g. "signatures collected of %target%"', 'p4ge')}
                value={attributes.text}
                onChange={this.toAttribute('text')}
              />
            </div>
            <div className="sidebar-blocks-help">
              These placeholders can be used: <code>%completed%</code>, <code>%target%</code>, <code>%remaining%</code>
            </div>
          </PanelBody>
        </InspectorControls>
      </Fragment>
    );
  }

  renderView() {
    const { attributes } = this.props;

    return <Fragment>
      <div className="counter-block">
        <header>
          <RichText
            tagName="h2"
            className="page-section-header"
            placeholder={__('Enter title', 'p4ge')}
            value={attributes.title}
            onChange={this.toAttribute('title')}
            keepPlaceholderOnFocus={true}
            withoutInteractiveFormatting
            characterLimit={60}
            multiline="false"
          />
        </header>
        <RichText
          tagName="p"
          className="page-section-description"
          placeholder={__('Enter description', 'p4ge')}
          value={attributes.description}
          onChange={this.toAttribute('description')}
          keepPlaceholderOnFocus={true}
          withoutInteractiveFormatting
          characterLimit={400}
        />
      </div>
      <CounterFrontend isEditing {...attributes} />
    </Fragment>;
  }

  render() {
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
