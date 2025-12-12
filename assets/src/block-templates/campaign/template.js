import {getActionsListBlockTemplate, ACTIONS_LIST_BLOCK_ATTRIBUTES} from '../../blocks/ActionsList';
import gravityFormWithText from '../templates/gravity-form-with-text';

const {__} = wp.i18n;

const template = () => ([
  ['core/group', {className: 'block'}, [
    ['planet4-block-templates/page-header', {
      titlePlaceholder: __('Page header title', 'planet4-blocks-backend'),
    }],
    ['core/spacer', {height: '64px'}],
    ['planet4-block-templates/side-image-with-text-and-cta', {
      title: __('The problem', 'planet4-master-theme'),
    }],
    ['core/spacer', {height: '32px'}],
    ['planet4-block-templates/side-image-with-text-and-cta', {
      title: __('The solution', 'planet4-master-theme'),
      mediaPosition: 'right',
    }],
    ['core/spacer', {height: '32px'}],
    ['core/query',
      ACTIONS_LIST_BLOCK_ATTRIBUTES,
      getActionsListBlockTemplate(__('How you can help', 'planet4-master-theme')),
    ],
    ['core/spacer', {height: '48px'}],
    gravityFormWithText(),
  ]],
]);

export default template;
