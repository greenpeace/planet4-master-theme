jQuery(function ($) {
  'use strict';

  const $climate = $('.campaign-climate #enform');

  if ($climate.length) {
    // Adjust first line of the description
    $('.title-and-description p:first-child').wrap('<span class="first-line">');
  }
});
