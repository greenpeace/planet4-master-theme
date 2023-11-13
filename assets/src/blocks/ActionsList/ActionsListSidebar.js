import {InspectorControls} from '@wordpress/block-editor';
import {TextControl, PanelBody} from '@wordpress/components';
import {BLOCK_NAME} from './ActionsList';

const {addFilter} = wp.hooks;
const {__} = wp.i18n;

// Customize the ActionsList sidebar options.
export const registerActionsListSidebar = () => {
  const withActionsListOptions = BlockEdit => props => {
    const {attributes, name, setAttributes} = props;
    const isActionsList = name === 'core/query' && attributes.namespace === BLOCK_NAME;

    return isActionsList ? (
      <>
        <InspectorControls>
          <PanelBody title={__('Planet 4 Options')}>
            <TextControl
              label={__('Actions count', 'planet4-blocks-backend')}
              help={__('Number of Actions to display', 'planet4-blocks-backend')}
              value={attributes.query.perPage || 3}
              type="number"
              min={1}
              onChange={value => setAttributes({
                query: {
                  ...attributes.query,
                  perPage: value,
                }}
              )}
            />
          </PanelBody>
        </InspectorControls>
        <BlockEdit {...props} />
      </>
    ) : (
      <BlockEdit {...props} />
    );
  };

  addFilter('editor.BlockEdit', 'core/query', withActionsListOptions);
};
