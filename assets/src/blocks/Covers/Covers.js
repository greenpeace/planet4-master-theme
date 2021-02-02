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
  const { covers, loading, loadMoreCovers, row } = useCovers(attributes, document.body.dataset.nro);
  const { covers_view, cover_type, isEditing } = attributes;

  if (loading) {
    return null;
  }

  if (!loading && !covers.length && isEditing) {
    return (
      <div className='EmptyMessage'>
        {__(`Block content is empty. Check the block's settings or remove it.`, 'planet4-blocks-backend')}
      </div>
    )
  }

  const coversProps = {
    covers,
    covers_view,
    row,
    loadMoreCovers,
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
