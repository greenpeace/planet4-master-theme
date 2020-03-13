/* global Hammer, LazyLoad */

export const FullWidthCarouselHeader = {
  /**
   * Given an active slide return the next slide, wrapping around the end of the carousel.
   *
   * @param {HTMLElement|jQuery} slide A slide in the carousel.
   * @returns {jQuery} A jQuery selection of the next slide.
   */
  nextSlide: function (el) {
    const $el = $(el);
    const $nextSlide = $el.next('.carousel-item');
    // prevAll returns items in reverse DOM order: the first slide is the last element.
    return $nextSlide.length ? $nextSlide : $el.prevAll('.carousel-item').last();
  },

  previousSlide: function (el) {
    const $el = $(el);
    const $previousSlide = $el.prev('.carousel-item');
    return $previousSlide.length ? $previousSlide : $el.nextAll('.carousel-item').last();
  },

  // Update active slide indicators
  switchIndicator: function (index) {
    this.$CarouselIndicators.children().each(function (i, el) {
      $(el).toggleClass('active', i === index);
    });
  },

  getCurrentSlideIndex: function () {
    return this.$CarouselHeaderWrapper.find('.carousel-item.active').index();
  },

  setup: function () {
    const me = this;

    me.isRTL = $('html').attr('dir') == 'rtl';

    me.$CarouselHeaderWrapper = $('#carousel-wrapper-header');
    me.$Slides = me.$CarouselHeaderWrapper.find('.carousel-item');

    me.$CarouselHeaderWrapper.find('img').on('load', function () {
      const current_img_src = $(this).get(0).currentSrc;
      const current_bg_img = $(this).parent().css('background-image').replace(/.*\s?url\(['"]?/, '').replace(/['"]?\).*/, '');
      if (current_img_src !== current_bg_img) {
        $(this).parent().css('background-image', 'url(' + $(this).get(0).currentSrc + ')');
      }
    });

    me.$CarouselIndicators = me.$CarouselHeaderWrapper.find('.carousel-indicators');

    me.$CarouselIndicators.find('li').remove(); // Empty the indicators list

    me.$Slides.each(function (i, el) {
      const $slide = $(el);

      // Populate carousel indicators list
      $('<li>')
        .attr('data-target', '#carousel-wrapper-header')
        .attr('data-slide-to', i)
        .toggleClass('active', i === 0)
        .appendTo(me.$CarouselIndicators);

      // Convert the provided image tag into background image styles.
      const $img = $slide.find('img');

      // Images after the first slide are preloaded
      // on initialization
      if ($img.hasClass('preload')) {
        if (!window.lazyLoad) {
          window.lazyLoad = new LazyLoad({
            elements_selector: '.lazyload'
          });
        }
        window.lazyLoad.load($img[0]);
      }

      const img_src = $img.get(0).currentSrc || $img.attr('src');
      $slide.find('.background-holder')
        .css('background-image', 'url(' + img_src + ')')
        .css('background-position', $img.data('background-position'));

      // Populate carousel slide index
      $slide.attr('data-slide', i);
    });

    // Bind mouse interaction events
    const clickTargets = ['.carousel-control-next', '.carousel-control-prev'];
    if (me.isRTL) {
      clickTargets.reverse();
    }
    me.$CarouselHeaderWrapper.on('click', clickTargets[0], function (evt) {
      evt.preventDefault();
      me.cancelAutoplayInterval();
      me.slideToNext();
    });

    me.$CarouselHeaderWrapper.on('click', clickTargets[1], function (evt) {
      evt.preventDefault();
      me.cancelAutoplayInterval();
      me.slideToPrevious();
    });

    me.$CarouselHeaderWrapper.on('click', '.carousel-indicators li', function (evt) {
      evt.preventDefault();
      me.cancelAutoplayInterval();
      me.activate($(evt.target).data('slide-to'));
    });

    /* Carousel header swipe on mobile */
    if ($('.carousel-header_full-width-classic').length > 0 && me.$Slides.length > 1) {
      const carousel_element = $('.carousel-header_full-width-classic')[0];
      const carousel_head_hammer = new Hammer(carousel_element, {recognizers: []});
      const hammer = new Hammer.Manager(carousel_head_hammer.element);
      const swipe = new Hammer.Swipe();
      hammer.add(swipe);

      const swipeListeners = [
        function () {
          me.slideToNext();
          me.cancelAutoplayInterval();
        },
        function () {
          me.cancelAutoplayInterval();
          me.slideToPrevious();
        }
      ];

      if (me.isRTL) {
        swipeListeners.reverse();
      }

      hammer.on('swipeleft', swipeListeners[0]);
      hammer.on('swiperight', swipeListeners[1]);

      // Vertical swiping on carousel should scroll the page
      hammer.on('swipeup', function (event) {
        const y = $(window).scrollTop();
        event.preventDefault();
        $('html, body').animate({scrollTop: y + 500});
      });
      hammer.on('swipedown', function (event) {
        const y = $(window).scrollTop();
        event.preventDefault();
        $('html, body').animate({scrollTop: y - 500});
      });
    }

    me.positionIndicators();
    me.setCarouselHeight(me.$Slides.first());

    me.$CarouselHeaderWrapper.find('.initial').on('transitionend', function () {
      $(this).removeClass('initial');
    });

    $(window).on('resize', function () {
      const $currentSlide = $('.carousel-item.active');
      me.setCarouselHeight($currentSlide);
      me.positionIndicators();
    });

    me.autoplayPaused = false;
    me.autoplayEnabled = me.$CarouselHeaderWrapper.data('carousel-autoplay') == true;

    if (me.$Slides.length > 1 && me.autoplayEnabled) {
      me.startAutoplayInterval();

      me.$CarouselHeaderWrapper.on('mouseenter', function () {
        me.autoplayPaused = true;
      });

      me.$CarouselHeaderWrapper.on('mouseleave', function () {
        me.autoplayPaused = false;
      });
    }

    $(window).on('scroll', function () {
      me.cancelAutoplayInterval();
      $(window).off('scroll');
    });
  },

  startAutoplayInterval: function () {
    const me = this;
    me.autoplayInterval = window.setInterval(function () {
      if (!me.autoplayPaused) {
        me.slideToNext();
      }
    }, 6000);
  },

  cancelAutoplayInterval: function () {
    window.clearInterval(this.autoplayInterval);
  },

  /**
   * Switch to a specific slide.
   *
   * @param {Number} slideIndex The index of a slide in the carousel.
   */
  activate: function (slideIndex) {
    const me = this;
    const $slide = me.$Slides.eq(slideIndex);
    const currentIndex = me.getCurrentSlideIndex();

    if (slideIndex == currentIndex) {
      return;
    }

    if (slideIndex > currentIndex) {
      me.slideToNext($slide);
    } else {
      me.slideToPrevious($slide);
    }
  },

  positionIndicators: function () {
    const me = this;
    const $indicators = this.$CarouselHeaderWrapper.find('.carousel-indicators');
    const $header = this.$CarouselHeaderWrapper.find('.carousel-item.active .action-button');
    const isLargeAndUp = window.matchMedia('(min-width: 992px)').matches;
    const isMedium = window.matchMedia('(min-width: 768px) and (max-width: 992px)').matches;
    const isMediumAndUp = window.matchMedia('(min-width: 768px)').matches;
    const rightSide = (isLargeAndUp && me.isRTL) || (isMedium && !me.isRTL);

    if (isMediumAndUp && $header.length) {
      let leftOffset = $header.offset().left;

      if (rightSide) {
        const rightOffset = leftOffset + (me.isRTL ? $header.width() : $header.parent().width());
        const indicatorsRight = $(window).width() - rightOffset;
        $indicators.css('right', indicatorsRight + 'px')
          .css('left', '')
          .css('margin-left', '0')
          .css('margin-right', '3px'); // same as indicators x margin
      } else {
        leftOffset = me.isRTL ? $header.parent().offset().left : $header.offset().left;
        $indicators.css('left', leftOffset + 'px')
          .css('right', '')
          .css('margin-right', '0')
          .css('margin-left', '3px');
      }
    } else {
      $indicators.css('right', '');
      $indicators.css('left', '');
    }
  },

  getSlideHeight: function ($slide) {
    return $slide.find('.carousel-item-mask .background-holder').outerHeight() + $slide.find('.carousel-caption').outerHeight() + 'px';
  },

  setCarouselHeight: function ($currentSlide) {
    const me = this;
    if (window.matchMedia('(max-width: 992px)').matches) {
      me.$CarouselHeaderWrapper.find('.carousel-inner, .carousel-item-mask').css('height', this.getSlideHeight($currentSlide));
    } else {
      me.$CarouselHeaderWrapper.find('.carousel-inner, .carousel-item-mask').css('height', '');
    }
  },

  slideToPrevious: function ($slide) {
    const me = this;
    const $activeSlide = me.$Slides.filter('.active');
    const shrinkClass = me.isRTL ? 'shrink-to-left' : 'shrink-to-right';

    let $previousSlide = null;
    if ($slide) {
      $previousSlide = $slide;
    } else {
      $previousSlide = me.previousSlide($activeSlide);
    }

    $activeSlide.addClass(shrinkClass);
    $previousSlide.addClass('prev');

    function unsetTransitionClasses() {
      $activeSlide.removeClass(`${shrinkClass} active`);
      $previousSlide.addClass('active').removeClass('prev');
      $activeSlide.off('transitionend');
    }

    me.setCarouselHeight($previousSlide);
    $activeSlide.on('transitionend', unsetTransitionClasses);
    me.switchIndicator(me.$Slides.index($previousSlide));
  },

  getActiveSlide: function() {
    return this.$Slides.filter('.active');
  },

  /**
   * Advance to the next slide in the carousel.
   */
  slideToNext: function ($nextSlide = null) {
    const me = this;
    const $activeSlide = me.getActiveSlide();
    const shrinkClass = me.isRTL ? 'shrink-to-right' : 'shrink-to-left';

    if (!$nextSlide) {
      $nextSlide = me.nextSlide($activeSlide);
    }

    $activeSlide.addClass(shrinkClass);
    $nextSlide.addClass('next');

    function unsetTransitionClasses() {
      $activeSlide.removeClass(`${shrinkClass} active`);
      $nextSlide.addClass('active').removeClass('next');
      $activeSlide.off('transitionend');
    }

    $activeSlide.on('transitionend', unsetTransitionClasses);
    $activeSlide.find('.btn').fadeIn();

    me.setCarouselHeight($nextSlide);
    me.switchIndicator(me.$Slides.index($nextSlide));
  },
};
