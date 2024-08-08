import {URLInput} from '../../block-editor/URLInput/URLInput';
import {useScript} from '../../hooks/useScript/useScript';
import {useStyleSheet} from '../../hooks/useStylesheet/useStylesheet';
import {Timeline} from './Timeline';
import {languages} from './TimelineLanguages';
import {URLDescriptionHelp} from './URLDescriptionHelp';
import {isLodash} from '../../functions/isLodash';

const {InspectorControls, RichText} = wp.blockEditor;
const {PanelBody, SelectControl, CheckboxControl, Tooltip} = wp.components;
const {debounce} = wp.compose;
const {useCallback, useState} = wp.element;
const {__} = wp.i18n;

const TIMELINE_JS_VERSION = '3.8.12';

const positions = [
  {label: 'Bottom', value: 'bottom'},
  {label: 'Top', value: 'top'},
];

const loadAssets = () => {
  const revertLodash = function() {
    // Address conflicts between the underscore and lodash libraries.
    if (isLodash()) {
      // eslint-disable-next-line no-undef
      _.noConflict();
    }
  };
  // eslint-disable-next-line no-unused-vars
  const [scriptLoaded, scriptError] = useScript(
    `https://cdn.knightlab.com/libs/timeline3/${TIMELINE_JS_VERSION}/js/timeline-min.js`,
    revertLodash
  );

  // eslint-disable-next-line no-unused-vars
  const [stylesLoaded, stylesError] = useStyleSheet(
    `https://cdn.knightlab.com/libs/timeline3/${TIMELINE_JS_VERSION}/css/timeline.css`
  );

  return [scriptLoaded, stylesLoaded];
};

const renderEdit = (
  {language, timenav_position, start_at_end},
  toAttribute,
  sheetURL,
  setSheetURL,
  debounceSheetURLUpdate
) => {
  return (
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

        <SelectControl
          label={__('Language', 'planet4-blocks-backend')}
          value={language}
          options={languages}
          onChange={toAttribute('language')}
        />

        <SelectControl
          label={__('Timeline navigation position', 'planet4-blocks-backend')}
          value={timenav_position}
          options={positions}
          onChange={toAttribute('timenav_position')}
        />

        <CheckboxControl
          label={__('Start at end', 'planet4-blocks-backend')}
          help={__('Begin at the end of the timeline', 'planet4-blocks-backend')}
          value={start_at_end}
          checked={start_at_end}
          onChange={toAttribute('start_at_end')}
        />

      </PanelBody>
    </InspectorControls>
  );
};

const renderView = (attributes, toAttribute, scriptLoaded, stylesLoaded) => {
  return (
    <section className={`block timeline-block ${attributes.className ?? ''}`}>
      <Tooltip text={__('Edit text', 'planet4-blocks-backend')}>
        <header className="articles-title-container">
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
      {!attributes.google_sheets_url &&
        <div className="block-edit-mode-warning components-notice is-warning">
          { __('Please include a Sheet URL.', 'planet4-blocks-backend') }
        </div>
      }
      {attributes.google_sheets_url && scriptLoaded && stylesLoaded &&
        <Timeline {...attributes} />
      }
    </section>
  );
};

export const TimelineEditor = ({isSelected, attributes, setAttributes}) => {
  const toAttribute = attributeName => value => setAttributes({[attributeName]: value});
  const [scriptLoaded, stylesLoaded] = loadAssets();
  // Using a state to prevent the input losing the cursor position, a React issue reported multiple times
  const [sheetURL, setSheetURL] = useState(attributes.google_sheets_url);
  const debounceSheetURLUpdate = useCallback(debounce(toAttribute('google_sheets_url'), 300), []);

  return (
    <>
      {renderView(attributes, toAttribute, scriptLoaded, stylesLoaded)}
      {isSelected &&
        renderEdit(attributes, toAttribute, sheetURL, setSheetURL, debounceSheetURLUpdate)
      }
    </>
  );
};
