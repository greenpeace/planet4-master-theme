import {ENFormFrontend} from './ENFormFrontend';

document.querySelectorAll('[data-render=\'planet4-blocks/enform\']').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    wp.element.render(<ENFormFrontend {...attributes.attributes} />, blockNode);
  }
);
