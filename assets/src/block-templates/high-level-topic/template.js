import gravityFormWithText from '../templates/gravity-form-with-text';
import {getPostListBlockTemplate, POSTS_LIST_BLOCK_ATTRIBUTES} from '../../blocks/PostsList';
import {getActionsListBlockTemplate, ACTIONS_LIST_BLOCK_ATTRIBUTES} from '../../blocks/ActionsList';

const {__} = wp.i18n;

const template = () => ([
  ['core/group', {className: 'block'}, [
    ['planet4-block-templates/page-header', {
      titlePlaceholder: __('Page header title', 'planet4-blocks-backend'),
      mediaPosition: 'right',
    }],
    ['core/spacer', {height: '64px'}],
    ['planet4-block-templates/reality-check'],
    ['planet4-block-templates/side-image-with-text-and-cta', {
      title: __('The problem', 'planet4-blocks'),
      alignFull: true,
    }],
    ['planet4-block-templates/deep-dive', {
      title: __('Better understand the issues [deep dive topics]', 'planet4-blocks'),
      backgroundColor: 'white',
    }],
    ['planet4-block-templates/side-image-with-text-and-cta', {
      title: __('What we do', 'planet4-blocks'),
      mediaPosition: 'right',
      alignFull: true,
    }],
    ['planet4-block-templates/highlighted-cta', {
      titlePlaceholder: __('Featured action title', 'planet4-blocks'),
    }],
    ['core/query',
      ACTIONS_LIST_BLOCK_ATTRIBUTES,
      getActionsListBlockTemplate(__('How you can help', 'planet4-blocks')),
    ],
    ['core/query',
      POSTS_LIST_BLOCK_ATTRIBUTES,
      getPostListBlockTemplate(__('Latest news & stories', 'planet4-blocks')),
    ],
    ['core/query',
      ACTIONS_LIST_BLOCK_ATTRIBUTES,
      getActionsListBlockTemplate(__('Latest investigations', 'planet4-blocks')),
    ],
    gravityFormWithText(),
    ['planet4-block-templates/quick-links', {
      title: __('Explore by topics', 'planet4-blocks'),
      backgroundColor: 'white',
    }],
  ]],
]);

export default template;
