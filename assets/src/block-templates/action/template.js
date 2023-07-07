import gravityFormWithText from '../templates/gravity-form-with-text';

const {__} = wp.i18n;

const isNewIdentity = window.p4ge_vars.planet4_options.new_identity_styles ?? false;
const backgroundColor = isNewIdentity ? 'beige-100' : 'grey-05';

const template = () => ([
  ['core/group', {className: 'block'}, [
    gravityFormWithText('white'),
    ['core/group', {
      backgroundColor,
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
