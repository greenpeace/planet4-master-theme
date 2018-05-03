var $ = jQuery;

jQuery(document).ready(function () {

    $("#selectable-images").selectable({
        selected: function (event, ui) {
            $('#ml-button-insert').removeAttr("disabled");
        },
        unselected: function (event, ui) {
            if ($(".ui-selected", $("#selectable-images")).size === 0) {
                $('#ml-button-insert').attr("disabled", "disabled");
            }
        }
    });

    // Add click event for clear selected images button.
    $("#clear_images").on('click', function () {
        $('#ml-button-insert').attr("disabled", true);
        $("#selectable-images li").removeClass('ui-selected');
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
});