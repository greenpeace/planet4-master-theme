import { COVER_TYPES } from './Covers';

export const getCoversClassName = (cover_type, covers_view) => {
  const isContentType = cover_type === COVER_TYPES.content;
  const isTakeActionType = cover_type === COVER_TYPES.takeAction;

  let rowClassName = isContentType ? 'show-all-rows' : 'show-all-covers';
  if (covers_view == '1') {
    rowClassName = isContentType ? 'show-1-row' : 'show-3-covers';
  } else if (covers_view === '2') {
    rowClassName = isContentType ? 'show-2-rows' : 'show-6-covers';
  } else if (!covers_view) {
    rowClassName = '';
  }

  const blockClassName = isTakeActionType ?
    'covers-block' :
    `${isContentType ? 'content' : 'campaign'}-covers-block`;

  return `block ${blockClassName} ${rowClassName}`;
};
