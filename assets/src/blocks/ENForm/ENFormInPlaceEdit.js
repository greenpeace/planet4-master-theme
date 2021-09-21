import { FormGenerator } from './FormGenerator';
import { ShareButtons } from '../../components/ShareButtons/ShareButtons';
import { RichText, BlockControls } from '@wordpress/block-editor';
import { ToolbarGroup } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';

const { __ } = wp.i18n;

export const ENFormInPlaceEdit = ({attributes, setAttributes}) => {
  const {
    en_form_style,
    className,
  } = attributes;

  // Switch between signup form and thank you message
  const templates = [
    {
      id: "signup",
      icon: "format-aside",
      title: __('Signup form', 'planet4-blocks-backend'),
    },
    {
      id: "thankyou",
      icon: "awards",
      title: __('Thank you message', 'planet4-blocks-backend'),
    }
  ];
  const [activeTplId, setActiveTplId] = useState('signup');
  const activeTpl = templates.find((tpl) => {return tpl.id === activeTplId});

  // Style specific params
  const is_side_style = en_form_style === 'side-style';
  const style_has_image = en_form_style === 'full-width-bg' || en_form_style === 'side-style';
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

  return (
    <>
    <BlockControls>
      <ToolbarGroup
        isCollapsed={ true }
        icon={ activeTpl.icon }
        label={ activeTpl.title }
        controls={
          templates.map((tpl) => {
            return {
              icon: tpl.icon,
              title: tpl.title,
              isActive: activeTplId === tpl.id,
              onClick: () => { setActiveTplId(tpl.id) }
            }
          })
        }
      />
    </BlockControls>

    <section
      className={`block enform-wrap enform-${en_form_style} ${section_style} ${className ?? ''}`}
    >
      {style_has_image &&
        <BackgroundImage {...{attributes}} />
      }
      <div className="container">
        <div className="row">
          <div className="col-md-12">
            {is_side_style &&
              <SideContent {...{attributes, setAttributes}} />
            }
            {activeTplId === 'signup' &&
              <Signup {...{attributes, setAttributes}} />
            }
            {activeTplId === 'thankyou' &&
              <ThankYou {...{attributes, setAttributes}} />
            }
          </div>
        </div>
      </div>
    </section>
    </>
  )
}

const BackgroundImage = ({attributes}) => {
  const {
    background,
    background_image_src,
    background_image_srcset,
    background_image_sizes,
    background_image_focus,
  } = attributes;

  if (! background) {
    return null;
  }

  return (
    <picture>
      <img src={ background_image_src || ''}
        style={{objectPosition: background_image_focus || {}}}
        border="0"
        srcSet={background_image_srcset || ''}
        sizes={background_image_sizes || ''}
        className={ background > 0 ? `wp-image-${background}` : ''}
      />
    </picture>
  );
}

const SideContent = ({attributes, setAttributes}) => {
  const {
    content_title,
    content_description,
    content_title_size,
    campaign_logo,
  } = attributes;

  return (
    <>
    <BlockControls>
      <ToolbarGroup
        isCollapsed={ true }
        icon="heading"
        label={content_title_size.toUpperCase()}
        controls={
          ['h1', 'h2'].map((size) => {
            const isActive = content_title_size === size;
            return {
              isActive,
              icon: "heading",
              title: size.toUpperCase(),
              onClick: () => { setAttributes({content_title_size: size}) }
            }
          })
        }
      />
    </BlockControls>
    <div className="form-caption">
      {campaign_logo &&
        <img src={ campaign_logo }
            alt={ content_title }
            className="campaign-logo" />
      }
      <RichText
        tagName={content_title_size}
        value={content_title}
        onChange={(title) => { setAttributes({content_title: title}) }}
        placeholder={__('Enter title', 'planet4-blocks-backend')}
        keepPlaceholderOnFocus={true}
        withoutInteractiveFormatting
        allowedFormats={[]}
        multiline="false"
      />
      <RichText
        tagName="p"
        value={content_description}
        onChange={(desc) => { setAttributes({content_description: desc}) }}
        placeholder={__('Enter description', 'planet4-blocks-backend')}
        keepPlaceholderOnFocus={true}
        allowedFormats={[]}
        multiline="false"
      />
    </div>
    </>
  );
};

const Signup = ({attributes, setAttributes}) => {
  const {
    en_form_style,
    title,
    description,
    en_form_id,
    en_form_fields,
  } = attributes;

  const form_post = useSelect((select) => {
    return en_form_id
      ? select('core').getEntityRecord('postType', 'p4en_form', en_form_id)
      : [];
  });
  const fields = en_form_fields.length > 0 ? en_form_fields : (
    form_post?.p4enform_fields || []
  );

  return (
    <div className="enform">
      <div id="enform-content">

        <div className="title-and-description">
          <RichText
            tagName="h2"
            value={title}
            onChange={(title) => { setAttributes({title}) }}
            placeholder={__('Enter form title', 'planet4-blocks-backend')}
            keepPlaceholderOnFocus={true}
            withoutInteractiveFormatting
            allowedFormats={[]}
            multiline="false"
          />
          {en_form_style === 'side-style' &&
            <div className={'enform-extra-header-placeholder'}></div>
          }

          <RichText
            tagName="div"
            value={description}
            className="form-description"
            onChange={(description) => { setAttributes({description}) }}
            placeholder={__('Enter form description', 'planet4-blocks-backend')}
            keepPlaceholderOnFocus={true}
            allowedFormats={[]}
            multiline="false"
          />
        </div>

        <div className="form-container">
          <FormContent {...{attributes, setAttributes, fields}}/>
        </div>

      </div>
    </div>
  )
}

const ThankYou = ({attributes, setAttributes}) => {
  const {
    en_form_style,
    thankyou_title,
    thankyou_subtitle,
    thankyou_donate_message,
    thankyou_social_media_message,
    donate_button_checkbox,
    donate_text,
    donatelink,
    social,
    social_accounts,
  } = attributes;

  let social_params = {...social, utm_medium: 'thank-you'};

  const toAttribute = (attributeName) => {
    return value => {
      setAttributes({ [attributeName]: value });
    }
  }

  const container_class = `thankyou ${en_form_style != 'side-style' ? 'full-width': ''}`;

  const error = '';
  if (error) {
    return (
      <div className={container_class}>
        {error &&
          <span className="enform-error">{ error }</span>
        }
      </div>
    )
  }

  return (
    <div className="enform">
      <div className={container_class}>
        <header>
          <RichText
            tagName="h2"
            className="page-section-header"
            value={ thankyou_title }
            onChange={toAttribute('thankyou_title')}
            placeholder={__('Enter title', 'planet4-blocks-backend')}
            keepPlaceholderOnFocus={true}
            withoutInteractiveFormatting
            allowedFormats={[]}
            multiline="false"
          />
        </header>
        <RichText
          tagName="p"
          className="page-section-description"
          value={ thankyou_subtitle }
          onChange={toAttribute('thankyou_subtitle')}
          placeholder={__('Enter description', 'planet4-blocks-backend')}
          keepPlaceholderOnFocus={true}
          allowedFormats={[]}
          multiline="true"
        />

        <div className="sub-section formblock-flex">
          <div className="form-group">
            <RichText
              tagName="h5"
              className="page-section-header"
              value={ thankyou_social_media_message }
              onChange={toAttribute('thankyou_social_media_message')}
              placeholder={__('Enter social media message', 'planet4-blocks-backend')}
              keepPlaceholderOnFocus={true}
              withoutInteractiveFormatting
              allowedFormats={[]}
              multiline="false"
            />
          </div>

          <div className="social-media form-group">
            <ShareButtons {...{social_params, social_accounts}} />
          </div>

          {! donate_button_checkbox &&
            <>
              <div className="form-group">
                <RichText
                  tagName="h5"
                  className="page-section-header"
                  value={ thankyou_donate_message }
                  onChange={toAttribute('thankyou_donate_message')}
                  placeholder={__('Enter donate message', 'planet4-blocks-backend')}
                  keepPlaceholderOnFocus={true}
                  allowedFormats={['core/bold', 'core/italic', 'core/link']}
                  multiline="false"
                />
              </div>

              <div className="form-group">
                <RichText
                  tagName="a"
                  href={ donatelink }
                  className="btn btn-primary btn-block"
                  value={donate_text}
                  onChange={toAttribute('donate_text')}
                  placeholder={__('Donate', 'planet4-blocks-backend')}
                  keepPlaceholderOnFocus={true}
                  withoutInteractiveFormatting
                  allowedFormats={[]}
                  multiline="false"
                />
              </div>
            </>
          }
        </div>
      </div>
    </div>
  )
}

const FormContent = ({attributes, setAttributes, fields}) => {
  const {
    en_form_style,
    button_text,
    text_below_button,
  } = attributes;

  const fwbg = en_form_style === 'full-width-bg';

  return (
    <form id="p4en_form" name="p4en_form">
      <div className={fwbg ? 'row' : ''}>
        <div className={fwbg ? 'col-md-8' : ''}>
          <FormGenerator {...{fields, attributes}} />
        </div>

        <div className={fwbg ? 'col-md-4 submit' : 'submit'}>
          <RichText
            tag="button"
            id="p4en_form_save_button"
            className={'btn btn-primary btn-block' + (fwbg ? ' w-auto' : '')}
            value={ button_text || __( 'Sign', 'planet4-engagingnetworks' ) }
            onChange={(text) => {setAttributes({button_text: text})}}
            placeholder={__('Sign', 'planet4-blocks-backend')}
          />
          {fwbg &&
            <div className="enform-legal">
              <RichText
                tagName="p"
                value={text_below_button}
                placeholder={__('Text below button', 'planet4-blocks-backend')}
                keepPlaceholderOnFocus={true}
                allowedFormats={[]}
                onChange={(text) => {setAttributes({text_below_button: text})}}
              />
            </div>
          }
        </div>
        {! fwbg &&
          <div className="enform-legal">
            <RichText
              tagName="p"
              value={text_below_button}
              placeholder={__('Text below button', 'planet4-blocks-backend')}
              keepPlaceholderOnFocus={true}
              allowedFormats={[]}
              onChange={(text) => {setAttributes({text_below_button: text})}}
            />
          </div>
        }
      </div>
    </form>
  )
}
