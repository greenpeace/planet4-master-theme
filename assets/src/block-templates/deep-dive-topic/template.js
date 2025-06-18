import {getPostListBlockTemplate, POSTS_LIST_BLOCK_ATTRIBUTES} from '../../blocks/PostsList';
import {getActionsListBlockTemplate, ACTIONS_LIST_BLOCK_ATTRIBUTES} from '../../blocks/ActionsList';

const {__} = wp.i18n;

const template = () => ([
  ['core/group', {}, [
    ['planet4-block-templates/page-header', {
      titlePlaceholder: __('Page header title', 'planet4-blocks'),
      mediaPosition: 'right',
    }],
    ['core/spacer', {height: '64px'}],
    ['planet4-block-templates/side-image-with-text-and-cta', {
      title: __('The problem', 'planet4-blocks'),
    }],
    ['planet4-block-templates/side-image-with-text-and-cta', {
      title: __('What can be done', 'planet4-blocks'),
      mediaPosition: 'right',
    }],
    ['core/query',
      ACTIONS_LIST_BLOCK_ATTRIBUTES,
      getActionsListBlockTemplate(__('How you can help', 'planet4-blocks')),
    ],
    ['core/query',
      POSTS_LIST_BLOCK_ATTRIBUTES,
      getPostListBlockTemplate(__('Latest news & stories', 'planet4-blocks')),
    ],
    ['planet4-block-templates/deep-dive', {
      title: __('Keep learning about', 'planet4-blocks'),
    }],
    ['planet4-block-templates/quick-links', {
      title: __('Explore other topics', 'planet4-blocks'),
      backgroundColor: 'white',
    }],
  ]],
]);

export default template;
