import { Sidebar } from './ShareButtonsSidebar';
import { ShareButtonsFrontend } from './ShareButtonsFrontend';

export const ShareButtonsEditor = ({
  attributes,
  setAttributes,
}) => (
  <>
    <Sidebar {...{ ...attributes, setAttributes }} />
    <ShareButtonsFrontend {...attributes} />
  </>
)
