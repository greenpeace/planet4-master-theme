import { AccordionEditor } from './AccordionEditor';
import { AccordionFrontend } from './AccordionFrontend';
const { registerBlockType, registerBlockStyle } = wp.blocks;
const { __ } = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/accordion';

const attributes = {
  title: {
    type: 'string',
    default: '',
  },
  description: {
    type: 'string',
    default: '',
  },
  tabs: {
    type: 'array',
    default: [],
  },
};

const styles = [
  {
    name: 'dark',
    label: __('Dark', 'planet4-blocks-backend'),
    isDefault: true
  },
  {
    name: 'light',
    label: __('Light', 'planet4-blocks-backend')
  },
];

export const registerAccordionBlock = () => {

  registerBlockType(BLOCK_NAME, {
    title: __('Accordion', 'planet4-blocks-backend'),
    icon: 'menu',
    category: 'planet4-blocks-beta',
    keywords: [
      __('accordion'),
      __('faq'),
      __('collapsible')
    ],
    supports: {
      html: false, // Disable "Edit as HTMl" block option.
    },
    attributes,
    edit: AccordionEditor,
    save: ({ attributes }) => {
      if (!attributes) {
        return null;
      }

      return <AccordionFrontend {...attributes} />;
    },
  });

  // Add our custom styles
  registerBlockStyle(BLOCK_NAME, styles);
}
