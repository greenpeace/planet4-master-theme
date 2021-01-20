export const addSlide = (slides, setAttributes) => {
  const newSlides = slides.concat({
    image: null,
    focal_points: {},
    header: '',
    header_size: 'h1',
    description: '',
    link_text: '',
    link_url: '',
    link_url_new_tab: false,
  });
  setAttributes({ slides: newSlides });
}
