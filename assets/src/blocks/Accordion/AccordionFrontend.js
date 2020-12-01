import { useState } from '@wordpress/element';

window.dataLayer = window.dataLayer || [];

export const AccordionFrontend = attributes => {
  const [openTab, setOpenTab] = useState(-1);
  const { title, description, tabs, className } = attributes;

  const toggleTab = (index, headline) => {
    const textToSend = headline.length > 50 ? `${headline.substring(0, 50)}...` : headline;
    if (index === openTab) {
      setOpenTab(-1);
      dataLayer.push({
        event: 'Close FAQ',
        Question: textToSend
      });
    } else {
      setOpenTab(index);
      dataLayer.push({
        event: 'Expand FAQ',
        Question: textToSend
      });
    }
  }

  const handleReadMoreClick = headline => {
    const textToSend = headline.length > 50 ? `${headline.substring(0, 50)}...` : headline;
    dataLayer.push({
      event: 'Read More FAQ',
      Question: textToSend
    });
  }

  return (
    <section className='block accordion-block'>
      {title &&
        <header>
          <h2 className="page-section-header">{title}</h2>
        </header>
      }
      {description &&
        <p className="page-section-description" dangerouslySetInnerHTML={{ __html: description }} />
      }
      {tabs.map(({ headline, text, button }, index) => {
        const { button_text, button_url, button_new_tab } = button || {};
        return (
          <div key={`accordion-content-${index}`} className={`accordion-content ${className}`}>
            {headline &&
              <div
                className={`accordion-headline ${openTab === index ? 'open' : ''}`}
                onClick={() => toggleTab(index, headline)}
                name={headline}
              >
                {headline}
              </div>
            }
            <div className={`panel ${openTab === index ? '' : 'panel-hidden'}`}>
              {text &&
                <p className="accordion-text" dangerouslySetInnerHTML={{ __html: text }} />
              }
              {button_text &&
                <a className="btn btn-secondary accordion-btn"
                  onClick={() => handleReadMoreClick(headline)}
                  href={button_url}
                  target={button_new_tab ? '_blank' : ''}
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
}
