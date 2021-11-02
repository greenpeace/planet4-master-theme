import { ShareButtons } from '../../components/ShareButtons/ShareButtons';
import { FormGenerator } from './FormGenerator';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { unescape } from '../../functions/unescape';

import { inputId } from './inputId';

const { __ } = wp.i18n;

export const ENFormFrontend = (attributes) => {
  const {
    en_page_id,
    en_form_id,
    en_form_style,
    en_form_fields,
    enform_goal,
    content_title,
    content_title_size,
    content_description,
    thankyou_url,
    background,
    background_image_src,
    background_image_srcset,
    background_image_sizes,
    background_image_focus,
    campaign_logo,
    className,
  } = attributes;

  const section_style = ((style) => {
    switch (style) {
      case 'side-style':
        return 'block-header block-wide';
      case 'full-width-bg':
        return 'block-footer block-wide';
      default:
        return '';
    }
  })(en_form_style);

  // todo: get campaign data
  // todo: get error message
  const campaign_data = { logo_path: '', template: '' };

  const style_has_image = en_form_style === 'full-width-bg' || en_form_style === 'side-style';
  const is_side_style = en_form_style === 'side-style';

  console.log(attributes, en_form_fields);
  let fields = en_form_fields;
  if (fields.length <= 0) {
    const form_post = useSelect((select) => {
      return en_form_id
        ? select('core').getEntityRecord('postType', 'p4en_form', en_form_id)
        : [];
    });
    fields = form_post?.p4enform_fields ?? [];
  }
  console.log('fields', fields);

  const HeadingTag = content_title_size;

  const [activeTplId, setActiveTplId] = useState('signup');
  const [errors, setErrors] = useState({});
  const [error_msg, setErrorMsg] = useState(null);
  const [form_data, setFormData] = useState(
    fields.reduce((acc, f) => { return {...acc, [inputId(f)['name']]: null} }, {})
  );

  const onInputChange = (field, e) => {
    setErrors(errs => {
      return {...errs, [field.id]: null}
    });

    const target = e.target;
    const value = target.type === 'checkbox' ? target.checked : target.value;
    const name = target.name;

    console.log('form', name, value);
    setFormData({...form_data, [name]: value});
  }

  const onBlur = (field, e) => {
    validateField(field, form_data, setErrors);
  }

  const onFormSubmit = (e) => {
    e.preventDefault();

    setErrorMsg(null);
    if (!validateForm(form_data, fields, setErrors)) {
      console.error('Validation error.', errors);
      return;
    }

    const url = `https://e-activist.com/ens/service/page/${en_page_id}/process`;
    submitENForm({form_data, fields, url, enform_goal, thankyou_url, setErrorMsg, setActiveTplId});
  }

  return (
    <section
      className={`block enform-wrap enform-${en_form_style} ${section_style} ${className ?? ''}`}
      style={{position: 'inherit'}}
    >
      {style_has_image && background_image_src &&
        <picture>
          <img src={background_image_src || ''}
            style={{objectPosition: background_image_focus || {}}}
            border="0"
            srcSet={background_image_srcset || ''}
            sizes={background_image_sizes || ''}
            className={ background > 0 ? `wp-image-${background}` : '' }
          />
        </picture>
      }

      <div className="caption-overlay"></div>

      <div className="container">
        <div className="row">
          <div className="col-md-12">

            {is_side_style &&
              <div className="form-caption">
                {campaign_logo &&
                  <img src={ campaign_data.logo_path }
                      alt={ campaign_data.template }
                      className="campaign-logo" />
                }
                <HeadingTag>
                  {content_title ? unescape(content_title) : ''}
                </HeadingTag>
                <p dangerouslySetInnerHTML={{ __html: content_description }} />
              </div>
            }

            {activeTplId === 'signup' &&
              <Signup {...{attributes, fields, onInputChange, onBlur, onFormSubmit, error_msg, errors}} />
            }
            {activeTplId === 'thankyou' &&
              <ThankYou {...{attributes, error_msg}} />
            }
          </div>
        </div>
      </div>
    </section>
  )
}

const Signup = ({attributes, fields, onInputChange, onBlur, onFormSubmit, error_msg, errors}) => {
  const {
    en_form_style,
    title,
    description,
    text_below_button,
    button_text,
  } = attributes;

  const is_side_style = en_form_style === 'side-style';

  return (
    <div className="enform" id="enform">
      <div id="enform-content">

        <div className="title-and-description">
          {title &&
            <h2>{title ? title : ''}</h2>
          }
          {is_side_style &&
            <div className="enform-extra-header-placeholder"></div>
          }
          <div className="form-description"
            dangerouslySetInnerHTML={{ __html: description }} />
        </div>

        <div className="form-container">
          <form
            id="p4en_form"
            name="p4en_form"
            method="post"
            noValidate
            onSubmit={ onFormSubmit }
          >
            <div className={ en_form_style == 'full-width-bg' ? 'row' : '' }>
              <div className={ en_form_style == 'full-width-bg' ? 'col-md-8' : '' }>
                  <FormGenerator {...{fields, attributes, onInputChange, onBlur, errors}} />
              </div>

              <div className={ en_form_style == 'full-width-bg' ? 'col-md-4 submit' : 'submit' }>
                <button type="submit" form="p4en_form" name="p4en_form_save_button" id="p4en_form_save_button" className="btn btn-primary btn-block" >
                  { button_text ? unescape(button_text) : __( 'Sign', 'planet4-engagingnetworks' ) }
                </button>
                <div className="enform-notice"></div>
                {en_form_style == 'full-width-bg' &&
                  <div className="enform-legal">
                    <p>{text_below_button ? unescape(text_below_button) : ''}</p>
                  </div>
                }
              </div>

              {en_form_style !== 'full-width-bg' &&
                <div className="enform-legal">
                  <p>{text_below_button ? unescape(text_below_button) : ''}</p>
                </div>
              }
            </div>
            {error_msg &&
              <span className="enform-error">{ error_msg }</span>
            }
          </form>
        </div>

      </div>
    </div>
  )
}

const submitENForm = (props) => {
  const {
    form_data,
    fields,
    url,
    enform_goal,
    thankyou_url,
    setErrorMsg,
    setActiveTplId,
  } = props;

  console.log('form_data', form_data, fields);

  // Normalize
  let supporter = {
    questions: {}
  };

  for (const key in form_data) {
    let field = fields.find((f) => inputId(f)['name'] === key);
    if (!field) {
      continue;
    }

    if (field.input_type === 'checkbox') {
      supporter.questions['question.' + field.id] = form_data[key] === true ? 'Y' : 'N';
    } else {
      supporter[field.property] = form_data[key] ?? null;
    }
  }

  const post_data = {
    standardFieldNames: true,
    supporter: supporter
  };
  console.log('post data', post_data);

  // Fetch token
  const token_endpoint = `${p4bk_vars.siteUrl}/wp-json/planet4/v1/get-en-session-token`;
  fetch(token_endpoint)
    .then(response => response.json())
    .then(token_data => {
      console.log('token_data', token_data);
      const session_token = token_data.token || null;
      if (!session_token) {
        throw new Error('Token not found.');
      }

      // Send
      return fetch(url, {
        method: 'POST',
        contentType: 'application/json',
        mode: 'cors',
        headers: {
          'Content-Type': 'application/json',
          'ens-auth-token': session_token
        },
        body: JSON.stringify(post_data),
      });
    }).then(() => {
      // Submit Hotjar success
      if ( typeof hj === 'function' ) {
        hj('formSubmitSuccessful'); // eslint-disable-line no-undef
      }

      // DataLayer push event on successful EN form submission.
      if ( typeof google_tag_value !== 'undefined' && google_tag_value ) {
        let dataLayerPayload = {
          'event' : 'petitionSignup'
        };
        if ( enform_goal ) {
          dataLayerPayload.gGoal = enform_goal;
        }
        dataLayer.push(dataLayerPayload);
      }

      // redirect or thanks
      if (thankyou_url && urlIsValid(thankyou_url)) {
        window.location = thankyou_url;
      } else {
        setActiveTplId('thankyou');
      }
    })
    .catch((error) => {
      console.error('Error:', error);
      // Submit Hotjar failure
      if ( typeof hj === 'function' ) {
        hj('formSubmitFailed'); // eslint-disable-line no-undef
      }
      setErrorMsg(error.message);
    });
}

const validateForm = (form_data, fields, setErrors) => {
  setErrors({});

  let formIsValid = true;
  fields.forEach((field) => {
    if (!validateField(field, form_data, setErrors)) {
      formIsValid = false;
    }
  });

  return formIsValid;
}

const validateField = (field, form_data, setErrors) => {
  const {id, name} = inputId(field);
  const value = form_data[name];
  const element = document.getElementById(id);

  if (!element) {
    return true;
  }

  if (field.required && [null, false, ''].includes(value)) {
    setErrors(errors => {
      return {...errors, [field.id]: element.dataset["errormessage"]}
    });
    return false;
  }

  if (element.type === "email") {
    return validateEmail(field, element, setErrors, value);
  }

  if (element.type === "radio") {
    return validateRadio(field, element, setErrors, name, fields);
  }

  const regexPattern = element.dataset['validate_regex'];
  if (regexPattern?.length) {
    return validateRegex(field, element, setErrors, value, regexPattern);
  }

  const callbackFunction = element.dataset['validate_callback'];
  if ('function' === typeof window[callbackFunction]) {
    return validateCallback(field, element, setErrors, callbackFunction);
  }

  return true;
}

const validateEmail = (field, element, setErrors, value) => {
  // Reference: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/email#basic_validation
  let re = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
  if (!re.test(String(value).toLowerCase())) {
    setErrors(errors => {
      return {...errors, [field.id]: element.dataset["errormessage"]}
    });
    return false;
  }
  return true;
}

const validateRadio = (field, element, setErrors, name, fields) => {
  let sibling_radios_checked = fields.find((f) => {
    let {id: f_id, name: f_name} = inputId(f);
    let f_element = document.getElementById(f_id);
    return f_name === name && f_element && f_element.checked === true;
  })
  if (!sibling_radios_checked) {
    setErrors(errors => {
      return {...errors, [field.id]: element.dataset["errormessage"]}
    });
    return false;
  }
  return true;
}

const validateRegex = (field, element, setErrors, value, regexPattern) => {
  const regex = new RegExp(regexPattern);
  if (!regex.test(value)) {
    setErrors(errors => {
      return {...errors, [field.id]: element.dataset["validate_regex_msg"]}
    });
    return false;
  }
  return true;
}

const validateCallback = (field, element, setErrors, callbackFunction) => {
  const validate = window[callbackFunction](element.value);
  if (true !== validate) {
    setErrors(errors => {
      return {...errors, [field.id]: validate}
    });
    return false;
  }
  return true;
}

const urlIsValid = (url_str) => {
  try {
    let url = new URL(url_str);
    return ['http:', 'https:'].includes(url.protocol);
  } catch (e) {
    console.log(e);
  }

  return false;
}

const ThankYou = ({attributes, error_msg}) => {
  const {
    en_form_style,
    thankyou_title,
    thankyou_subtitle,
    thankyou_social_media_message,
    thankyou_donate_message,
    donate_button_checkbox,
    donate_text,
    donatelink,
    social,
    social_accounts,
  } = attributes;

  let social_params = {...social, utm_medium: 'thank-you'};

  return (
    <div className="enform" id="enform">
    <div
      className={'thankyou ' + (en_form_style != 'side-style' ? 'full-width': '')}
    >
      {error_msg &&
        <span className="enform-error">{ error_msg }</span>
      }

      <header>
        <h2 className="page-section-header">{ unescape(thankyou_title) }</h2>
      </header>
      <p className="page-section-description"
        dangerouslySetInnerHTML={{ __html: thankyou_subtitle }} />

      <div className="sub-section formblock-flex">

        <div className="form-group">
          <h5>{ thankyou_social_media_message }</h5>
        </div>

        <div className="social-media form-group">
          <ShareButtons {...{social_params, social_accounts}} />
        </div>

        {! donate_button_checkbox &&
          <>
            <div className="form-group">
              <h5>{thankyou_donate_message}</h5>
            </div>

            <div className="form-group">
              <a href={donatelink} className="btn btn-primary btn-block">{donate_text ?? __('Donate', 'planet4-engagingnetworks')}</a>
            </div>
          </>
        }

      </div>
    </div>
    </div>
  )
}
