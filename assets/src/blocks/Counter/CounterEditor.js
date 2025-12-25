import {URLInput} from '../../block-editor/URLInput/URLInput';
import {CounterFrontend} from './CounterFrontend';

const {InspectorControls, RichText} = wp.blockEditor;
const {TextControl, TextareaControl, PanelBody} = wp.components;
const {__} = wp.i18n;

export const CounterEditor = ({setAttributes, attributes, isSelected}) => {
  const toAttribute = attributeName => value => setAttributes({[attributeName]: value});

  const renderEdit = () => (
    <>
      <InspectorControls>
        <PanelBody title={__('Settings', 'planet4-master-theme-backend')}>
          <div>
            <TextControl
              __nextHasNoMarginBottom
              __next40pxDefaultSize
              label={__('Number of Items Collected', 'planet4-master-theme-backend')}
              placeholder={__('e.g. Signatures at this moment', 'planet4-master-theme-backend')}
              type="number"
              value={attributes.completed}
              onChange={value => toAttribute('completed')(Number(value))}
              min={0}
            />
          </div>

          <div>
            <URLInput
              label={__('API URL for Goal Reached', 'planet4-master-theme-backend')}
              placeholder={__('API URL for the total amount e.g. of signatures', 'planet4-master-theme-backend')}
              value={attributes.completed_api}
              onChange={toAttribute('completed_api')}
            />
          </div>

          <div>
            <TextControl
              __nextHasNoMarginBottom
              __next40pxDefaultSize
              label={__('Goal', 'planet4-master-theme-backend')}
              placeholder={__('e.g. Total amount of signatures', 'planet4-master-theme-backend')}
              type="number"
              value={attributes.target}
              onChange={value => toAttribute('target')(Number(value))}
              min={0}
            />
          </div>

          <div>
            <TextareaControl
              __nextHasNoMarginBottom
              label={__('Text', 'planet4-master-theme-backend')}
              placeholder={__('e.g. "signatures collected of %target%"', 'planet4-master-theme-backend')}
              value={attributes.text}
              onChange={toAttribute('text')}
            />
          </div>
          <div className="components-base-control__help">
            {__('Use the following placeholders within the text to showcase the real numbers when using an API URL: ', 'planet4-master-theme-backend')}{' '}
            <code>%completed%</code>, <code>%target%</code>, <code>%remaining%</code>
          </div>
        </PanelBody>
        <PanelBody title={__('Learn more about this block ', 'planet4-master-theme-backend')} initialOpen={false}>
          <p className="components-base-control__help">
            <a target="_blank" href="https://planet4.greenpeace.org/content/blocks/counter/" rel="noreferrer">
            P4 Handbook Counter
            </a>
            {' '} &#129518;
          </p>
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
            placeholder={__('Enter title', 'planet4-master-theme-backend')}
            value={attributes.title}
            onChange={toAttribute('title')}
            withoutInteractiveFormatting
            allowedFormats={[]}
          />
        </header>
        <RichText
          tagName="p"
          className="page-section-description"
          placeholder={__('Enter description', 'planet4-master-theme-backend')}
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
