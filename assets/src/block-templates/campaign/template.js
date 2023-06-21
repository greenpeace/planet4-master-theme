import gravityFormWithText from '../templates/gravity-form-with-text';

const {__} = wp.i18n;

const template = () => ([
  ['core/group', {className: 'block'}, [
    ['planet4-block-templates/page-header', {
      titlePlaceholder: __('Page header title', 'planet4-blocks-backend'),
    }],
    ['core/spacer', {height: '64px'}],
    ['planet4-block-templates/side-image-with-text-and-cta', {
      title: __('The problem', 'planet4-blocks'),
    }],
    ['core/spacer', {height: '32px'}],
    ['planet4-block-templates/side-image-with-text-and-cta', {
      title: __('The solution', 'planet4-blocks'),
      mediaPosition: 'right',
    }],
    ['core/spacer', {height: '32px'}],
    ['planet4-blocks/covers', {
      title: __('How you can help', 'planet4-blocks'),
    }],
    ['core/spacer', {height: '48px'}],
    gravityFormWithText(),
  ]],
]);

export default template;
