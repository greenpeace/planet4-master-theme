$(document).ready(function () {
    $(".step-info-wrap").click(function () {
        if ($(this).parent().hasClass('active')) {
            $(this).parent().removeClass('active');
        }
        else {
            $('.col').removeClass('active');
            $(this).parent().addClass('active');
        }
    });
});

function createCookie(name, value, days) {
    console.log('in create cookie method');
    document.cookie = encodeURI(name) + '=' + encodeURI(value) + ';domain=.' + document.domain + ';path=/;';
}

function readCookie(name) {
    console.log('in read cookie method');
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

$(function () {
    cookie = readCookie('greenpeace');
    if (cookie == null) {
        console.log('cookie is not present')
        $(".cookie-block").show();
    } else {
        console.log('cookie is present, cookie banner hidden!');
    }
});

$("#hidecookie").click(function () {
    $(".cookie-block").slideUp("slow");
    createCookie('greenpeace', 'policy-accepted');
});

$('.country-select-dropdown').click(function () {
    $(this).parent().toggleClass('active-li');
    $('.country-select-box').toggle();
});

$('.country-select-box .country-list li').click(function () {
    $(this).parents('.country-select-box').find('li').removeClass('active');
    $(this).addClass('active');
});

// Footer JS goes in this
// ----------- Header JS start ------------------//

$(document).on('click', [
    '.navbar-dropdown-toggle',
    '.country-dropdown-toggle',
    '.navbar-search-toggle',
].join(), function toggleNavDropdown(evt) {
    evt.preventDefault();
    evt.stopPropagation();

    var $button = $(this);
    var target = $button.data('target');
    if (!target) {
        throw new Error('Missing `data-target` attribute: specify the container to be toggled');
    }
    var toggleClass = $button.data('toggle');
    if (!toggleClass) {
        throw new Error('Missing `data-toggle` attribute: specify the class to toggle');
    }

    // Toggle visibility of the target specified via data-target.
    $(target).toggleClass(toggleClass);
    // Toggle aria-expanded attribute.
    $button.attr('aria-expanded', function (i, attr) {
        return attr === 'false' ? 'true' : 'false';
    });
});

$(document).on('click', function closeInactiveMenus(evt) {
    var clickedElement = evt.target;
    $('.btn-navbar-toggle[aria-expanded="true"]').each(function (i, button) {
        var $button = $(button);
        var buttonTarget = $($button.data('target')).get(0);
        if (buttonTarget && !$.contains(buttonTarget, clickedElement)) {
            // Spoof a click on the open menu's toggle to close that menu.
            $button.trigger('click');
        }
    });
});
/**
 * Close the menu if the user clicks the dedicated dropdown close button.
 */
$(document).on('click', '.close-navbar-dropdown', function (evt) {
    evt.preventDefault();
    // Proxy to the main navbar close button
    $('.navbar-dropdown-toggle').trigger('click');
});

// Hide Header on on scroll down
if ($(window).width() <= 768) {
    var didScroll;
    var lastScrollTop = 0;
    var delta = 5;
    var navbarHeight = $('.top-navigation').outerHeight();
    $(window).scroll(function (event) {
        didScroll = true;
    });
    setInterval(function () {
        if (didScroll) {
            hasScrolled();
            didScroll = false;
        }
    }, 250);

    function hasScrolled() {
        var st = $(this).scrollTop();
        if (Math.abs(lastScrollTop - st) <= delta)
            return;
        if (st > lastScrollTop && st > navbarHeight) {
            $('.top-navigation').removeClass('nav-down').addClass('nav-up');
        } else {
            if (st + $(window).height() < $(document).height()) {
                $('.top-navigation').removeClass('nav-up').addClass('nav-down');
            }
        }
        lastScrollTop = st;
    }

    var $slider = $('.mobile-menus');
    $(document).click(function () {
        if ($('.menu').hasClass('active')) {
            //Hide the menus if visible
            $slider.animate({
                left: parseInt($slider.css('left'), 10) == 0 ?
                    -320 : 0
            });
            $('.menu').removeClass('active');
        }
        if ($('.search-box').hasClass('active')) {
            //Hide the search if visible
            $searchBox.slideToggle().toggleClass('active');
            ;
        }
    });

    $('.menu').click(function () {
        event.stopPropagation();
        $(this).toggleClass('active');
        $slider.animate({
            left: parseInt($slider.css('left'), 10) == -320 ?
                0 : -320
        });
    });

    var $searchBox = $('#search .search-box');
    var $searchTrigger = $('#search-trigger');

    $searchTrigger.on('click', function (e) {
        event.stopPropagation();
        $searchBox.slideToggle().toggleClass('active');
    });
}
// ----------- Header JS end ------------------//

// Hide Header on on scroll down
if ($(window).width() <= 768) {
    var didScroll;
    var lastScrollTop = 0;
    var delta = 5;
    var navbarHeight = $('.top-navigation').outerHeight();
    $(window).scroll(function (event) {
        didScroll = true;
    });
    setInterval(function () {
        if (didScroll) {
            hasScrolled();
            didScroll = false;
        }
    }, 250);

    function hasScrolled() {
        var st = $(this).scrollTop();
        if (Math.abs(lastScrollTop - st) <= delta)
            return;
        if (st > lastScrollTop && st > navbarHeight) {
            $('.top-navigation').removeClass('nav-down').addClass('nav-up');
        } else {
            if (st + $(window).height() < $(document).height()) {
                $('.top-navigation').removeClass('nav-up').addClass('nav-down');
            }
        }
        lastScrollTop = st;
    }

    var $slider = $('.mobile-menus');
    $(document).click(function () {
        if ($('.menu').hasClass('active')) {
            //Hide the menus if visible
            $slider.animate({
                left: parseInt($slider.css('left'), 10) == 0 ?
                    -320 : 0
            });
            $('.menu').removeClass('active');
        }
        if ($('.search-box').hasClass('active')) {
            //Hide the search if visible
            $searchBox.slideToggle().toggleClass('active');
            ;
        }
    });

    $('.menu').click(function () {
        event.stopPropagation();
        $(this).toggleClass('active');
        $slider.animate({
            left: parseInt($slider.css('left'), 10) == -320 ?
                0 : -320
        });
    });

    var $searchBox = $('#search .search-box');
    var $searchTrigger = $('#search-trigger');

    $searchTrigger.on('click', function (e) {
        event.stopPropagation();
        $searchBox.slideToggle().toggleClass('active');
    });
}

$(function () {
    $('.publications-slider').slick({
        infinite: false,
        mobileFirst: true,
        slidesToShow: 2.2,
        slidesToScroll: 1,
        arrows: false,
        dots: false,
        responsive: [
            {
                breakpoint: 992,
                settings: {slidesToShow: 4}
            },
            {
                breakpoint: 768,
                settings: {slidesToShow: 3}
            },
            {
                breakpoint: 576,
                settings: {slidesToShow: 2}
            }
        ]
    });
});


$(function () {
    $('#search-type button').click(function () {
        $('#search-type button').removeClass("active");
        $(this).addClass("active");
    });

    $('.btn-filter').click(function () {
        $('#filtermodal').modal('show');
    });
});
// Index for #carousel-wrapper-header
currentIndexheader = $('#carousel-wrapper-header .carousel-item.active').next('.carousel-item').find('img').attr('src');

$('#carousel-wrapper-header').on('slid.bs.carousel', function () {
    currentIndexheader = $('#carousel-wrapper-header .carousel-item.active').next('.carousel-item');
    var e = currentIndexheader.find('img').attr('src');
    // Last Index
    if (e === 'undefined' || e === undefined) {
        currentIndexheader = $('#carousel-wrapper-header .carousel-item').first('.carousel-item').find('img').attr('src');
    } else {
        currentIndexheader = currentIndexheader.find('img').attr('src');
    }
    $('#carousel-wrapper-header a.carousel-control-next').css('background-image', 'url(' + currentIndexheader + ')');

});

$('#carousel-wrapper-header a.carousel-control-next').css('background-image', 'url(' + currentIndexheader + ')');

