import ReactDOMServer from 'react-dom/server';
import { Tooltip } from '@wordpress/components';
import { HubspotFormEditor } from './HubspotFormEditor';
import { HubspotFormFrontend } from './HubspotFormFrontend';
const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/hubspot-form';

const getStyleLabel = (label, help) => (
  (help)
    ? <Tooltip text={help}>
        <span>{label}</span>
      </Tooltip>
    : label
);

export const registerHubspotFormBlock = () => {
  return registerBlockType(BLOCK_NAME, {
    title: 'Hubspot Form (beta)',
    icon: 'feedback',
    category: 'planet4-blocks-beta',
    supports: {
      multiple: false,
      html: false,
    },
    attributes: {
      blockTitle: {
        type: 'string',
      },
      blockText: {
        type: 'string',
      },
      blockBackgroundImageId: {
        type: 'integer',
      },
      blockBackgroundImageUrl: {
        type: 'string',
      },
      blockStyle: {
        type: 'string',
        default: 'image-full-width',
      },
      ctaText: {
        type: 'string',
      },
      ctaLink: {
        type: 'string',
      },
      ctaNewTab: {
        type: 'boolean',
        default: false,
      },
      formTitle: {
        type: 'string',
      },
      formText: {
        type: 'string',
      },
      hubspotShortcode: {
        type: 'string',
        default: '',
      },
      hubspotThankyouMessage: {
        type: 'string',
      },
      enableCustomHubspotThankyouMessage: {
        type: 'boolean',
        default: false,
      },
      version: {
        type: 'integer',
        default: 1,
      },
    },
    styles: [
      {
        name: 'image-full-width',
        label: getStyleLabel(
          __('Image full width', 'planet4-blocks-backend'),
          __('https://p4-designsystem.greenpeace.org/05f6e9516/p/213df0-hubspot-forms/b/99e047', 'planet4-blocks-backend'),
        ),
        isDefault: true,
      },
    ],
    edit: HubspotFormEditor,
    save: (props) => {
      /**
       * This parser is added cause the Hubspot plugin takes the shortcode, by a hook,
       * and converts it into a <script>. In consequence, it fails when it is parsed to json through the hydration.
       *
       * This parser is only affected to the hydration.
       *
       * Ideally, we should use innerBlocks but it has various reported conflicts using SSR.
       */
      const attributes = {...props.attributes};
      attributes.hubspotShortcode = props.attributes.hubspotShortcode.replace('[', '').replace(']', '');

      const markup = ReactDOMServer.renderToString(
        <div
          data-hydrate={BLOCK_NAME}
          data-attributes={JSON.stringify(attributes)}
        >
          <HubspotFormFrontend {...props} />
        </div>
      );
      return <wp.element.RawHTML>{ markup }</wp.element.RawHTML>;
    },
  })
}
