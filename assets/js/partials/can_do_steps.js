$(document).ready(function() {
  'use strict';

  $('.step-info-wrap').click(function(){
    if($(this).parent().hasClass('active')){
      $(this).parent().removeClass('active');
    }
    else {
      $('.col').removeClass('active');
      $(this).parent().addClass('active');
    }
  });
});
