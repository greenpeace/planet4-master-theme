import { Component } from '@wordpress/element';

import { CookiesFrontend } from './CookiesFrontend';

export class CookiesEditor extends Component {
  constructor(props) {
    super(props);
    this.toAttribute = this.toAttribute.bind(this);
  }

  toAttribute(attributeName) {
    return value => this.props.setAttributes({ [attributeName]: value });
  }

  render() {
    const { attributes, isSelected } = this.props;
    return (
      <CookiesFrontend
        { ...attributes }
        isEditing
        toAttribute={ this.toAttribute }
        isSelected={ isSelected }
        />
    );
  };
}
