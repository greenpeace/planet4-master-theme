/* global dataLayer */

document.addEventListener( 'DOMContentLoaded', () => {
  function getAnalyticsText(item) {
    const headline = item.innerText || item.textContent;
    return headline.length > 50 ? `${headline.substring(0, 50)}...` : headline;
  }

  function handleReadMoreClick(textToSend) {
    dataLayer.push({
      event: 'Read More FAQ',
      Question: textToSend
    });
  }

  function closeItem(item) {
    if (item.classList.contains('open')) {
      // Close the tab (toggle arrow)
      item.classList.remove('open');

      // Hide the corresponding panel
      const panel = item.nextElementSibling;
      panel.classList.add('panel-hidden');
    }
  }

  function openItem(item) {
    if (!item.classList.contains('open')) {
      // Open the tab (toggle arrow)
      item.classList.add('open');

      // Show the corresponding panel
      const panel = item.nextElementSibling;
      panel.classList.remove('panel-hidden');

      // Add button handler to corresponding panel
      const button = [...panel.children].find(child => child.classList.contains('accordion-btn'));

      if (button && !button.onclick) {
        const textToSend = getAnalyticsText(item);
        button.onclick = () => handleReadMoreClick(textToSend);
      }
    }
  }

  function toggleAccordionItem(item, siblings) {
    const textToSend = getAnalyticsText(item);

    if (item.classList.contains('open')) {
      closeItem(item);

      dataLayer.push({
        event: 'Close FAQ',
        Question: textToSend
      });
    } else {
      openItem(item);

      // Close all other items if necessary
      siblings.forEach(sibling => {
        if (sibling !== item) {
          closeItem(sibling);
        }
      });

      dataLayer.push({
        event: 'Expand FAQ',
        Question: textToSend
      });
    }
  }

  // Add necessary handlers to accordion blocks
  const accordionBlocks = [...document.querySelectorAll('.accordion-block')];

  accordionBlocks.forEach(accordion => {
    const accordionItems = [...accordion.children].filter(child => child.classList.contains('accordion-content'));
    const accordionHeadlines = accordionItems.map(item => [...item.children].find(child => child.classList.contains('accordion-headline')));
    accordionHeadlines.forEach(headline => headline.onclick = () => toggleAccordionItem(headline, accordionHeadlines));
  });
});
