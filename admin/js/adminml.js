var $ = jQuery;

jQuery(document).ready(function () {

    $("#selectable-images").selectable({
        selected: function (event, ui) {
            $('#ml-button-insert').removeAttr("disabled");
            var count = $("li.ui-selected", "#selectable-images").length;
            $('#images_count').html(count);
        },
        unselected: function (event, ui) {
            var count = $("li.ui-selected", "#selectable-images").length;
            $('#images_count').html(count);
            if (count < 1) {
                $('#ml-button-insert').attr("disabled", "disabled");
            }
        }
    });

    // Add click event for clear selected images button.
    $("#clear_images").on('click', function () {
        $('#ml-button-insert').attr("disabled", true);
        $("#selectable-images li").removeClass('ui-selected');
        $('#images_count').html('0');
    });


    // Add click event for media insert button.
    $('#ml-button-insert').off('click').on('click', function () {

        var selected_images = $(".ui-selected").map(function (index, element) {
            return $(element).data('id');
        }).get();

        $('#ml_loader').removeClass('hidden');
        var nonce = media_library_params.nonce;
        $.ajax({
            url: media_library_params.ajaxurl,
            type: 'GET',
            data: {
                action: 'download_images_from_library',
                nonce: nonce,
                images: selected_images
            },
            dataType: 'html'
        }).done(function (response) {

            try {
                resp = JSON.parse(response);
                wp = parent.wp;

                if ('undefined' !== resp.images) {

                    var promises = [];
                    for (len = resp.images.length, i = 0; i < len; ++i) {
                        var image = resp.images[i];

                        options = {
                            id: image.wordpress_id,
                            'image-size': 'full',
                            post_content: image.caption,
                            post_excerpt: image.caption,
                        };

                        promises.push(wp.media.post('send-attachment-to-editor', {
                            nonce: wp.media.view.settings.nonce.sendToEditor,
                            attachment: options,
                            html: '',
                            post_id: $("#post_ID").val()
                        }));
                    }
                    //TODO handle promises results/errors better.
                    Promise.all(promises).then(function (values) {
                        parent.send_to_editor(values.join(' '));
                    });
                }
            } catch (e) {
            }
            $('#ml_loader').addClass('hidden');

        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(errorThrown); //eslint-disable-line no-console
            $('#ml_loader').addClass('hidden');
        });
    });

    // Search media library images.
    $('.ml-search').on('keyup', function() {
        if (this.value.length > 3) {
            var reset_page = 1;
            $(this).data( 'current_page', reset_page );

            $.ajax({
                url: media_library_params.ajaxurl,
                type: 'GET',
                data: {
                    action:          'get_paged_medias',
                    'search-action': 'get_searched_medias',
                    'paged':         reset_page,
                    'query-string':  $( this ).val()
                },
                dataType: 'html'
            }).done(function ( response ) {
                // Show the search query response.
                $( '.ml-media-list' ).html( response );
            }).fail(function ( jqXHR, textStatus, errorThrown ) {
                console.log(errorThrown); //eslint-disable-line no-console
            });
        }
    });

    var $load_more_button = $( '.btn-load-more-click-scroll' );
    var load_more_count   = 0;
    var loaded_more       = false;

    // Add click event for load more button in popup.
    $load_more_button.off( 'click' ).on( 'click', function() {
        var next_page = $(this).data( 'current_page' ) + 1;
        $(this).data( 'current_page', next_page );
        $('.spinner').addClass( 'is-active' );
        $load_more_button.hide();

        $.ajax({
            url: media_library_params.ajaxurl,
            type: 'GET',
            data: {
                action:          'get_paged_medias',
                'search-action': 'get_paged_medias',
                'paged':         next_page,
                'query-string':  $( '.ml-search' ).val()
            },
            dataType: 'html'
        }).done(function ( response ) {
            $('.spinner').removeClass( 'is-active' );
            $load_more_button.show();
            // Append the response at the bottom of the results.
            $( '.ml-media-list' ).append( response );
        }).fail(function ( jqXHR, textStatus, errorThrown ) {
            console.log(errorThrown); //eslint-disable-line no-console
        });
    });

    // Reveal more results just by scrolling down the first 2 times.
    $( window ).scroll(function() {
        if ($load_more_button.length > 0) {
            var element_top    = $load_more_button.offset().top,
                element_height = $load_more_button.outerHeight(),
                window_height  = $(window).height(),
                window_scroll  = $(this).scrollTop(),
                load_earlier_offset = 250;

            if ( load_more_count < media_library_params.show_scroll_times ) {
                // If next page has not loaded then load next page as soon as scrolling
                // reaches 'load_earlier_offset' pixels before the Load more button.
                if ( ! loaded_more && window_scroll > (element_top + element_height - window_height - load_earlier_offset)) {
                    load_more_count++;
                    $load_more_button.click();
                    loaded_more = true;

                    // Add a throttle to avoid multiple scroll events from firing together.
                    setTimeout(function () {
                        loaded_more = false;
                    }, 500);
                }
            }
            return false;
        }
    });
});
