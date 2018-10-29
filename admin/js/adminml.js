var $ = jQuery;

jQuery(document).ready(function () {

    $( '#selectable-images' ).selectable({
        selected: function (event, ui) {
            $( '#ml-button-insert' ).removeAttr( 'disabled' );
            var count = $( 'li.ui-selected', '#selectable-images' ).length;
            $( '#images_count' ).html(count);
        },
        unselected: function (event, ui) {
            var count = $( 'li.ui-selected', '#selectable-images' ).length;
            $( '#images_count' ).html(count);
            if (count < 1) {
                $( '#ml-button-insert' ).attr( 'disabled', 'disabled' );
                $( '.ml-media-sidebar' ).hide();
            }
        }
    });

    // Add click event for clear selected images button.
    $( '#clear_images' ).on('click', function () {
        $( '#ml-button-insert' ).attr('disabled', true);
        $( '#selectable-images li' ).removeClass('ui-selected');
        $( '#images_count' ).html('0');
        // On clear image click, hide attachement details panel.
        $( '.ml-media-sidebar' ).hide();
    });


    // Add click event for media insert button.
    $(document).off('click').on('click', '#ml-button-insert', function () {

        var selected_images = $( '.ui-selected' ).map(function (index, element) {
            return $( element ).data( 'id' );
        }).get();

        $( '#ml_spinner' ).addClass('is-active');
        var nonce = media_library_params.nonce;
        // media_details_flag value (1 = Default Title & Description, 2 = Original language Title & Description).
        var media_details_flag = $( '.media_details_flag:checked' ).val();

        $.ajax({
            url: media_library_params.ajaxurl,
            type: 'GET',
            data: {
                action: 'download_images_from_library',
                nonce: nonce,
                images: selected_images,
                media_details_flag: media_details_flag
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

                        if (1 === parseInt(media_details_flag)) {
                            var alt_text   = '.' === image.title.substr(-1)  ? image.title : image.title + '.';
                            var media_desc = image.caption;
                        } else {
                            var alt_text   = '.' === image.original_language_title.substr(-1)  ? image.original_language_title : image.original_language_title + '.';
                            var media_desc = image.original_language_description;
                        }

                        // Add credit to alt text.
                        alt_text = '' !== image.credit ? alt_text + ' © ' + image.credit : alt_text;

                        options = {
                            id: image.wordpress_id,
                            'image-size': 'full',
                            post_content: media_desc,
                            post_excerpt: media_desc,
                            image_alt: alt_text,
                        };

                        // If this page contains a wp editor then insert the selected image inside the editor.
                        if ( "function" === typeof parent.send_to_editor ) {
                            promises.push(wp.media.post('send-attachment-to-editor', {
                                nonce: wp.media.view.settings.nonce.sendToEditor,
                                attachment: options,
                                html: '',
                                post_id: wp.media.view.settings.post.id
                            }));
                        }
                    }
                    //TODO handle promises results/errors better.
                    Promise.all(promises).then(function (values) {
                        parent.send_to_editor(values.join(' '));
                    });
                }
            } catch (e) {
            }
            $( '#ml_spinner' ).removeClass('is-active');
            // If this is not a page with a wp editor, then move to the Media Library page.
            if ( "function" !== typeof parent.send_to_editor ) {
                parent.window.location.replace('/wp-admin/upload.php');
            }

        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(errorThrown); //eslint-disable-line no-console
            $( '#ml_spinner' ).removeClass('is-active');
        });
    });

    // Search media library images.
    $('.ml-search').on('keyup', function() {
        if (this.value.length > 3) {
            var reset_page = 1;
            scroll_more = 0;
            $( '#ml_current_page' ).val( reset_page );

            $.ajax({
                url: media_library_params.ajaxurl,
                type: 'GET',
                data: {
                    action:          'get_paged_medias',
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

    $( '.media_details_flag' ).change(function () {
        var media_details_flag = $(this).val(); // media_details_flag value (1 = Default Title & Description, 2 = Original language Title & Description).
        var media_id = $( '.ml-media-id' ).val();
        var elObj = $( 'li[data-id=' + media_id + ']' );
        if (1 === parseInt( media_details_flag )) {
            $( '.ml-title' ).val( elObj.find('#ml-title').val() );
            $( '.ml-caption' ).val( elObj.find('#ml-caption').val() );
            $( '.ml-alt' ).val( elObj.find('#ml-alt').val() );
            $( '.ml-description' ).val( elObj.find('#ml-description').val() );
        } else {
            $( '.ml-title' ).val( elObj.find('#ml-ori-lang-title').val() );
            $( '.ml-caption' ).val( elObj.find('#ml-ori-lang-desc').val() );
            $( '.ml-alt' ).val( elObj.find('#ml-ori-lang-title').val() );
            $( '.ml-description' ).val( elObj.find('#ml-ori-lang-desc').val() );
        }
    });
});

// Get file name from full url/path.
String.prototype.filename = function( extension ) {
    var filename = this.replace(/\\/g, '/');
    filename     = filename.substring( filename.lastIndexOf('/') + 1 );
    return extension ? filename.replace(/[?#].+$/, '') : filename.split('.')[0];
}

// Add click event for image selection
function select_image( elObj ) {
    $( '.ml-media-sidebar' ).show();

    $( '.ml-image' ).attr('src', $(elObj).find('img').attr('src'));
    $( '.ml-filename' ).html( $(elObj).find('img').attr('src').filename());

    // TO DO : Need to make it dynamic.
    //$( '.ml-file-date' ).html( $(elObj).find('#ml-file-date').val());
    //$( '.ml-file-size' ).html( $(elObj).find('#ml-file-size').val());
    //$( '.ml-file-dimensions' ).html( $(elObj).find('#ml-file-dimensions').val());

    $( '.ml-url' ).val( $(elObj).find('img').attr('src'));
    $( '.ml-title' ).val( $(elObj).find('#ml-title').val());
    $( '.ml-caption' ).val( $(elObj).find('#ml-caption').val());
    $( '.ml-alt' ).val( $(elObj).find('#ml-alt').val());
    $( '.ml-description' ).val( $(elObj).find('#ml-description').val());
    $( '.ml-credit' ).val( $(elObj).find('#ml-credit').val());
    $( '.ml-media-id' ).val( $(elObj).attr('data-id'));

    // Hide/show ML additional fields.
    var ml_additional_fields = $( '.ml-additional-fields' );
    var ml_org_lang_label = $( '.ml-org-lang-label' );
    var ml_restrictions_label = $( '.ml-restrictions-label' );
    var ml_radio_gr = $( '.ml-radio-gr' );
    ml_additional_fields.hide();
    ml_org_lang_label.hide();
    ml_restrictions_label.hide();
    ml_radio_gr.hide();

    if ( $(elObj).find('#ml-ori-lang-title').val() ) {
        var ml_org_lang = '<b>' + $(elObj).find('#ml-ori-lang-title').val() + '</b><br>' + $(elObj).find('#ml-ori-lang-desc').val()
        $( '.ml-org-lang' ).html( ml_org_lang );
        ml_additional_fields.show();
        ml_org_lang_label.show();
        ml_radio_gr.show();
    }

    if ( $(elObj).find('#ml-restrictions').val() ) {
        $( '.ml-restrictions' ).html( $(elObj).find('#ml-restrictions').val());
        ml_additional_fields.show();
        ml_restrictions_label.show();
    }

    $( '.details' ).removeClass('details');
    $( elObj ).addClass('details');
}

var scroll_more = 0;

// Call the function on scroll event.
function scroll_ml_images() {

    if (0 === scroll_more) {

        scroll_more = 1;
        var next_page = parseInt( $( '#ml_current_page' ).val() ) + 1;
        $( '#ml_current_page' ).val( next_page );
        $( '#ml_spinner' ).addClass('is-active');

        $.ajax({
            url: media_library_params.ajaxurl,
            type: 'GET',
            data: {
                action:          'get_paged_medias',
                'paged':         next_page,
                'query-string':  $( '.ml-search' ).val()
            },
            dataType: 'html'
        }).done(function ( response ) {
            $( '#ml_spinner' ).removeClass('is-active');
            // Append the response at the bottom of the results.
            $( '.ml-media-list' ).append( response );

            // Add a throttle to avoid multiple scroll events from firing together.
            setTimeout(function () {
                scroll_more = 0;
            }, 500);
        }).fail(function ( jqXHR, textStatus, errorThrown ) {
            console.log(errorThrown); //eslint-disable-line no-console
            scroll_more = 0;
            $( '#ml_spinner' ).removeClass('is-active');
        });
    }
}
