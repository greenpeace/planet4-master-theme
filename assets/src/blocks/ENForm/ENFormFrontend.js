import { ShareButtons } from './ShareButtons';
import { ENFormGenerator } from './ENFormGenerator';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { unescape } from '../../functions/unescape';

import { inputName } from './inputName';

const { __ } = wp.i18n;

export const ENFormFrontend = (attributes) => {
  const {
    en_page_id = 47512,
    en_form_id,
    en_form_style,
    en_form_fields,
    enform_goal,
    title,
    description,
    content_title,
    content_title_size,
    content_description,
    donate_button_checkbox,
    thankyou_url,
    thankyou_title,
    thankyou_subtitle,
    thankyou_donate_message,
    thankyou_social_media_message,
    background,
    background_image_src,
    background_image_srcset,
    background_image_sizes,
    background_image_focus,
    text_below_button,
    button_text,
    campaign_logo,
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
  const [error_msg, setErrorMsg] = useState(null);
  const [form, setFormData] = useState(
    fields.reduce((acc, f) => { return {...acc, [inputName(f)]: null} }, {})
  );
  const onInputChange = (e) => {
    const target = e.target;
    const value = target.type === 'checkbox' ? target.checked : target.value;
    const name = target.name;
    console.log('form', name, value);
    setFormData({...form, [name]: value});
    console.log(form);
  }

  const onFormSubmit = (e) => {
    e.preventDefault();
    const url = `https://e-activist.com/ens/service/page/${en_page_id}/process`;
    submitENForm({form, fields, url, enform_goal, thankyou_url, setErrorMsg, setActiveTplId});
  }

  return (
    <section
      className={`block enform-wrap enform-${en_form_style} ${section_style}`}
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
                  {unescape(content_title)}
                </HeadingTag>
                <p>{unescape(content_description)}</p>
              </div>
            }

            {activeTplId === 'signup' &&
              <Signup {...{attributes, fields, onInputChange, onFormSubmit, error_msg}} />
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

const Signup = ({attributes, fields, onInputChange, onFormSubmit, error_msg}) => {
  const {
    en_page_id = 47512,
    en_form_id,
    en_form_style,
    en_form_fields,
    enform_goal,
    title,
    description,
    background,
    background_image_src,
    text_below_button,
    button_text,
    campaign_logo,
  } = attributes;

  const is_side_style = en_form_style === 'side-style';

  return (
    <div className="enform" id="enform">
      <div id="enform-content">

        <div className="title-and-description">
          {title &&
            <h2>{unescape(title)}</h2>
          }
          {is_side_style &&
            <div className="enform-extra-header-placeholder"></div>
          }
          <div className="form-description">
            {unescape(description)}
          </div>
        </div>

        <div className="form-container">
          <form
            id="p4en_form"
            name="p4en_form"
            method="post"
            noValidate
            onSubmit={ onFormSubmit }
          >
            {error_msg &&
              <span className="enform-error">{ error_msg }</span>
            }

            <div className={ en_form_style == 'full-width-bg' ? 'row' : '' }>
              <div className={ en_form_style == 'full-width-bg' ? 'col-md-8' : '' }>
                  <ENFormGenerator {...{fields, attributes, onInputChange}} />
              </div>

              <div className={ en_form_style == 'full-width-bg' ? 'col-md-4 submit' : 'submit' }>
                <button type="submit" form="p4en_form" name="p4en_form_save_button" id="p4en_form_save_button" className="btn btn-primary btn-block" >
                  { button_text ? unescape(button_text) : __( 'Sign', 'planet4-engagingnetworks' ) }
                </button>
                <div className="enform-notice"></div>
                {en_form_style == 'full-width-bg' &&
                  <div className="enform-legal">
                    <p>{unescape(text_below_button)}</p>
                  </div>
                }
              </div>

              {en_form_style !== 'full-width-bg' &&
                <div className="enform-legal">
                  <p>{unescape(text_below_button)}</p>
                </div>
              }
            </div>
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

  console.log('data', form_data);

  // Normalize
  let supporter = {
    questions: {}
  };

  for (const key in form_data) {
    let field = fields.find((f) => inputName(f) === key);
    if (!field) {
      continue;
    }

    if (field.input_type === 'checkbox') {
      supporter.questions['question.' + field.id] = form_data[key] === true ? 'Y' : 'N';
    } else {
      supporter[field.property] = form_data[key] ?? null;
    }
  }

  const data = {
    standardFieldNames: true,
    supporter: supporter
  };

  console.log('data', data);

  // Fetch token
  const token_endpoint = '/wp-json/planet4/v1/get-en-session-token';
  fetch(token_endpoint)
    .then(response => response.json())
    .then(data => {
      console.log('data', data);
      const session_token = data.token || null;
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
          'ens-auth-token': sessionToken
        },
        body: JSON.stringify(data),
      });
    }).then(() => {
      console.log('next');
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

      // redirect
      // todo: validate url
      if (thankyou_url) {
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
      setErrorMsg(error);
    });
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
  } = attributes;

  return (
    <div
      className={'thankyou ' + (en_form_style != 'side-style' ? 'full-width': '')}
    >
      {error_msg &&
        <span className="enform-error">{ error_msg }</span>
      }

      <header>
        <h2 className="page-section-header">{ thankyou_title }</h2>
      </header>
      <p className="page-section-description">{ thankyou_subtitle }</p>

      <div className="sub-section formblock-flex">

        <div className="form-group">
          <h5>{ thankyou_social_media_message }</h5>
        </div>

        <div className="social-media form-group">
          <ShareButtons {...{social, accounts: []}} />
        </div>

        {! donate_button_checkbox &&
          <>
            <div className="form-group">
              <h5>{thankyou_donate_message}</h5>
            </div>

            <div className="form-group">
              <a href={donatelink} className="btn btn-primary btn-block">{donate_text}</a>
            </div>
          </>
        }

      </div>
    </div>
  )
}
