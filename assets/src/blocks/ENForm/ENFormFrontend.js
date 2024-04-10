import {ShareButtons} from '../../components/ShareButtons/ShareButtons';
import {FormGenerator} from './FormGenerator';
import {useState} from '@wordpress/element';
import {unescape} from '../../functions/unescape';

import {inputId} from './inputId';

const {__} = wp.i18n;

export const ENFormFrontend = ({attributes}) => {
  const {
    en_page_id,
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
    campaign_logo_path,
    className,
  } = attributes;

  const section_style = (style => {
    switch (style) {
    case 'side-style':
      return 'block-header alignfull';
    case 'full-width-bg':
      return 'block-footer alignfull';
    default:
      return '';
    }
  })(en_form_style);

  const style_has_image = ['full-width-bg', 'side-style'].includes(en_form_style);
  const is_side_style = en_form_style === 'side-style';
  const fields = en_form_fields ?? [];

  const HeadingTag = content_title_size || 'h1';

  const [activeTplId, setActiveTplId] = useState('signup');
  const [errors, setErrors] = useState({});
  const [error_msg, setErrorMsg] = useState(null);
  const [form_data, setFormData] = useState(
    fields.reduce((acc, f) => {
      return {...acc, [inputId(f).name]: null};
    }, {})
  );

  const onInputChange = (field, e) => {
    setErrors(errs => {
      return {...errs, [field.id]: null};
    });

    const target = e.target;
    const value = target.type === 'checkbox' ? target.checked : target.value;
    const name = target.name;

    setFormData({...form_data, [name]: value});
  };

  const onBlur = field => {
    validateField(field, form_data, setErrors);
  };

  const onFormSubmit = e => {
    e.preventDefault();

    setErrorMsg(null);
    if (!validateForm(form_data, fields, setErrors)) {
      // eslint-disable-next-line no-console
      console.error('Validation error.', errors);
      return;
    }

    submitENForm({form_data, fields, enform_goal, thankyou_url, setErrorMsg, setActiveTplId, en_page_id});
  };

  return (
    <section
      className={`block enform-wrap enform-${en_form_style} ${section_style} ${className ?? ''}`}
      style={{position: 'inherit'}}
    >
      {style_has_image && background_image_src &&
        <picture>
          <img src={background_image_src}
            style={{objectPosition: background_image_focus || {}}}
            border="0"
            srcSet={background_image_srcset || ''}
            sizes={background_image_sizes || ''}
            className={background > 0 ? `wp-image-${background}` : ''}
            alt=""
          />
        </picture>
      }

      <div className="caption-overlay"></div>

      <div className="container">
        <div className="row">
          <div className="col-md-12">

            {is_side_style &&
              <div className="form-caption">
                {campaign_logo && campaign_logo_path &&
                  <img src={campaign_logo_path}
                    alt={content_title ?? ''}
                    className="campaign-logo" />
                }
                <HeadingTag dangerouslySetInnerHTML={{__html: content_title ? unescape(content_title) : ''}} />
                <div dangerouslySetInnerHTML={{__html: unescape(content_description)}} />
              </div>
            }

            {activeTplId === 'signup' &&
              <Signup {...{attributes, fields, form_data, onInputChange, onBlur, onFormSubmit, error_msg, errors}} />
            }
            {activeTplId === 'thankyou' &&
              <ThankYou {...{attributes, error_msg}} />
            }
          </div>
        </div>
      </div>
    </section>
  );
};

const Signup = ({attributes, fields, form_data, onInputChange, onBlur, onFormSubmit, error_msg, errors}) => {
  const {
    en_form_style,
    title,
    description,
    text_below_button,
    button_text,
  } = attributes;

  const is_side_style = en_form_style === 'side-style';
  // Keep extra content between repaints
  const extra_content = document.querySelector('.enform-extra-header-placeholder')?.innerHTML;

  return (
    <div className="enform" id="enform">
      <div id="enform-content">

        <div className="title-and-description">
          {title &&
            <h2 dangerouslySetInnerHTML={{__html: title ? unescape(title) : ''}} />
          }
          {is_side_style &&
            <div className="enform-extra-header-placeholder"
              dangerouslySetInnerHTML={{__html: extra_content ? unescape(extra_content) : ''}} />
          }
          {description &&
            <div className="form-description" dangerouslySetInnerHTML={{__html: unescape(description)}} />
          }
        </div>

        <div className="form-container">
          <form
            id="p4en_form"
            name="p4en_form"
            method="post"
            noValidate
            onSubmit={onFormSubmit}
          >
            <div className={en_form_style === 'full-width-bg' ? 'row' : ''}>
              <div className={en_form_style === 'full-width-bg' ? 'col-md-8' : ''}>
                <FormGenerator {...{fields, attributes, onInputChange, onBlur, errors}} />
              </div>

              <div className={en_form_style === 'full-width-bg' ? 'col-md-4 submit' : 'submit'}>
                <button type="submit" form="p4en_form" name="p4en_form_save_button" id="p4en_form_save_button" className="btn btn-primary" >
                  { button_text ? unescape(button_text) : __('Sign', 'planet4-engagingnetworks') }
                </button>
                <div className="enform-notice"></div>
                {en_form_style === 'full-width-bg' &&
                  <div className="enform-legal">
                    <p dangerouslySetInnerHTML={{__html: text_below_button ? unescape(text_below_button) : ''}} />
                  </div>
                }
              </div>

              {en_form_style !== 'full-width-bg' &&
                <div className="enform-legal">
                  <p dangerouslySetInnerHTML={{__html: text_below_button ? unescape(text_below_button) : ''}} />
                </div>
              }
            </div>
            {error_msg &&
              <span className="enform-error">{ error_msg }</span>
            }
          </form>
          <div id="form-data" data-postdata={JSON.stringify(makePostData(form_data, fields))} />
        </div>
      </div>
    </div>
  );
};

const submitENForm = props => {
  const {
    form_data,
    fields,
    enform_goal,
    thankyou_url,
    setErrorMsg,
    setActiveTplId,
    en_page_id,
  } = props;

  const post_data = makePostData(form_data, fields);

  // Send form
  const post_url = `${window.p4bk_vars.siteUrl}/wp-json/planet4/v1/enform/${en_page_id}`;
  fetch(post_url, {
    method: 'POST',
    contentType: 'application/json',
    mode: 'cors',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(post_data),
  })
    .then(response => {
      if (response.status !== 200) {
        throw new Error(`Error submitting form: ${response.statusText || 'unknown error'}`);
      }
      return response.json();
    })
    .then(() => {
    // Submit Hotjar success
      if (typeof hj === 'function') {
        hj('formSubmitSuccessful'); // eslint-disable-line no-undef
      }

      // DataLayer push event on successful EN form submission.
      // eslint-disable-next-line no-undef
      if (typeof google_tag_value !== 'undefined' && google_tag_value) {
        const dataLayerPayload = {
          event: 'petitionSignup',
        };
        if (enform_goal) {
          dataLayerPayload.gGoal = enform_goal;
        }
        // eslint-disable-next-line no-undef
        dataLayer.push(dataLayerPayload);
      }

      // redirect or thanks
      if (thankyou_url && urlIsValid(thankyou_url)) {
        window.location = thankyou_url;
      } else {
        setActiveTplId('thankyou');
      }
    })
    .catch(error => {
      // eslint-disable-next-line no-console
      console.error('Error:', error);
      // Submit Hotjar failure
      if (typeof hj === 'function') {
        hj('formSubmitFailed'); // eslint-disable-line no-undef
      }
      setErrorMsg(error.message);
    });
};

/**
 * Build data to be posted on form submit
 *
 * @param {Object} form_data The form data
 * @param {Array}  fields    The fields
 * @return {Object}  Formatted data for EN
 */
const makePostData = (form_data, fields) => {
  const supporter = {
    questions: {},
  };

  for (const key in form_data) {
    const field = fields.find(f => inputId(f).name === key);
    if (!field) {
      continue;
    }

    // Questions via checkbox or text question
    if (key.startsWith('supporter.questions.')) {
      const value = typeof form_data[key] === 'string' ? form_data[key] : checkboxValue(form_data[key]);
      supporter.questions['question.' + field.id] = value;
      continue;
    }

    // Remove fields without name
    if (!field.property) {
      continue;
    }

    // Basic data & hidden field
    if (null !== form_data[key]) {
      supporter[field.property] = form_data[key];
    } else if (field.input_type === 'hidden') {
      supporter[field.property] = field.default_value;
    }
  }

  return {
    standardFieldNames: true,
    supporter,
  };
};

const checkboxValue = value => true === value ? 'Y' : 'N';

const validateForm = (form_data, fields, setErrors) => {
  setErrors({});

  let formIsValid = true;
  fields.forEach(field => {
    if (!validateField(field, form_data, setErrors)) {
      formIsValid = false;
    }
  });

  return formIsValid;
};

const validateField = (field, form_data, setErrors) => {
  const {id, name} = inputId(field);
  const value = form_data[name];
  const element = document.getElementById(id);

  if (!element) {
    return true;
  }

  if (field.required && [null, false, ''].includes(value)) {
    setErrors(errors => {
      return {...errors, [field.id]: element.dataset.errormessage};
    });
    return false;
  }

  if (element.type === 'email') {
    return validateEmail(field, element, setErrors, value);
  }

  if (element.type === 'radio') {
    return validateRadio(field, element, setErrors, name);
  }

  const regexPattern = element.dataset.validate_regex;
  if (regexPattern?.length) {
    return validateRegex(field, element, setErrors, value, regexPattern);
  }

  const callbackFunction = element.dataset.validate_callback;
  if ('function' === typeof window[callbackFunction]) {
    return validateCallback(field, element, setErrors, callbackFunction);
  }

  return true;
};

const validateEmail = (field, element, setErrors, value) => {
  // Reference: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/email#basic_validation
  const re = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
  if (!re.test(String(value).toLowerCase())) {
    setErrors(errors => {
      return {...errors, [field.id]: element.dataset.errormessage};
    });
    return false;
  }
  return true;
};

const validateRadio = (field, element, setErrors, name, fields) => {
  const sibling_radios_checked = fields.find(f => {
    const {id: f_id, name: f_name} = inputId(f);
    const f_element = document.getElementById(f_id);
    return f_name === name && f_element && f_element.checked === true;
  });
  if (!sibling_radios_checked) {
    setErrors(errors => {
      return {...errors, [field.id]: element.dataset.errormessage};
    });
    return false;
  }
  return true;
};

const validateRegex = (field, element, setErrors, value, regexPattern) => {
  const regex = new RegExp(regexPattern);
  if (!regex.test(value)) {
    setErrors(errors => {
      return {...errors, [field.id]: element.dataset.validate_regex_msg};
    });
    return false;
  }
  return true;
};

const validateCallback = (field, element, setErrors, callbackFunction) => {
  const validate = window[callbackFunction](element.value);
  if (true !== validate) {
    setErrors(errors => {
      return {...errors, [field.id]: validate};
    });
    return false;
  }
  return true;
};

const urlIsValid = url_str => {
  try {
    const url = new URL(url_str);
    return ['http:', 'https:'].includes(url.protocol);
  } catch (e) {
    // eslint-disable-next-line no-console
    console.log(e);
  }

  return false;
};

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

  const social_params = {...social, utm_medium: 'thank-you'};

  return (
    <div className="enform" id="enform">
      <div
        className={'thankyou ' + (en_form_style !== 'side-style' ? 'full-width' : '')}
      >
        {error_msg &&
        <span className="enform-error">{ error_msg }</span>
        }

        <header>
          <h2 className="page-section-header">{ unescape(thankyou_title) }</h2>
        </header>
        <p className="page-section-description"
          dangerouslySetInnerHTML={{__html: thankyou_subtitle}} />

        <div className="sub-section formblock-flex">

          <div className="form-group">
            <h5>{ thankyou_social_media_message }</h5>
          </div>

          <div className="social-media form-group">
            <ShareButtons {...{social_params, social_accounts}} />
          </div>

          {!donate_button_checkbox &&
          <>
            <div className="form-group">
              <h5>{thankyou_donate_message}</h5>
            </div>

            <div className="form-group">
              <a href={donatelink} className="btn btn-primary">{donate_text ?? __('Donate', 'planet4-engagingnetworks')}</a>
            </div>
          </>
          }

        </div>
      </div>
    </div>
  );
};
