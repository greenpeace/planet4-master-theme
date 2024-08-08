const {registerBlockType} = wp.blocks;

import edit from './edit';

export const registerActionButtonTextBlock = () => registerBlockType(
  'planet4-blocks/action-button-text',
  {
    title: 'Action Button Text',
    category: 'planet4-blocks',
    description: 'Displays the custom button text for an Action, or the default text if undefined',
    usesContext: ['postId'],
    edit,
  });
