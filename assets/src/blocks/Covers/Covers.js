import {ContentCovers} from './ContentCovers';
import {TakeActionCovers} from './TakeActionCovers';
import {COVERS_TYPES} from './CoversConstants';

export const Covers = ({cover_type, ...props}) => {
  switch (cover_type) {
  case COVERS_TYPES.content:
    return <ContentCovers {...props} />;
  case COVERS_TYPES.takeAction:
    return <TakeActionCovers {...props} />;
  default:
    return null;
  }
};
