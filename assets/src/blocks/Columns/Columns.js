export { LAYOUT_NO_IMAGE, LAYOUT_ICONS, LAYOUT_TASKS } from './ColumnTypes';

export const ColumnsIcons = ({ columns, columns_block_style, isCampaign }) => {
  if (!columns || !columns.length) {
    return null;
  }

  if (columns_block_style === LAYOUT_TASKS) {
    return <ColumnsTasks columns={columns} isCampaign={isCampaign} />
  } else {
    return (
      <div className='row'>
        {columns.map(column => {
          const {
            cta_link,
            cta_text,
            attachment,
            link_new_tab,
            title,
            description,
          } = column;
          const titleAnalytics = {
            'data-ga-category': 'Columns Block',
            'data-ga-action': 'Title',
            'data-ga-label': 'n/a'
          };
          return (
            <div className='col-md-6 col-lg column-wrap'>
              {attachment && columns_block_style !== LAYOUT_NO_IMAGE &&
                <div className='attachment-container'>
                  {cta_link ?
                    <a
                      href={cta_link}
                      target={link_new_tab && link_new_tab !== 'false' ? '_blank' : ''}
                      data-ga-category='Columns Block'
                      data-ga-action={columns_block_style === LAYOUT_ICONS ? 'Icon' : 'Image'}
                      data-ga-label={cta_link}
                    >
                      <img src={attachment} alt={title} />
                    </a> :
                    <img src={attachment} alt={title} />
                  }
                </div>
              }
              <h3 {...isCampaign ? titleAnalytics : {}} >
                {cta_link && !isCampaign ?
                  <a
                    href={cta_link}
                    data-ga-category='Columns Block'
                    data-ga-action='Title'
                    data-ga-label={cta_link}
                  >
                    {title}
                  </a> :
                  title
                }
              </h3>
              {description &&
                <p dangerouslySetInnerHTML={{ __html: description }} />
              }
              {cta_text && cta_link &&
                <a
                  href={cta_link}
                  target={link_new_tab ? '_blank' : ''}
                  className={isCampaign || columns_block_style === LAYOUT_NO_IMAGE ?
                    `btn btn-${isCampaign ? 'primary' : 'secondary'}` :
                    'call-to-action-link'
                  }
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
    );
  }
}
