import {FrontendRichText} from '../components/FrontendRichText/FrontendRichText';
import {CookiesFieldResetButton} from './CookiesFieldResetButton';
const {InspectorControls} = wp.blockEditor;
const {PanelBody} = wp.components;

const {__} = wp.i18n;

const COOKIES_DEFAULT_COPY = window.p4_vars.options.cookies_default_copy || {};

// Planet4 settings(Planet 4 > Cookies > Enable Analytical Cookies).
const ENABLE_ANALYTICAL_COOKIES = window.p4_vars.options.enable_analytical_cookies;

export const CookiesEditor = ({setAttributes, attributes, isSelected}) => {
  const {
    title,
    description,
    className,
    necessary_cookies_name,
    necessary_cookies_description,
    analytical_cookies_name,
    analytical_cookies_description,
    all_cookies_name,
    all_cookies_description,
  } = attributes;

  const getFieldValue = fieldName => {
    if (attributes[fieldName] === undefined) {
      return COOKIES_DEFAULT_COPY[fieldName] || '';
    }
    return attributes[fieldName] || '';
  };

  const toAttribute = attributeName => value => setAttributes({
    [attributeName]: value,
  });

  const renderView = () => (
    <section className={`block cookies-block ${className ?? ''}`}>
      <header>
        <FrontendRichText
          tagName="h2"
          className="page-section-header cookies-title"
          placeholder={__('Enter title', 'planet4-blocks-backend')}
          value={title}
          onChange={toAttribute('title')}
          withoutInteractiveFormatting
          allowedFormats={[]}
          editable
        />
      </header>
      <FrontendRichText
        tagName="p"
        className="page-section-description cookies-description"
        placeholder={__('Enter description', 'planet4-blocks-backend')}
        value={description}
        onChange={toAttribute('description')}
        withoutInteractiveFormatting
        allowedFormats={['core/bold', 'core/italic']}
        editable
      />
      <>
        <div className="d-flex align-items-center">
          <FrontendRichText
            tagName="span"
            className="custom-control-description cookies-header-text"
            placeholder={__('Enter necessary cookies name', 'planet4-blocks-backend')}
            value={getFieldValue('necessary_cookies_name')}
            onChange={toAttribute('necessary_cookies_name')}
            withoutInteractiveFormatting
            allowedFormats={[]}
            editable
          />
          <span className="always-enabled">{__('Always enabled', 'planet4-blocks')}</span>
          <CookiesFieldResetButton
            fieldName="necessary_cookies_name"
            currentValue={necessary_cookies_name}
            toAttribute={toAttribute}
          />
        </div>
        <div className="d-flex align-items-center">
          <FrontendRichText
            tagName="p"
            className="cookies-checkbox-description"
            placeholder={__('Enter necessary cookies description', 'planet4-blocks-backend')}
            value={getFieldValue('necessary_cookies_description')}
            onChange={toAttribute('necessary_cookies_description')}
            withoutInteractiveFormatting
            allowedFormats={['core/bold', 'core/italic']}
            editable
          />
          <CookiesFieldResetButton
            fieldName="necessary_cookies_description"
            currentValue={necessary_cookies_description}
            toAttribute={toAttribute}
          />
        </div>
      </>
      {ENABLE_ANALYTICAL_COOKIES &&
        <>
          <div className="d-flex align-items-center">
            <label className="custom-control" style={{pointerEvents: 'none'}} htmlFor="analytical-cookies__control">
              <input
                id="analytical-cookies__control"
                type="checkbox"
                name="analytical_cookies"
                onChange={() => { }}
                className="p4-custom-control-input"
              />
              <FrontendRichText
                tagName="span"
                className="custom-control-description cookies-header-text"
                placeholder={__('Enter analytical cookies name', 'planet4-blocks-backend')}
                value={getFieldValue('analytical_cookies_name')}
                onChange={toAttribute('analytical_cookies_name')}
                withoutInteractiveFormatting
                allowedFormats={[]}
                editable
              />
            </label>
            <CookiesFieldResetButton
              fieldName="analytical_cookies_name"
              currentValue={analytical_cookies_name}
              toAttribute={toAttribute}
            />
          </div>
          <div className="d-flex align-items-center">
            <FrontendRichText
              tagName="p"
              className="cookies-checkbox-description"
              placeholder={__('Enter analytical cookies description', 'planet4-blocks-backend')}
              value={getFieldValue('analytical_cookies_description')}
              onChange={toAttribute('analytical_cookies_description')}
              withoutInteractiveFormatting
              allowedFormats={['core/bold', 'core/italic']}
              editable
            />
            <CookiesFieldResetButton
              fieldName="analytical_cookies_description"
              currentValue={analytical_cookies_description}
              toAttribute={toAttribute}
            />
          </div>
        </>
      }
      <>
        <div className="d-flex align-items-center">
          <label className="custom-control" style={{pointerEvents: 'none'}} htmlFor="all-cookies__control">
            <input
              id="all-cookies__control"
              type="checkbox"
              name="all_cookies"
              className="p4-custom-control-input"
            />
            <FrontendRichText
              tagName="span"
              className="custom-control-description cookies-header-text"
              placeholder={__('Enter all cookies name', 'planet4-blocks-backend')}
              value={getFieldValue('all_cookies_name')}
              onChange={toAttribute('all_cookies_name')}
              withoutInteractiveFormatting
              allowedFormats={[]}
            />
          </label>
          <CookiesFieldResetButton
            fieldName="all_cookies_name"
            currentValue={all_cookies_name}
            toAttribute={toAttribute}
          />
        </div>
        <div className="d-flex align-items-center">
          <FrontendRichText
            tagName="p"
            className="cookies-checkbox-description"
            placeholder={__('Enter all cookies description', 'planet4-blocks-backend')}
            value={getFieldValue('all_cookies_description')}
            onChange={toAttribute('all_cookies_description')}
            withoutInteractiveFormatting
            allowedFormats={['core/bold', 'core/italic']}
          />
          <CookiesFieldResetButton
            fieldName="all_cookies_description"
            currentValue={all_cookies_description}
            toAttribute={toAttribute}
          />
        </div>
      </>
    </section>
  );

  const renderEdit = () => (
    <>
      <InspectorControls>
        <PanelBody title={__('Learn more about this block ', 'planet4-blocks-backend')} initialOpen={false}>
          <p className="components-base-control__help">
            <a target="_blank" href="https://planet4.greenpeace.org/content/blocks/cookies/" rel="noreferrer">
            P4 Handbook Cookies
            </a>
            {' '} &#127850;
          </p>
        </PanelBody>
      </InspectorControls>
    </>
  );

  return (
    <>
      {isSelected ? renderEdit() : null}
      {renderView()}
    </>
  );
};
