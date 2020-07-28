import { SplittwocolumnsEditor, migrateAttributes } from './SplittwocolumnsEditor';
import { frontendRendered } from '../frontendRendered';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

export const BLOCK_NAME = 'planet4-blocks/split-two-columns';
export const VERSION = 2;

export class SplittwocolumnsBlock {
  constructor() {
    const attributes = {
      version: { type: 'number', default: VERSION },
      select_issue: { type: 'number', default: 0 },
      title: { type: 'string', default: '' },
      issue_description: { type: 'string', default: '' },
      issue_link_text: { type: 'string', default: '' },
      issue_link_path: { type: 'string', default: '' },
      issue_image_id: { type: 'number', default: 0 },
      issue_image_src: { type: 'string', default: '' },
      issue_image_srcset: { type: 'string', default: '' },
      issue_image_title: { type: 'string', default: '' },
      focus_issue_image: { type: 'string', default: '50% 50%' },
      select_tag: { type: 'number', default: 0 },
      tag_name: { type: 'string', default: '' },
      tag_description: { type: 'string', default: '' },
      tag_link: { type: 'string', default: '' },
      button_text: { type: 'string', default: '' },
      button_link: { type: 'string', default: '' },
      tag_image_id: { type: 'number', default: 0 },
      tag_image_src: { type: 'string', default: '' },
      tag_image_srcset: { type: 'string', default: '' },
      tag_image_title: { type: 'string', default: '' },
      focus_tag_image: { type: 'string', default: '50% 50%' },
    }

    registerBlockType( BLOCK_NAME, {
      title: __('Split Two Columns', 'planet4-blocks-backend'),
      icon: 'editor-table',
      category: 'planet4-blocks',
      attributes,
      edit: SplittwocolumnsEditor,
      save: frontendRendered( BLOCK_NAME ),
      deprecated: [
        {
          attributes: {
            issue_image: { type: 'number', default: 0 },
            tag_image: { type: 'number', default: 0 },
            ...attributes
          },
          isEligible(attributes) { 
            return attributes.issue_image || attributes.tag_image
          },
          migrate: migrateAttributes,
          save() { return null },
        }
      ]
    });
  }
}
