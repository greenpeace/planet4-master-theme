import classNames from 'classnames';
import { toSrcSet } from './sizeFunctions';

export const ArchivePickerList = ({
  isSelected,
  toggleSingleSelection,
  toggleMultiSelection,
  images
}) => {

  return !images ? '' : images.map(image => {
    const {
      id,
      sizes,
      title,
      alt,
      wordpress_id,
      original,
    } = image;

    try {

      return <li
        key={id}
        data-wordpress-id={wordpress_id}>
        <img
          className={classNames({ 'picker-selected': isSelected(image) })}
          srcSet={toSrcSet(sizes, { maxWidth: 900 })}
          title={`${title}`}
          alt={alt}
          width={200 * (original.width / original.height)}
          height={200}
          onClick={(event) =>
            (event.ctrlKey || event.metaKey) // metaKey for Mac users
              ? toggleMultiSelection(image)
              : toggleSingleSelection(image)
          }
        />
      </li>;
    } catch (exception) {
      return <li
        key={id}
      >
        <span>{image.title}</span>
        <span>No image available.</span>
      </li>;
    }
  });
};
