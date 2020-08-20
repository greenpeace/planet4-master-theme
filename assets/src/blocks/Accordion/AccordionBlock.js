/* eslint-disable no-unused-vars */
import { Component } from '@wordpress/element';
import { AccordionEditor } from './AccordionEditor';
import { frontendRendered } from '../frontendRendered';

const BLOCK_NAME = 'planet4-blocks/accordion';

export class AccordionBlock extends Component {
  constructor (props) {
    super(props);
    const { registerBlockType } = wp.blocks;
    const { __ } = wp.i18n;

    const attributes = {
      accordion_title: {
        type: 'string',
        default: '',
        selector: '.page-section-header'
      },
      accordion_description: {
        type: 'string',
        default: '',
        selector: '.page-section-description'
      },
      accordion_rows: {
        type: 'array',
        default: [],
        selector: '.accordion-content'
      },
      accordion_id: {
        type: 'integer',
        default: ''
      },
      accordion_headline: {
        type: 'string',
        default: '',
        selector: '.accordion-headline'
      },
      accordion_text: {
        type: 'string',
        default: '',
        selector: '.accordion-text'
      },
      accordion_btn_text: {
        type: 'string',
        default: '',
        selector: '.btn-txt'
      },
      accordion_btn_url: {
        type: 'string',
        default: '',
        selector: '.btn-accordion'
      },
      button_link_new_tab: {
        type: 'boolean',
        default: false
      }
    };

    registerBlockType(BLOCK_NAME, {
      title: __('Accordion', 'planet4-blocks-backend'),
      icon: 'menu',
      category: 'planet4-blocks-beta',
      keywords: [
        __(BLOCK_NAME),
        __('faq'),
        __('collapsible')
      ],
      attributes,
      deprecated: [
        {
          attributes,
          save () {
            return null;
          }
        }
      ],
      edit: ({ isSelected, attributes, setAttributes }) => {
        return <AccordionEditor
          attributes={attributes}
          setAttributes={setAttributes}
          isSelected={ isSelected }
        />;
      },
      save: frontendRendered(BLOCK_NAME)
    });
  }
}
