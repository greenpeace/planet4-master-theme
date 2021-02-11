import { CampaignCovers } from './CampaignCovers';
import { ContentCovers } from './ContentCovers';
import { TakeActionCovers } from './TakeActionCovers';

export const COVER_TYPES = {
  takeAction: 'take-action',
  campaign: 'campaign',
  content: 'content'
};

export const getCoversClassName = cover_type => {
  const isTakeActionType = cover_type === COVER_TYPES.takeAction;

  const blockClassName = isTakeActionType ?
    'covers-block' :
    `${cover_type}-covers-block`;

  return `block ${blockClassName}`;
};

export const Covers = ({ cover_type, ...props }) => {
  switch (cover_type) {
    case COVER_TYPES.content:
      return <ContentCovers {...props} />;
    case COVER_TYPES.campaign:
      return <CampaignCovers {...props} />;
    case COVER_TYPES.takeAction:
      return <TakeActionCovers {...props} />;
    default:
      return null;
  }
}
