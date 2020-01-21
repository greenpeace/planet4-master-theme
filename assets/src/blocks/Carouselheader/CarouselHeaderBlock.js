import {CarouselHeader} from './CarouselHeader.js';

export class CarouselHeaderBlock {
  constructor() {
    const {registerBlockType} = wp.blocks;

    registerBlockType('planet4-blocks/carousel-header', {
      title: 'Carousel Header',
      icon: 'welcome-widgets-menus',
      category: 'planet4-blocks',
      supports: {
        multiple: false, // Use the block just once per post.
      },
      /**
       * Transforms old 'shortcake' shortcode to new gutenberg block.
       *
       * old block-shortcode:
       * [shortcake_carousel_header block_style="full-width-classic" carousel_autoplay="true" image_1="16" focus_image_1="center center"
       *     header_1="Carousel header - full width  1" description_1="Lorem ipsum " link_text_1="Curabitur rutrum viverra" image_2="348"
       *     focus_image_2="center center"
       *     header_2="Carousel header - full width  2"
       *     description_2="Pellentesque cursus" link_text_2="Pellentesque cursus"
       *     image_3="357" focus_image_3="left top" header_3="Carousel header - full width  3"
       *     description_3="Nam condimentum" focus_image_4="left top"
       * /]
       *
       * new block-gutenberg:
       * <!-- wp:planet4-blocks/carousel-header {"carousel_autoplay":"true","slides":[{"image":16,"header":"Carousel header - full width  1","description":"Lorem ipsum","link_text":"Curabitur rutrum viverra","focal_points":{"x":0.5,"y":0.5}},{"image":348,"header":"Carousel header - full width  2","description":"Pellentesque cursus","link_text":"Pellentesque cursus","focal_points":{"x":0.5,"y":0.5}},{"image":357,"header":"Carousel header - full width  3","description":"Nam condimentum","focal_points":{"x":0,"y":0}}]} /-->
       *
       */
      transforms: {
        from: [
          {
            type: 'shortcode',
            // Shortcode tag can also be an array of shortcode aliases
            // This `shortcode` definition will be used as a callback,
            // it is a function which expects an object with at least
            // a `named` key with `cover_type` property whose default value is 1.
            // See: https://simonsmith.io/destructuring-objects-as-function-parameters-in-es6
            tag: 'shortcake_carousel_header',
            attributes: {
              block_style: {
                type: 'string',
                shortcode: function (attributes) {
                  return Number(attributes.named.block_style);
                }
              },
              carousel_autoplay: {
                type: 'boolean',
                shortcode: function (attributes) {
                  return attributes.named.carousel_autoplay;
                }
              },
              slides: {
                type: 'array',
                shortcode: function (attributes) {

                  const convert_position = function(position) {
                    switch (position) {
                    case 'left top':
                      return {x: 0, y: 0};
                    case 'center top':
                      return {x: 0.5, y: 0};
                    case 'right top':
                      return {x: 1, y: 0};
                    case 'left center':
                      return {x: 0, y: 0.5};
                    case 'center center':
                      return {x: 0.5, y: 0.5};
                    case 'right center':
                      return {x: 1, y: 0.5};
                    case 'left bottom':
                      return {x: 0, y: 1};
                    case 'center bottom':
                      return {x: 0.5, y: 1};
                    case 'right bottom':
                      return {x: 1, y: 1};
                    }
                  };


                  let slides = [];
                  if (attributes.named.image_1) {
                    let slide = {
                      image: Number(attributes.named.image_1),
                      header: attributes.named.header_1,
                      header_size: attributes.named.header_size_1,
                      subheader: attributes.named.subheader_1,
                      description: attributes.named.description_1,
                      link_text: attributes.named.link_text_1,
                      link_url: attributes.named.link_url_1,
                      focal_points: convert_position(attributes.named.focus_image_1)
                    };
                    slides.push(Object.assign({}, slide));

                    if (attributes.named.image_2) {
                      let slide = {
                        image: Number(attributes.named.image_2),
                        header: attributes.named.header_2,
                        header_size: attributes.named.header_size_2,
                        subheader: attributes.named.subheader_2,
                        description: attributes.named.description_2,
                        link_text: attributes.named.link_text_2,
                        link_url: attributes.named.link_url_2,
                        focal_points: convert_position(attributes.named.focus_image_2)
                      };
                      slides.push(Object.assign({}, slide));

                      if (attributes.named.image_3) {
                        let slide = {
                          image: Number(attributes.named.image_3),
                          header: attributes.named.header_3,
                          header_size: attributes.named.header_size_3,
                          subheader: attributes.named.subheader_3,
                          description: attributes.named.description_3,
                          link_text: attributes.named.link_text_3,
                          link_url: attributes.named.link_url_3,
                          focal_points: convert_position(attributes.named.focus_image_3)
                        };
                        slides.push(Object.assign({}, slide));

                        if (attributes.named.image_4) {
                          let slide = {
                            image: Number(attributes.named.image_4),
                            header: attributes.named.header_4,
                            header_size: attributes.named.header_size_4,
                            subheader: attributes.named.subheader_4,
                            description: attributes.named.description_4,
                            link_text: attributes.named.link_text_4,
                            link_url: attributes.named.link_url_4,
                            focal_points: convert_position(attributes.named.focus_image_4)
                          };
                          slides.push(Object.assign({}, slide));

                        }
                      }
                    }
                  }
                  return slides;
                }
              }
            }
          }
        ]
      },
      attributes: {
        block_style: {
          type: 'string',
        },
        carousel_autoplay: {
          type: 'boolean',
        },
        slides: {
          type: 'array',
          default: [
            {
              image: null,
              focal_points: {},
              header: '',
              header_size: 'h1',
              subheader: '',
              description: '',
              link_text: '',
              link_url: '',
              link_url_new_tab: false,
            }
          ]
        },
      },
      edit: ({isSelected, attributes, setAttributes}) => {

        function addSlide() {
          setAttributes({
            slides: attributes.slides.concat({
              image: null,
              focal_points: {},
              header: '',
              header_size: 'h1',
              subheader: '',
              description: '',
              link_text: '',
              link_url: '',
              link_url_new_tab: false,
            })
          });
        }

        function onCarouselAutoplayChange(value) {
          setAttributes({carousel_autoplay: value});
        }

        function onTitleChange(value) {
          setAttributes({title: value});
        }

        function onImageChange(index, value) {
          let slides = JSON.parse(JSON.stringify(attributes.slides));
          if (null !== value) {
            slides[index].image = value;
          } else {
            slides[index].image = null;
          }
          setAttributes({slides: slides});
        }

        function onFocalPointsChange(index, value) {
          let slides = JSON.parse(JSON.stringify(attributes.slides));
          if (null !== value) {
            const fo = JSON.parse(JSON.stringify(value));
            slides[index].focal_points = fo;
          } else {
            slides[index].focal_points = null;
          }
          setAttributes({slides: slides});
        }

        function onHeaderChange(index, value) {
          let slides = JSON.parse(JSON.stringify(attributes.slides));
          slides[index].header = value;
          setAttributes({slides: slides});
        }

        function onHeaderSizeChange(index, value) {
          let slides = JSON.parse(JSON.stringify(attributes.slides));
          slides[index].header_size = value;
          setAttributes({slides: slides});
        }

        function onSubheaderChange(index, value) {
          let slides = JSON.parse(JSON.stringify(attributes.slides));
          slides[index].subheader = value;
          setAttributes({slides: slides});
        }

        function onDescriptionChange(index, value) {
          let slides = JSON.parse(JSON.stringify(attributes.slides));
          slides[index].description = value;
          setAttributes({slides: slides});
        }

        function onLinkUrlChange(index, value) {
          let slides = JSON.parse(JSON.stringify(attributes.slides));
          slides[index].link_url = value;
          setAttributes({slides: slides});
        }

        function onLinkTextChange(index, value) {
          let slides = JSON.parse(JSON.stringify(attributes.slides));
          slides[index].link_text = value;
          setAttributes({slides: slides});
        }

        function onLinkNewTabChange(index, value) {
          let slides = JSON.parse(JSON.stringify(attributes.slides));
          slides[index].link_url_new_tab = value;
          setAttributes({slides: slides});
        }

        function onBlockStyleChange(value) {
          setAttributes({block_style: value});
        }

        function removeSlide() {
          setAttributes({slides: attributes.slides.slice(0, -1)});
        }

        return <CarouselHeader
          {...attributes}
          isSelected={isSelected}
          onBlockStyleChange={onBlockStyleChange}
          onCarouselAutoplayChange={onCarouselAutoplayChange}
          onTitleChange={onTitleChange}
          onImageChange={onImageChange}
          onHeaderChange={onHeaderChange}
          onSubheaderChange={onSubheaderChange}
          onHeaderSizeChange={onHeaderSizeChange}
          onDescriptionChange={onDescriptionChange}
          onLinkTextChange={onLinkTextChange}
          onLinkUrlChange={onLinkUrlChange}
          onLinkNewTabChange={onLinkNewTabChange}
          onFocalPointsChange={onFocalPointsChange}
          addSlide={addSlide}
          removeSlide={removeSlide}
        />;
      },
      save() {
        return null;
      }
    });
  };
}

