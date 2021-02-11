export const setupEnhancedDonateButton = () => {
  const isMobile = () => window.matchMedia('(max-width: 576px)').matches;

  const setupDonateButton = () => {
    const donateButton = document.querySelector('.btn-donate-top');
    if ( isMobile() ) {
      if ( ! donateButton ) {
        const enhancedDonateBtn = document.querySelector('.btn-enhanced-donate').cloneNode(true);
        enhancedDonateBtn.classList.add('btn-donate-top');

        document.querySelector('body').appendChild(enhancedDonateBtn);
        document.querySelector('body').classList.add('with-donate-on-top');
      }
    } else {
      if ( donateButton ) {
        donateButton.parentNode.removeChild(donateButton);
      }

      document.querySelector('body').classList.remove('with-donate-on-top');
    }
  };

  const ready = (fn) => {
    if ( document.readyState !== 'loading' ) {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  };

  ready(() => {

    // Check Show enhanced donate button setting.
    if (!document.querySelector('.btn-enhanced-donate')) {
      return false;
    }

    setupDonateButton();

    window.addEventListener('resize', () => {
      setupDonateButton();
    });

    const windowHeight = window.innerHeight;

    window.addEventListener('scroll', () => {

      const enhancedDonateBtn = document.querySelector('.btn-enhanced-donate.btn-donate-top');

      if ( enhancedDonateBtn ) {
        if ( isMobile() ) {
          enhancedDonateBtn.classList.toggle('btn-donate-top-hide', window.scrollY > windowHeight);
        } else {
          enhancedDonateBtn.classList.remove('btn-donate-top-hide');
        }
      }
    });
  });
};
