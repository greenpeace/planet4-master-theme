import { Fragment, useState } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { URLInput } from '../../components/URLInput/URLInput';
import {
  PanelBody,
  CheckboxControl,
  Button,
} from '@wordpress/components';

import { debounce } from 'lodash';

const { RichText } = wp.blockEditor;
const { __ } = wp.i18n;

// Renders the editor view
const renderView = ({ title, description, tabs, className }, setAttributes, isSelected, updateTabAttribute) => {

  const toAttribute = attributeName => value => setAttributes({
    [attributeName]: value
  });

  return (
    <div className='block accordion-block'>
      <header>
        <RichText
          tagName="h2"
          className="page-section-header"
          placeholder={__('Enter title', 'planet4-blocks-backend')}
          value={title}
          onChange={toAttribute('title')}
          keepPlaceholderOnFocus={true}
          withoutInteractiveFormatting
          characterLimit={60}
          multiline="false"
        />
      </header>
      <RichText
        tagName="p"
        className="page-section-description"
        placeholder={__('Enter description', 'planet4-blocks-backend')}
        value={description}
        onChange={toAttribute('description')}
        keepPlaceholderOnFocus={true}
        withoutInteractiveFormatting
        characterLimit={200}
      />
      {tabs.map((tab, index) => (
        <div key={`accordion-content-${index}`} className={`accordion-content ${className}`}>
          <RichText
            tagName="h4"
            className={`accordion-headline ${isSelected ? 'open' : ''}`}
            placeholder={__('Enter headline', 'planet4-blocks-backend')}
            value={tab.headline}
            onChange={updateTabAttribute('headline', index)}
            keepPlaceholderOnFocus={true}
            withoutInteractiveFormatting
            multiline="false"
          />
          <div className={`panel ${isSelected ? '' : 'panel-hidden'}`}>
            <RichText
              tagName="p"
              className="accordion-text"
              placeholder={__('Enter text', 'planet4-blocks-backend')}
              value={tab.text}
              onChange={updateTabAttribute('text', index)}
              keepPlaceholderOnFocus={true}
            />
            <div className="btn btn-secondary accordion-btn">
              <RichText
                tagName="div"
                placeholder={__('Optional button', 'planet4-blocks-backend')}
                value={tab.button_text}
                onChange={updateTabAttribute('button_text', index)}
                keepPlaceholderOnFocus={true}
                withoutInteractiveFormatting
                multiline="false"
              />
            </div>
          </div>
        </div>
      ))}
    </div>
  );
}

// Renders the sidebar settings
const renderEdit = ({ tabs }, setAttributes, updateTabAttribute) => {
  const [buttonUrl, setButtonUrl] = useState({});

  const addTab = () => setAttributes({ tabs: [...tabs, {}] });

  const removeTab = () => setAttributes({ tabs: tabs.slice(0, tabs.length - 1) });

  const debounceButtonUrl = debounce((index, url) => {
    updateTabAttribute('button_url', index)(url);
  }, 300);

  return (
    <InspectorControls>
      <PanelBody title={__('Setting', 'planet4-blocks-backend')}>
        {tabs.map((tab, index) => (
          <div key={`tab-${index}`}>
            <p>{__('Item', 'planet4-blocks-backend')} {index + 1}</p>
            <URLInput
              label={__('Button link', 'planet4-blocks-backend')}
              value={buttonUrl[index] ? buttonUrl[index].value : tab.button_url}
              onChange={url => {
                setButtonUrl({ [index]: url, ...buttonUrl });
                debounceButtonUrl(index, url);
              }}
            />
            <CheckboxControl
              label={__('Open in a new tab', 'planet4-blocks-backend')}
              value={tab.button_new_tab}
              checked={tab.button_new_tab}
              onChange={updateTabAttribute('button_new_tab', index)}
            />
          </div>
        ))}
        <Button
          isPrimary
          onClick={addTab}
          style={{ marginRight: 10 }}
        >
          {__('Add item', 'planet4-blocks-backend')}
        </Button>
        <Button
          isSecondary
          disabled={tabs.length <= 1}
          onClick={removeTab}
        >
          {__('Remove item', 'planet4-blocks-backend')}
        </Button>
      </PanelBody>
    </InspectorControls>
  );
}

export const AccordionEditor = ({ attributes, isSelected, setAttributes }) => {
  // If there are no tabs yet, we add an empty one as placeholder
  const { title, description, className } = attributes;
  const tabs = attributes.tabs.length > 0 ? attributes.tabs : [{}];

  const updateTabAttribute = (attributeName, index) => value => {
    const newTabs = [...tabs];
    newTabs[index][attributeName] = value;
    setAttributes({
      tabs: newTabs
    });
  };

  return (
    <Fragment>
      {isSelected && renderEdit({ tabs }, setAttributes, updateTabAttribute)}
      {renderView({ tabs, title, description, className }, setAttributes, isSelected, updateTabAttribute)}
    </Fragment>
  );
};

