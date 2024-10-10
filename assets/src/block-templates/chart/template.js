const {__} = wp.i18n;

const template = ({
  title,
  description,
}) => ([
  ['core/group', {
    className: 'block',
    layout: {
      type: 'flex',
      flexWrap: 'nowrap',
      justifyContent: 'left',
    },
  }, [
    ['planet4-blocks/chart', {
      chartType: 'pie',
    }],
    ['core/group', {
      className: 'container',
    }, [
      ['core/heading', {
        level: 2,
        placeholder: __('Enter title', 'planet4-blocks-backend'),
        content: title,
        style: {
          spacing: {
            margin: {
              bottom: '24px',
            },
          },
        },
        textAlign: 'left',
      },
      ],
      ['core/paragraph', {
        placeholder: __('Enter description', 'planet4-blocks-backend'),
        content: description,
      },
      ],
    ],
    ]],
  ]]);

export default template;

