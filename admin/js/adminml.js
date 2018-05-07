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

    $('.ml-search').on('keyup', function() {
        if (this.value.length > 3) {
            var reset_page = 1;
            $(this).data( 'current_page', reset_page );

            $.ajax({
                url: ajaxurl,
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
});