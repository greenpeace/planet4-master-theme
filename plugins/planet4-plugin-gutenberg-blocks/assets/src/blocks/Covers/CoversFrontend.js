import { Covers } from './Covers';
import { useCovers } from './useCovers';

export const CoversFrontend = attributes => {
  const { initialRowsLimit, cover_type, title, description, covers, className } = attributes;

  const { loadMoreCovers, row } = useCovers(attributes, true);

  const coversProps = {
    covers,
    initialRowsLimit,
    row,
    loadMoreCovers,
    cover_type,
  };

  if (!covers.length) {
    return null;
  }

  return (
    <section className={`block covers-block ${cover_type}-covers-block ${className ?? ''}`}>
      {title &&
        <h2 className='page-section-header' dangerouslySetInnerHTML={{ __html: title }} />
      }
      {description &&
        <div className='page-section-description' dangerouslySetInnerHTML={{ __html: description }} />
      }
      <Covers {...coversProps} />
    </section>
  );
}

