import { Covers, getCoversClassName } from './Covers';
import { useCovers } from './useCovers';

export const CoversFrontend = attributes => {
  const { initialRowsLimit, cover_type, title, description, covers } = attributes;
  const blockClassName = getCoversClassName(cover_type);

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
    <section className={blockClassName}>
      {title &&
        <h2 class="page-section-header" dangerouslySetInnerHTML={{ __html: title }} />
      }
      {description &&
        <div class="page-section-description" dangerouslySetInnerHTML={{ __html: description }} />
      }
      <Covers {...coversProps} />
    </section>
  );
}

