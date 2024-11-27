window.dataLayer = window.dataLayer || [];

export const AccordionFrontend = ({title, description, tabs, animation, className}) => (
  <section className={`block accordion-block ${className ?? ''} ${animation ? `animate__animated ${animation}` : ''}`}>
    {title &&
      <header>
        <h2 className="page-section-header">{title}</h2>
      </header>
    }
    {description &&
      <p className="page-section-description" dangerouslySetInnerHTML={{__html: description}} />
    }
    {tabs.map(({headline, text, button}, index) => {
      const {button_text, button_url, button_new_tab} = button || {};
      const buttonProps = {};
      if (button_new_tab) {
        buttonProps.target = '_blank';
        buttonProps.rel = 'noopener noreferrer';
      }

      return (
        <div key={`accordion-content-${index}`} className="accordion-content">
          {headline &&
            <div
              className="accordion-headline"
              name={headline}
            >
              {headline}
            </div>
          }
          <div className="panel panel-hidden">
            {text &&
              <p className="accordion-text" dangerouslySetInnerHTML={{__html: text}} />
            }
            {button_text &&
              <a
                className="btn btn-secondary accordion-btn"
                href={button_url}
                {...buttonProps}
              >
                {button_text}
              </a>
            }
          </div>
        </div>
      );
    })}
  </section>
);
