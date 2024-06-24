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
    ['planet4-blocks/articles', {
      article_heading: __('Daily actions', 'planet4-blocks'),
    }],
    ['core/spacer', {height: '32px'}],
    ['planet4-blocks/covers', {
      title: __('Support a cause', 'planet4-blocks'),
    }],
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
        ['planet4-blocks/articles', {
          article_heading: __('Take action with us', 'planet4-blocks'),
        }],
        ['core/spacer', {height: '32px'}],
        ['planet4-block-templates/deep-dive', {
          title: __('Raise awareness in your community', 'planet4-blocks'),
        }],
        ['core/spacer', {height: '32px'}],
        ['planet4-blocks/articles', {
          article_heading: __('Donate', 'planet4-blocks'),
        }],
      ]],
    ]],
  ]],
]);

export default template;
