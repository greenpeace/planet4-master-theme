
import {createRoot, useEffect, useState} from '@wordpress/element';

(function() {
  const SearchPage = () => {
    const [searchText, setSearchText] = useState('');

    const onChange = evt => {
      evt.preventDefault();
      setSearchText(evt.currentTarget.value);
    };

    useEffect(() => {
      const params = new URL(document.location).searchParams;
      setSearchText(params.get('s'));
    }, []);

    return (
      <section style={{padding: '30px'}}>
        <header style={{display: 'flex', justifyContent: 'space-between'}}>
          <a className="site-logo" href="http://www.planet4.test/">
            <img
              src="http://www.planet4.test/wp-content/themes/planet4-master-theme/images/gp-logo.svg"
              alt="Greenpeace"
              data-ga-category="Menu Navigation"
              data-ga-action="Greenpeace Logo"
              data-ga-label="Homepage"
            />
          </a>
          <label htmlFor="input-search">
            <input
              id="input-search"
              name="input-search"
              style={{width: '400px', height: '50px'}}
              onChange={onChange}
              placeholder="Search"
              value={searchText}
            />
          </label>
        </header>
        <h1 style={{marginTop: '50px', textAlign: 'center'}}>Results for {searchText}</h1>
        <div style={{width: '70%', display: 'flex', justifyContent: 'space-between'}}>
          <input
            style={{width: '100%', height: '50px'}}
            onChange={onChange}
            placeholder="Search"
            value={searchText}
          />
          <button type="submit" className="btn btn-primary search-btn btn-block" data-ga-category="Search Page" data-ga-action="Search Button" data-ga-label="n/a">Search</button>
        </div>
      </section>
    );
  };

  const wrapper = document.createElement('div');
  wrapper.setAttribute('id', 'root');
  const root = createRoot(wrapper);
  document.body.appendChild(wrapper);
  root.render(<SearchPage />);
}());
