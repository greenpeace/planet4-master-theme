jQuery(function ($) {

  let $all_oembed_videos = $( 'iframe[src*="youtube"]' );

  //Remove height and with from imbed iframes and wrap im container
  $all_oembed_videos.each(function() {
    $(this).removeAttr('height').removeAttr('width').wrap( '<div class="embed-container"></div>' );
  });

});