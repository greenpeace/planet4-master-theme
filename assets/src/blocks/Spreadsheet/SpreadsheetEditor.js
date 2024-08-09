import ColorPaletteControl from '../../block-editor/ColorPaletteControl/ColorPaletteControl';
import {SpreadsheetFrontend} from './SpreadsheetFrontend';

const {InspectorControls} = wp.blockEditor;
const {TextControl, PanelBody} = wp.components;
const {__} = wp.i18n;
const {debounce} = wp.compose;
const {useState} = wp.element;

const colors = [
  {name: 'blue', color: '#167f82'},
  {name: 'green', color: '#1f4912'},
  {name: 'grey', color: '#45494c'},
  {name: 'gp-green', color: '#198700'},
];

export const SpreadsheetEditor = ({
  attributes,
  setAttributes,
  isSelected,
}) => {
  const [invalidSheetId, setInvalidSheetId] = useState(false);
  const [url, setUrl] = useState(attributes.url);

  const debounceUrl = debounce(newUrl => {
    setAttributes({url: newUrl});
  }, 300);

  const toColorName = code => colors.find(color => color.color === code)?.name || 'grey';

  const toColorCode = name => colors.find(color => color.name === name)?.color || '#ececec';

  const renderEdit = () => (
    <>
      <InspectorControls>
        <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
          <ColorPaletteControl
            label={__('Table Color', 'planet4-blocks-backend')}
            value={toColorCode(attributes.color)}
            onChange={value => setAttributes({color: toColorName(value)})}
            disableCustomColors
            clearable={false}
            options={colors}
          />
          <TextControl
            label={__('Spreadsheet URL', 'planet4-blocks-backend')}
            placeholder={__('Enter Google Spreadsheet URL', 'planet4-blocks-backend')}
            value={url}
            onChange={newUrl => {
              setUrl(newUrl);
              debounceUrl(newUrl);
            }}
          />
          <div className="sidebar-blocks-help">
            <ul>
              <li>
                {/* eslint-disable-next-line no-restricted-syntax, @wordpress/i18n-no-collapsible-whitespace */}
                { __(`From Your Google Spreadsheet Table choose File -> Publish on web.
                No need to choose the output format, any of them will work.
                A pop-up window will show up, click on the Publish button and then OK when the confirmation message is displayed.
                Copy the URL that is highlighted and paste it in this block.`, 'planet4-blocks-backend') }
              </li>
              <li>
                {/* eslint-disable-next-line no-restricted-syntax, @wordpress/i18n-no-collapsible-whitespace */}
                { __(`If you make changes to the sheet after publishing
                  then these changes do not always immediately get reflected,
                  even when "Automatically republish when changes are made" is checked.`, 'planet4-blocks-backend') }
              </li>
              <li>
                {/* eslint-disable-next-line no-restricted-syntax, @wordpress/i18n-no-collapsible-whitespace */}
                { __(`You can force an update by unpublishing and republishing the sheet.
                  This will not change the sheet's public url.`, 'planet4-blocks-backend') }
              </li>
            </ul>
          </div>
        </PanelBody>
      </InspectorControls>
    </>
  );

  const renderView = () => (
    <>
      {!attributes.url ?
        <div className="block-edit-mode-warning components-notice is-warning">
          { __('No URL has been specified. Please edit the block and provide a Spreadsheet URL using the sidebar.', 'planet4-blocks-backend') }
        </div> :
        null
      }

      {attributes.url && invalidSheetId ?
        <div className="block-edit-mode-warning components-notice is-error">
          { __('The Spreadsheet URL appears to be invalid.', 'planet4-blocks-backend') }
        </div> :
        null
      }

      <SpreadsheetFrontend {...attributes} setInvalidSheetId={setInvalidSheetId} />
    </>
  );

  return (
    <>
      {isSelected ? renderEdit() : null}
      {renderView()}
    </>
  );
};
