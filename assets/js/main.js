jQuery( document ).ready( function( $ ) {

    $(window).scroll(function() {
        if ($(this).scrollTop() > 130){
            $('.fixed-element, .md-navigation').addClass("sticky");
        }
        else{
            $('.fixed-element, .md-navigation').removeClass("sticky");
        }
    });
// Footer JS goes in this
// Header JS goes in this.
// Returns width of browser viewport
    if($( window ).width() >= 768) {
        $(window).scroll(function() {
            if ($(this).scrollTop() > 130){
                $('.fixed-element, .md-navigation').addClass("sticky");
            }
            else{
                $('.fixed-element, .md-navigation').removeClass("sticky");
            }
        });
        $(document).ready(function() {
            $(".steps-action").hide();
            $(".step-info-wrap").click(function(){
                $('.step-info-wrap').parent('.col-md-3').removeClass('active');
                $(this).parent().addClass('active');

                $(".steps-action").slideUp();
                $(this).find(".steps-action").slideToggle();
            })

            $(function(){
                var $searchlink = $('#searchtoggl i');
                var $searchbar  = $('#searchbar');

                $('.mobile-search-wrap .fa-search').on('click', function(e){
                    e.preventDefault();

                    if($(this).attr('id') == 'searchtoggl') {
                        if(!$searchbar.is(":visible")) {
                            // if invisible we switch the icon to appear collapsable
                            $searchlink.removeClass('fa-search').addClass('fa-search-minus');
                        } else {
                            // if visible we switch the icon to appear as a toggle
                            $searchlink.removeClass('fa-search-minus').addClass('fa-search');
                        }

                        $searchbar.slideToggle(300, function(){
                            // callback after search bar animation
                        });
                    }
                });

                $('#searchform').submit(function(e){
                    e.preventDefault(); // stop form submission
                });
            });
        });
    }

    console.log('Planet4 javascript reporting for duty!')
});
