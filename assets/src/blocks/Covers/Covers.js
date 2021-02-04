import { CampaignCovers } from './CampaignCovers';
import { ContentCovers } from './ContentCovers';
import { TakeActionCovers } from './TakeActionCovers';

export const COVER_TYPES = {
  takeAction: '1',
  campaign: '2',
  content: '3'
};

export const Covers = props => {
  switch (props.cover_type) {
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
