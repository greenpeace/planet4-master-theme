export const ColumnsTasks = ({ isCampaign, columns, no_of_columns }) => (
  <>
    <div className='tasks-wrap can-do-steps d-none d-lg-block'>
      <div className='row'>
        {columns.map((column, index) => {
          const {
            title,
            cta_text,
            cta_link,
            attachment,
            description,
            link_new_tab,
          } = column;
          return (
            <div className='col-md-6 col-lg column-wrap step-info'>
              <span className='step-number'>
                <span
                  className='step-number-inner'
                  data-ga-category='Columns Block'
                  data-ga-action='Task Number'
                  data-ga-label='n/a'
                >
                  {index + 1}
                </span>
              </span>
              {title &&
                <h5>
                  {cta_link ?
                    <a
                      href={cta_link}
                      target={link_new_tab ? '_blank' : ''}
                      data-ga-category='Columns Block'
                      data-ga-action='Title'
                      data-ga-label={cta_link}
                    >
                      {title}
                    </a> :
                    title
                  }
                </h5>
              }
              {description &&
                <p dangerouslySetInnerHTML={{ __html: description }} />
              }
              {attachment &&
                <img src={attachment} alt='' />
              }
              {cta_text && cta_link &&
                <a
                  className={`btn btn-small btn-${isCampaign ? 'primary' : 'secondary'}`}
                  href={cta_link}
                  target={link_new_tab ? '_blank' : ''}
                  data-ga-category='Columns Block'
                  data-ga-action='Call to Action'
                  data-ga-label={cta_link}
                >
                  {cta_text}
                </a>
              }
            </div>
          );
        })}
      </div>
    </div>
    <div className='tasks-wrap can-do-steps-mobile d-lg-none'>
      <div id='accordion' className='card-accordion' role='tablist' aria-multiselectable='true'>
        {columns.map((column, index) => {
          const {
            title,
            cta_text,
            cta_link,
            attachment,
            description,
            link_new_tab,
          } = column;
          let taskNumber = 'one';
          if (index === 1) {
            taskNumber = 'two';
          } else if (index === 2) {
            taskNumber = 'three';
          } else if (index === 3) {
            taskNumber = 'four';
          }
          return (
            <div className='card'>
              <a
                className={`card-header ${index > 0 ? ' collapsed' : ''}`}
                role='tab'
                id={`heading-${taskNumber}`}
                data-bs-toggle='collapse'
                data-bs-target={`.card-header:hover + #collapse-${taskNumber}`}
                href={`collapse-${taskNumber}`}
                aria-expanded='true'
                aria-controls={`collapse-${taskNumber}`}
                data-ga-category='Columns Block'
                data-ga-action='Title'
                data-ga-label='n/a'
              >
                <span
                  className='step-number'
                  data-ga-category='Columns Block'
                  data-ga-action='Task Number'
                  data-ga-label='n/a'
                >
                  {index + 1}
                </span>
                {title}
              </a>

              <div
                id={`collapse-${taskNumber}`}
                className={`collapse ${no_of_columns <= 2 || index === 0 ? 'show' : ''}`}
                data-bs-parent='#accordion' role='tabpanel'
                aria-labelledby={`heading-${taskNumber}`}
              >
                <div className='card-block info-with-image-wrap clearfix'>
                  <div className='mobile-accordion-info'>
                    {description &&
                      <p dangerouslySetInnerHTML={{ __html: description }} />
                    }
                  </div>
                  {attachment &&
                    <img src={attachment} alt='' />
                  }
                  {cta_text && cta_link &&
                    <a
                      className={`btn btn-small btn-${isCampaign ? 'primary' : 'secondary'}`}
                      href={cta_link}
                      data-ga-category='Columns Block'
                      data-ga-action='Call to Action'
                      data-ga-label={cta_link}
                      target={link_new_tab ? '_blank' : ''}
                    >
                      {cta_text}
                    </a>
                  }
                </div>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  </>
);
