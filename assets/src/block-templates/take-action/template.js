import {getPostListBlockTemplate, POSTS_LIST_BLOCK_ATTRIBUTES} from '../../blocks/PostsList';
import {getActionsListBlockTemplate, ACTIONS_LIST_BLOCK_ATTRIBUTES} from '../../blocks/ActionsList';

const {__} = wp.i18n;

const template = ({
  backgroundColor = 'beige-100',
}) => ([
  ['core/group', {className: 'block'}, [
    ['planet4-block-templates/page-header', {
      titlePlaceholder: __('Page header title', 'planet4-blocks'),
      mediaPosition: 'right',
    }],
    ['core/spacer', {height: '64px'}],
    ['core/query',
      POSTS_LIST_BLOCK_ATTRIBUTES,
      getPostListBlockTemplate(__('Daily actions', 'planet4-blocks')),
    ],
    ['core/spacer', {height: '32px'}],
    ['core/query',
      ACTIONS_LIST_BLOCK_ATTRIBUTES,
      getActionsListBlockTemplate(__('Support a cause', 'planet4-blocks')),
    ],
    ['core/spacer', {height: '48px'}],
    ['core/group', {
      align: 'full',
      backgroundColor,
      style: {
        spacing: {
          padding: {
            top: '64px',
            bottom: '64px',
          },
        },
      },
    }, [
      ['core/group', {className: 'container'}, [
        ['core/query',
          POSTS_LIST_BLOCK_ATTRIBUTES,
          getPostListBlockTemplate(__('Take action with us', 'planet4-blocks')),
        ],
        ['core/spacer', {height: '32px'}],
        ['planet4-block-templates/deep-dive', {
          title: __('Raise awareness in your community', 'planet4-blocks'),
        }],
        ['core/spacer', {height: '32px'}],
        ['core/query',
          POSTS_LIST_BLOCK_ATTRIBUTES,
          getPostListBlockTemplate(__('Donate', 'planet4-blocks')),
        ],
      ]],
    ]],
  ]],
]);

export default template;
