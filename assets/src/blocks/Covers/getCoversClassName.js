import { COVER_TYPES } from './Covers';

export const getCoversClassName = (cover_type, initialRowsLimit) => {
  const isContentType = cover_type === COVER_TYPES.content;
  const isTakeActionType = cover_type === COVER_TYPES.takeAction;

  let rowClassName = isContentType ? 'show-all-rows' : 'show-all-covers';
  if (initialRowsLimit == 1) {
    rowClassName = isContentType ? 'show-1-row' : 'show-3-covers';
  } else if (initialRowsLimit === 2) {
    rowClassName = isContentType ? 'show-2-rows' : 'show-6-covers';
  }

  const blockClassName = isTakeActionType ?
    'covers-block' :
    `${cover_type}-covers-block`;

  return `block ${blockClassName} ${rowClassName}`;
};
