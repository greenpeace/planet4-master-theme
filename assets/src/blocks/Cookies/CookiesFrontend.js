import { Component, Fragment } from '@wordpress/element';
import { FrontendRichText } from '../../components/FrontendRichText/FrontendRichText';

const { __ } = wp.i18n;

export class CookiesFrontend extends Component {
  constructor(props) {
		super(props);
		const cookie = this.readCookie('greenpeace');

		this.state = {
			necessaryCookiesChecked: ['1', '2'].includes( cookie ),
			allCookiesChecked: '2' === cookie,
		}

		this.onNecessaryCookiesClick = this.onNecessaryCookiesClick.bind(this);
		this.onAllCookiesClick = this.onAllCookiesClick.bind(this);
    this.setNoTrackCookie = this.setNoTrackCookie.bind(this);
  }

	showCookieNotice() {
		// the .cookie-notice element belongs to the P4 Master Theme
		$('.cookie-notice').css('display', 'flex');
	}

	hideCookieNotice() {
		// the .cookie-notice element belongs to the P4 Master Theme
		$('.cookie-notice').fadeOut('slow');
	}

	onNecessaryCookiesClick() {
		const isChecked = !this.state.necessaryCookiesChecked;
		let { allCookiesChecked } = this.state;

    if (isChecked) {
      this.createCookie('greenpeace', '1', 365);
			this.hideCookieNotice();
    } else {
			allCookiesChecked = false;
      this.createCookie('greenpeace', '0', -1);
      this.showCookieNotice();
		}

		this.setState({
			necessaryCookiesChecked: isChecked,
			 // if Necessary Cookies is not checked,
			 // All Cookies should be unchecked too
			allCookiesChecked,
		}, this.setNoTrackCookie);
	}

	onAllCookiesClick() {
		const isChecked = !this.state.allCookiesChecked;

		if (isChecked) {
      this.createCookie('greenpeace', '2', 365);
			this.hideCookieNotice();
		} else {
			if ( this.state.necessaryCookiesChecked ) {
				this.createCookie('greenpeace', '1', 365);
			} else {
				this.createCookie('greenpeace', '0', -1);
				this.showCookieNotice();
			}
		}

		const cookie = this.readCookie('greenpeace');

		this.setState({
			necessaryCookiesChecked: ['1', '2'].includes( cookie ),
			allCookiesChecked: isChecked,
		}, this.setNoTrackCookie);
	}

  createCookie(name, value, days) {
    let date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = encodeURI(name) + '=' + encodeURI(value) + ';domain=.' + document.domain + ';path=/;' + '; expires=' + date.toGMTString();
  }

  readCookie(name) {
    const nameEQ = name + '=';
    const ca = document.cookie.split(';');
    let c;
    for (let i = 0; i < ca.length; i++) {
      c = ca[i];
      while (c.charAt(0) === ' ') {
        c = c.substring(1, c.length);
      }
      if (c.indexOf(nameEQ) === 0) {
        return c.substring(nameEQ.length, c.length);
      }
    }
    return null;
	}

  setNoTrackCookie() {
    const { necessaryCookiesChecked, allCookiesChecked } = this.state;
    if (!necessaryCookiesChecked && !allCookiesChecked) {
      // If user manually disables all trackings, set a 'no_track' cookie.
      createCookie('no_track', 'true', 20 * 365);
    } else {
      // Remove the 'no_track' cookie, if user accept the cookies consent.
      createCookie('no_track', 'true', -1);
    }
  }

  render() {
		const {
			isSelected,
			title,
			description,
			necessary_cookies_name,
			necessary_cookies_description,
			all_cookies_name,
      all_cookies_description,
      isEditing,
      toAttribute
    } = this.props;

    const { necessaryCookiesChecked, allCookiesChecked } = this.state;

    return (
      <Fragment>
        <section className="block cookies-block">
          {(isEditing || title) &&
            <header>
              <FrontendRichText
                tagName="h2"
                className="page-section-header"
                placeholder={ __('Enter title', 'planet4-blocks-backend') }
                value={ title }
                onChange={ toAttribute && toAttribute('title') }
                keepPlaceholderOnFocus={ true }
                withoutInteractiveFormatting
                characterLimit={ 40 }
                multiline="false"
                editable={ isEditing }
              />
            </header>
          }
          {(isEditing || description) &&
            <FrontendRichText
              tagName="p"
              className="page-section-description"
              placeholder={ __('Enter description', 'planet4-blocks-backend') }
              value={ description }
              onChange={ toAttribute && toAttribute('description') }
              keepPlaceholderOnFocus={ true }
              withoutInteractiveFormatting
              characterLimit={ 300 }
              editable={ isEditing }
            />
          }
          {(isEditing || (necessary_cookies_name && necessary_cookies_description)) &&
            <Fragment>
              <label className="custom-control custom-checkbox"
                style={ isSelected ? { pointerEvents: 'none' } : null }>
                <input
                  type="checkbox"
                  tabIndex={ isSelected ? '-1' : null }
                  name="necessary_cookies"
                  onChange={ this.onNecessaryCookiesClick }
                  checked={ necessaryCookiesChecked }
                  className="p4-custom-control-input"
                />
                <FrontendRichText
                  tagName="span"
                  className="custom-control-description"
                  placeholder={ __('Enter necessary cookies name', 'planet4-blocks-backend') }
                  value={ necessary_cookies_name }
                  onChange={ toAttribute && toAttribute('necessary_cookies_name') }
                  keepPlaceholderOnFocus={ true }
                  withoutInteractiveFormatting
                  characterLimit={ 40 }
                  multiline="false"
                  editable={ isEditing }
                />
              </label>
              <FrontendRichText
                tagName="p"
                className="cookies-checkbox-description"
                placeholder={ __('Enter necessary cookies description', 'planet4-blocks-backend') }
                value={ necessary_cookies_description }
                onChange={ toAttribute && toAttribute('necessary_cookies_description') }
                keepPlaceholderOnFocus={ true }
                withoutInteractiveFormatting
                characterLimit={ 300 }
                editable={ isEditing }
              />
            </Fragment>
          }
          {(isEditing || (all_cookies_name && all_cookies_description)) &&
            <Fragment>
              <label className="custom-control custom-checkbox"
                style={ isSelected ? { pointerEvents: 'none' } : null }>
                <input
                  type="checkbox"
                  tabIndex={ isSelected ? '-1' : null }
                  onChange={ this.onAllCookiesClick }
                  name="all_cookies"
                  checked={ allCookiesChecked }
                  className="p4-custom-control-input"
                />
                <FrontendRichText
                  tagName="span"
                  className="custom-control-description"
                  placeholder={ __('Enter all cookies name', 'planet4-blocks-backend') }
                  value={ all_cookies_name }
                  onChange={ toAttribute && toAttribute('all_cookies_name') }
                  keepPlaceholderOnFocus={ true }
                  withoutInteractiveFormatting
                  characterLimit={ 40 }
                  multiline="false"
                  editable={ isEditing }
                />
              </label>
              <FrontendRichText
                tagName="p"
                className="cookies-checkbox-description"
                placeholder={ __('Enter all cookies description', 'planet4-blocks-backend') }
                value={ all_cookies_description }
                onChange={ toAttribute && toAttribute('all_cookies_description') }
                keepPlaceholderOnFocus={ true }
                withoutInteractiveFormatting
                characterLimit={ 300 }
                editable={ isEditing }
              />
            </Fragment>
          }
          </section>
      </Fragment>
    );
  }
}
