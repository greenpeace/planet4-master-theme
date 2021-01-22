import { CampaignCovers } from './CampaignCovers';
import { ContentCovers } from './ContentCovers';
import { TakeActionCovers } from './TakeActionCovers';
import { useCovers } from './useCovers';

export const COVER_TYPES = {
  takeAction: '1',
  campaign: '2',
  content: '3'
};

export const Covers = attributes => {
  const { covers, loading } = useCovers(attributes, document.body.dataset.nro);
  const { covers_view, cover_type } = attributes;

  if (loading) {
    return null;
  }

  const coversProps = {
    covers,
    covers_view,
  };

  if (cover_type === COVER_TYPES.content) {
    return <ContentCovers {...coversProps} />;
  } else if (cover_type === COVER_TYPES.campaign) {
    return <CampaignCovers {...coversProps} />;
  } else if (cover_type === COVER_TYPES.takeAction) {
    return <TakeActionCovers {...coversProps} />;
  } else {
    return null;
  }
}
