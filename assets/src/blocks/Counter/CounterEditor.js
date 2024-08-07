import {InspectorControls, RichText} from '@wordpress/block-editor';
import {URLInput} from '../../block-editor/URLInput/URLInput';
import {CounterFrontend} from './CounterFrontend';

const {TextControl, TextareaControl, PanelBody} = wp.components;
const {__} = wp.i18n;

export const CounterEditor = ({setAttributes, attributes, isSelected}) => {
  const toAttribute = attributeName => value => setAttributes({[attributeName]: value});

  const renderEdit = () => (
    <>
      <InspectorControls>
        <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
          <div>
            <TextControl
              label={__('Number of Items Collected', 'planet4-blocks-backend')}
              placeholder={__('e.g. Signatures at this moment', 'planet4-blocks-backend')}
              type="number"
              value={attributes.completed}
              onChange={value => toAttribute('completed')(Number(value))}
              min={0}
            />
          </div>

          <div>
            <URLInput
              label={__('API URL for Goal Reached', 'planet4-blocks-backend')}
              placeholder={__('API URL for the total amount e.g. of signatures', 'planet4-blocks-backend')}
              value={attributes.completed_api}
              onChange={toAttribute('completed_api')}
            />
          </div>

          <div>
            <TextControl
              label={__('Goal', 'planet4-blocks-backend')}
              placeholder={__('e.g. Total amount of signatures', 'planet4-blocks-backend')}
              type="number"
              value={attributes.target}
              onChange={value => toAttribute('target')(Number(value))}
              min={0}
            />
          </div>

          <div>
            <TextareaControl
              label={__('Text', 'planet4-blocks-backend')}
              placeholder={__('e.g. "signatures collected of %target%"', 'planet4-blocks-backend')}
              value={attributes.text}
              onChange={toAttribute('text')}
            />
          </div>
          <div className="components-base-control__help">
            {__('Use the following placeholders within the text to showcase the real numbers when using an API URL: ', 'planet4-blocks-backend')}{' '}
            <code>%completed%</code>, <code>%target%</code>, <code>%remaining%</code>
          </div>
        </PanelBody>
      </InspectorControls>
    </>
  );

  const renderView = () => (
    <>
      <div className="counter-block">
        <header>
          <RichText
            tagName="h2"
            className="page-section-header"
            placeholder={__('Enter title', 'planet4-blocks-backend')}
            value={attributes.title}
            onChange={toAttribute('title')}
            withoutInteractiveFormatting
            allowedFormats={[]}
          />
        </header>
        <RichText
          tagName="p"
          className="page-section-description"
          placeholder={__('Enter description', 'planet4-blocks-backend')}
          value={attributes.description}
          onChange={toAttribute('description')}
          withoutInteractiveFormatting
          allowedFormats={['core/bold', 'core/italic']}
        />
      </div>
      <CounterFrontend isEditing {...attributes} />
    </>
  );

  return (
    <>
      {isSelected ? renderEdit() : null}
      {renderView()}
    </>
  );
};
