import {URLInput} from '../../block-editor/URLInput/URLInput';
import {NewTimelineFrontend} from './NewTimelineFrontend';
import {URLDescriptionHelp} from './URLDescriptionHelp';

const {InspectorControls, RichText} = wp.blockEditor;
const {PanelBody, Tooltip} = wp.components;
const {debounce} = wp.compose;
const {useCallback, useState} = wp.element;
const {__} = wp.i18n;

const renderEdit = (
  sheetURL,
  setSheetURL,
  debounceSheetURLUpdate
) => (
  <InspectorControls>
    <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
      <URLInput
        label={__('Google Sheets URL', 'planet4-blocks-backend')}
        placeholder={__('Enter URL', 'planet4-blocks-backend')}
        value={sheetURL}
        onChange={value => {
          setSheetURL(value);
          debounceSheetURLUpdate(value);
        }}
      />

      <URLDescriptionHelp />

    </PanelBody>
    <PanelBody title={__('Learn more about this block', 'planet4-blocks-backend')} initialOpen={false}>
      <p className="components-base-control__help">
        <a target="_blank" href="https://planet4.greenpeace.org/content/blocks/timeline/" rel="noreferrer">
          P4 Handbook Timeline
        </a>
        {' '} &#8987;
      </p>
    </PanelBody>
  </InspectorControls>
);

const renderView = (attributes, toAttribute) => (
  <section className={`block timeline-block new-timeline-block ${attributes.className ?? ''}`}>
    <Tooltip text={__('Edit text', 'planet4-blocks-backend')}>
      <header>
        <RichText
          tagName="h2"
          className="page-section-header"
          placeholder={__('Enter title', 'planet4-blocks-backend')}
          value={attributes.timeline_title}
          onChange={toAttribute('timeline_title')}
          withoutInteractiveFormatting
          maxLength={40}
          allowedFormats={[]}
        />
      </header>
    </Tooltip>
    <RichText
      tagName="p"
      className="page-section-description"
      placeholder={__('Enter description', 'planet4-blocks-backend')}
      value={attributes.description}
      onChange={toAttribute('description')}
      withoutInteractiveFormatting
      maxLength={200}
      allowedFormats={['core/bold', 'core/italic']}
    />
    {!attributes.google_sheets_url ?
      <div className="block-edit-mode-warning components-notice is-warning">
        { __('Please include a Sheet URL.', 'planet4-blocks-backend') }
      </div> :
      <NewTimelineFrontend attributes={{isEditing: true, ...attributes}} />
    }
  </section>
);

export const NewTimelineEditor = ({isSelected, attributes, setAttributes}) => {
  const toAttribute = attributeName => value => setAttributes({[attributeName]: value});
  // Using a state to prevent the input losing the cursor position, a React issue reported multiple times
  const [sheetURL, setSheetURL] = useState(attributes.google_sheets_url);
  const debounceSheetURLUpdate = useCallback(debounce(toAttribute('google_sheets_url'), 300), []);

  return (
    <>
      {renderView(attributes, toAttribute)}
      {isSelected &&
        renderEdit(sheetURL, setSheetURL, debounceSheetURLUpdate)
      }
    </>
  );
};
