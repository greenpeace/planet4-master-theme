import {ColumnsImagePlaceholder} from './ColumnsImagePlaceholder';
import {LAYOUT_NO_IMAGE, LAYOUT_ICONS, LAYOUT_TASKS} from './ColumnConstants';
import {ColumnsTasks} from './ColumnsTasks';
import {IMAGE_SIZES} from './imageSizes';

export const Columns = ({columns, columns_block_style, isCampaign, isExample = false}) => {
  if (!columns || !columns.length) {
    return null;
  }

  if (columns_block_style === LAYOUT_TASKS) {
    return <ColumnsTasks columns={columns} isCampaign={isCampaign} />;
  }

  return (
    <div className="row">
      {columns.map((column, index) => {
        const {
          cta_link,
          cta_text,
          attachment,
          attachment_srcset,
          link_new_tab,
          title,
          description,
        } = column;
        const titleAnalytics = {
          'data-ga-category': 'Columns Block',
          'data-ga-action': 'Title',
          'data-ga-label': 'n/a',
        };

        const hasImage = attachment !== 0 && attachment !== undefined;
        const columnsWidth = columns_block_style !== LAYOUT_ICONS ? '100%' : 100;

        const columnsHeight = columns_block_style !== LAYOUT_ICONS ? 150 : 100;

        return (
          <div key={`column-${index}`} className="col-md-6 col-lg column-wrap">
            {(hasImage || isExample) && columns_block_style !== LAYOUT_NO_IMAGE &&
              <div className="attachment-container">
                { isExample &&
                  <ColumnsImagePlaceholder
                    width={columnsWidth}
                    height={columnsHeight}
                  />
                }

                { (!isExample && cta_link) ?
                  <a
                    href={cta_link}
                    data-ga-category="Columns Block"
                    data-ga-action={columns_block_style === LAYOUT_ICONS ? 'Icon' : 'Image'}
                    data-ga-label={cta_link}
                    {...link_new_tab && {target: '_blank', rel: 'noreferrer'}}
                  >
                    <img src={attachment}
                      srcSet={attachment_srcset || null}
                      sizes={IMAGE_SIZES[`col-${columns.length}`] ?? ''}
                      alt={title}
                      title={title}
                      loading="lazy" />
                  </a> :
                  <img src={attachment}
                    srcSet={attachment_srcset || null}
                    sizes={IMAGE_SIZES[`col-${columns.length}`] ?? ''}
                    alt={title}
                    title={title}
                    loading="lazy" />
                }
              </div>
            }
            <h3 {...isCampaign ? titleAnalytics : {}} >
              {cta_link && !isCampaign ?
                <a
                  href={cta_link}
                  data-ga-category="Columns Block"
                  data-ga-action="Title"
                  data-ga-label={cta_link}
                  {...link_new_tab && {target: '_blank', rel: 'noreferrer'}}
                >
                  {title}
                </a> :
                title
              }
            </h3>
            {description &&
              <p dangerouslySetInnerHTML={{__html: description}} />
            }
            {cta_text && cta_link &&
              <a
                href={cta_link}
                className={isCampaign || columns_block_style === LAYOUT_NO_IMAGE ?
                  `btn btn-${isCampaign ? 'primary' : 'secondary'}` :
                  'standalone-link'}
                data-ga-category="Columns Block"
                data-ga-action="Call to Action"
                data-ga-label={cta_link}
                {...link_new_tab && {target: '_blank', rel: 'noreferrer'}}
              >
                {cta_text}
              </a>
            }
          </div>
        );
      })}
    </div>
  );
};

