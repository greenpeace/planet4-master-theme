$ = jQuery;

// convert an element to slider using slick js
function slickify(element) {
    $(element).slick({
        infinite:       false,
        mobileFirst:    true,
        slidesToShow:   2.2,
        slidesToScroll: 1,
        arrows:         false,
        dots:           false,
        responsive: [
            {
                breakpoint: 992,
                settings: { slidesToShow: 4 }
            },
            {
                breakpoint: 768,
                settings: { slidesToShow: 3 }
            },
            {
                breakpoint: 576,
                settings: { slidesToShow: 2 }
            }
        ]
    });
}

$(document).ready(function () {

    // Add click event for load more button in blocks.
    var load_more_btn_selector = '.btn-load-more-click';
    $(load_more_btn_selector).off('click').on('click', function () {
        var $row = $('.row-hidden', $(load_more_btn_selector).closest('.container'));

        if (1 === $row.size()) {
            $(load_more_btn_selector).closest('.load-more-button-div').hide('fast');
        }

        var row_id = $row.attr('id');
        if (row_id !== undefined && row_id.indexOf("publications-row") !== -1) {
            $row.first().removeClass('row-hidden').show("slow", function () {
                slickify("#" + row_id);
            });
        }
        else {
            $row.first().show('fast').removeClass('row-hidden');
        }
    });
});

