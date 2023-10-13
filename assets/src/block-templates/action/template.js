import gravityFormWithText from '../templates/gravity-form-with-text';

const {__} = wp.i18n;

const template = () => ([
  ['core/group', {className: 'block'}, [
    gravityFormWithText('white'),
    ['core/group', {
      backgroundColor: 'beige-100',
      align: 'full',
      style: {
        spacing: {
          padding: {
            top: '80px',
            bottom: '80px',
          },
        },
      },
    }, [
      ['core/group', {className: 'container'}, [
        ['planet4-block-templates/side-image-with-text-and-cta', {
          title: __('The problem', 'planet4-blocks'),
          mediaPosition: 'right',
        }],
      ]],
    ]],
    ['planet4-blocks/covers', {
      cover_type: 'take-action',
    }],
    ['core/separator', {backgroundColor: 'grey-20'}],
    ['planet4-block-templates/quick-links', {
      title: __('Explore by topics', 'planet4-blocks'),
      backgroundColor: 'white',
    }],
  ]],
]);

export default template;
