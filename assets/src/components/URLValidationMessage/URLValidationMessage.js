import {Component} from '@wordpress/element';

export class URLValidationMessage extends Component {
  constructor(props) {
    super(props);
  }

  render() {

    const {__} = wp.i18n;
    const { url } = this.props

    let errorMessage = "";


    const isURLValid = () => {
      if ( !url ) {
        return true;
      }

      try {
        const urlObject = new URL(url);
        if ( urlObject.protocol !== 'https:') {
          errorMessage = 'The URL must start with "HTTPS://"';
          return false;
        }
        return true;
      } catch(e) {
        errorMessage = 'The URL is invalid';
        return false;
      }
    }

    if (isURLValid()) {
        return null;
    }

    return (
        <span className='input_error'>{ __(errorMessage, 'p4ge') }</span>
    )
  }
}
