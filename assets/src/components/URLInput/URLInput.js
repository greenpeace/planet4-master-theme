import {Component} from '@wordpress/element';
import {TextControl} from '@wordpress/components';
import {URLValidationMessage} from "../../components/URLValidationMessage/URLValidationMessage";

export class URLInput extends Component {
  constructor(props) {
    super(props);
  }

  render() {

    const {__} = wp.i18n;
    const { label, placeholder, value, onChange, disabled, help } = this.props

    return (
      <div>
          <TextControl
            label={label}
            placeholder={placeholder}
            value={value}
            onChange={onChange}
            disabled={disabled}
            help={help}
          />
          <URLValidationMessage
            url={value}
          />
      </div>
    )
  }
}
