import {Component, Fragment} from '@wordpress/element';

const { RichText } = wp.blockEditor;
const { __ } = wp.i18n;

import { CookiesFrontend } from './CookiesFrontend';

export class CookiesEditor extends Component {
  constructor(props) {
    super(props);
    this.toAttribute = this.toAttribute.bind(this);
  }

  toAttribute(attributeName) {
    const { setAttributes } = this.props;
    return value => setAttributes({ [attributeName]: value });
  }

  render() {
    const { attributes } = this.props;
    return (
      <CookiesFrontend
        { ...attributes }
        toAttribute={ this.toAttribute }
        isSelected={ this.props.isSelected }
        />
    );
  };
}
