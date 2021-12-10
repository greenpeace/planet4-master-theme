import { useState, useEffect } from '@wordpress/element';

export const useHubspotForm = (shortcode, targetRef) => {
  const [ submitted, setSubmitted ] = useState(false);
  const [ formId, setFormId ] = useState();
  const [ portalId, setPortalId ] = useState();
  const [ submittedMessage, setSubmittedMessage ] = useState();

  const createHubspotForm = () => {
    window.hbspt.forms.create({
      portalId,
      formId,
      target: `#${targetRef.current.attributes.id.value}`,
      region: "",
      onFormSubmitted: ($form) => {
        setSubmitted(true);
        if($form.length) {
          setSubmittedMessage($form[0].innerText);
        }
      }
    });
  }

  const fetchHubspotFormsLibrary = () => {
    const script = document.createElement('script');
    script.src = 'https://js.hsforms.net/forms/v2.js';
    document.body.appendChild(script);
    script.addEventListener('load', createHubspotForm);
  }

  useEffect(() => {
    if(formId && portalId) {
      try {
        createHubspotForm();
      } catch(exception) {
        fetchHubspotFormsLibrary();
      }
    }
  }, [
    formId,
    portalId,
  ]);

  /**
   * Parse the `portalId` and `formId` values from the `shortcode`.
   */
  useEffect(() => {
    if(shortcode) {
      shortcode.replace(/["'\)\(\]\[]/g,'').split(' ').forEach(value => {
        if(/portal=/.test(value)) {
          setPortalId(value.split('=')[1]);
        }
        if(/id=/.test(value)) {
          setFormId(value.split('=')[1]);
        }
      });
    }
  }, [
    shortcode,
  ]);

  return {
    submitted,
    submittedMessage,
  };
};
