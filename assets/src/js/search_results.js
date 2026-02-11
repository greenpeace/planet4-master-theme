import {createRoot} from 'react-dom/client';

const {useEffect} = wp.element;

export default function SearchController({restUrl}) {
  useEffect(() => {
    const form = document.getElementById('search_form_inner');
    const results = document.getElementById('search-results');

    if (!form || !results) {return;}

    const wrapper = document.getElementById('search-results-wrapper');

    const onSubmit = async e => {
      e.preventDefault();

      const params = new URLSearchParams(new FormData(form));
      const url = `${restUrl}planet4/v1/search?${params.toString()}`;

      wrapper.setAttribute('aria-busy', 'true');

      const res = await fetch(url, {
        headers: {'X-Requested-With': 'XMLHttpRequest'},
      });

      const html = await res.text();
      results.innerHTML = html;

      history.pushState({}, '', `?${params.toString()}`);
      wrapper.setAttribute('aria-busy', 'false');
    };

    form.addEventListener('submit', onSubmit);

    return () => form.removeEventListener('submit', onSubmit);
  }, [restUrl]);

  return null;
}


const el = document.getElementById('search-controller');

if (el) {
  createRoot(el).render(
    <SearchController restUrl={el.dataset.restUrl} />
  );
}
