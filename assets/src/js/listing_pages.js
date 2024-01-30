import {createRoot} from '@wordpress/element';
import ListingPage from './Components/ListingPage/ListingPage';

export const setupListingPages = () => {
  const rootElement = document.getElementById('listing-page-root');

  createRoot(rootElement).render(<ListingPage />);
};
