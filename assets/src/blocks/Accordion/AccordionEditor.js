import {URLInput} from '../../block-editor/URLInput/URLInput';

const {InspectorControls, RichText} = wp.blockEditor;
const {PanelBody, CheckboxControl, Button} = wp.components;
const {debounce} = wp.compose;
const {useState} = wp.element;
const {__} = wp.i18n;

// Renders the editor view
const renderView = ({title, description, tabs, className}, setAttributes, isSelected, updateTabAttribute) => {
  const toAttribute = attributeName => value => setAttributes({
    [attributeName]: value,
  });

  const addButton = index => {
    const newTabs = [...tabs];
    newTabs[index].button = {};
    setAttributes({
      tabs: newTabs,
    });
  };

  const removeButton = index => {
    const newTabs = [...tabs];
    delete newTabs[index].button;
    setAttributes({
      tabs: newTabs,
    });
  };

  return (
    <div className={`block accordion-block ${className ?? ''}`}>
      <header>
        <RichText
          tagName="h2"
          className="page-section-header"
          placeholder={__('Enter title', 'planet4-blocks-backend')}
          value={title}
          onChange={toAttribute('title')}
          withoutInteractiveFormatting
          allowedFormats={[]}
        />
      </header>
      <RichText
        tagName="p"
        className="page-section-description"
        placeholder={__('Enter description', 'planet4-blocks-backend')}
        value={description}
        onChange={toAttribute('description')}
        withoutInteractiveFormatting
        allowedFormats={['core/bold', 'core/italic']}
      />
      {tabs.map((tab, index) => (
        <div key={`accordion-content-${index}`} className="accordion-content">
          <RichText
            tagName="h4"
            className={`accordion-headline ${isSelected ? 'open' : ''}`}
            placeholder={__('Enter headline', 'planet4-blocks-backend')}
            value={tab.headline}
            onChange={updateTabAttribute('headline', index)}
            withoutInteractiveFormatting
            allowedFormats={[]}
          />
          <div className={`panel ${isSelected ? '' : 'panel-hidden'}`}>
            <RichText
              tagName="p"
              className="accordion-text"
              placeholder={__('Enter text', 'planet4-blocks-backend')}
              value={tab.text}
              onChange={updateTabAttribute('text', index)}
              allowedFormats={['core/bold', 'core/italic', 'core/link']}
            />
            {tab.button ?
              <div className="button-container">
                <RichText
                  tagName="div"
                  className="btn btn-secondary accordion-btn"
                  placeholder={__('Button text', 'planet4-blocks-backend')}
                  value={tab.button.button_text}
                  onChange={updateTabAttribute('button_text', index)}
                  withoutInteractiveFormatting
                  allowedFormats={[]}
                />
                <Button
                  className="remove-btn"
                  onClick={() => removeButton(index)}
                  icon="trash"
                />
              </div> :
              <div onClick={() => addButton(index)} className="add-button" role="presentation">
                <span className="plus">+</span>
                {__('Add button', 'planet4-blocks-backend')}
              </div>
            }
          </div>
        </div>
      ))}
    </div>
  );
};

// Renders the sidebar settings
const renderEdit = ({tabs}, setAttributes, updateTabAttribute) => {
  const [buttonUrl, setButtonUrl] = useState({});

  const addTab = () => setAttributes({tabs: [...tabs, {}]});

  const removeTab = () => setAttributes({tabs: tabs.slice(0, tabs.length - 1)});

  const debounceButtonUrl = debounce((index, url) => {
    updateTabAttribute('button_url', index)(url);
  }, 300);

  return (
    <InspectorControls>
      <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
        {tabs.map((tab, index) => {
          const {button} = tab;
          if (!button) {
            return; // eslint-disable-line array-callback-return
          }

          return (
            <div key={`tab-${index}`}>
              <p>{__('Item', 'planet4-blocks-backend')} {index + 1}</p>
              <URLInput
                label={__('Button link', 'planet4-blocks-backend')}
                value={buttonUrl[index] ? buttonUrl[index].value : button.button_url}
                onChange={url => {
                  setButtonUrl({[index]: url, ...buttonUrl});
                  debounceButtonUrl(index, url);
                }}
              />
              <CheckboxControl
                label={__('Open in a new tab', 'planet4-blocks-backend')}
                value={button.button_new_tab}
                checked={button.button_new_tab}
                onChange={updateTabAttribute('button_new_tab', index)}
              />
            </div>
          );
        })}
        <Button
          isPrimary
          onClick={addTab}
          style={{marginRight: 10}}
        >
          {__('Add item', 'planet4-blocks-backend')}
        </Button>
        <Button
          variant="secondary"
          disabled={tabs.length <= 1}
          onClick={removeTab}
        >
          {__('Remove item', 'planet4-blocks-backend')}
        </Button>
      </PanelBody>
      <PanelBody title={__('Learn more about this block ', 'planet4-blocks-backend')} initialOpen={false}>
        <p className="components-base-control__help">
          <a target="_blank" href="https://planet4.greenpeace.org/content/blocks/accordion/" rel="noreferrer">
            P4 Handbook Accordion
          </a>
          {' '} &#11015;&#65039;
        </p>
      </PanelBody>
    </InspectorControls>
  );
};

export const AccordionEditor = ({attributes, isSelected, setAttributes, className}) => {
  const {title, description} = attributes;

  // If there are no tabs yet, we add an empty one as placeholder
  const tabs = attributes.tabs.length > 0 ? attributes.tabs : [{}];

  const updateTabAttribute = (attributeName, index) => value => {
    const newTabs = [...tabs];
    if (attributeName.startsWith('button_')) {
      newTabs[index].button[attributeName] = value;
    } else {
      newTabs[index][attributeName] = value;
    }
    setAttributes({
      tabs: newTabs,
    });
  };

  return (
    <>
      {isSelected && renderEdit({tabs}, setAttributes, updateTabAttribute)}
      {renderView({tabs, title, description, className}, setAttributes, isSelected, updateTabAttribute)}
    </>
  );
};

