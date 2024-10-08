const antiflickerConf = {'{{ google_tag_value }}': true};
(function(a, s, y, n, c, h, i, d, e) {
  s.className += ' ' + y; h.start = 1 * new Date; h.end = i = function() {
    s.className = s.className.replace(RegExp(' ?' + y), '');
    h.duration = h.duration || (1 * new Date) - h.start;
  }; (a[n] = a[n] || []).hide = h; setTimeout(() => {
    i(); h.end = null;
  }, c); h.timeout = c;
}(window, document.documentElement, 'google-optimize-loading', 'dataLayer', 4000, antiflickerConf));
