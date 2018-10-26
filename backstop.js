/* global require, process, module */

var arguments = require('minimist')(process.argv.slice(2));

var url = 'https://k8s.p4.greenpeace.org/defaultcontent/';
if (arguments.url) {
  url = arguments.url;
}

var paths = [
  '/',
  '/act/',
  '/act/vestibulum-leo-libero/',
  '/explore/',
  '/explore/energy/',
  '/tag/coal/',
  '/author/lreyes/',
  '/copyright/',
  '/privacy-and-cookies/',
  '/?s=food&orderby=relevant',
];

var scenarios = [];

for (var i = 0; i < paths.length; i++) {
  scenarios.push({
    'label': paths[i],
    'url': url + paths[i],
    'delay': 500,
  });
}

module.exports = {
  'id': 'planet4',
  'viewports': [
    {
      'label': 'mobile',
      'width': 320,
      'height': 480
    },
    {
      'label': 'small',
      'width': 600,
      'height': 768
    },
    {
      'label': 'medium',
      'width': 800,
      'height': 1024
    },
    {
      'label': 'large',
      'width': 1024,
      'height': 992
    },
    {
      'label': 'xlarge',
      'width': 1300,
      'height': 1024
    }
  ],
  'scenarios':
    scenarios
  ,
  'paths': {
    'bitmaps_reference': 'tests/backstop/bitmaps_reference',
    'bitmaps_test':      'tests/backstop/bitmaps_test',
    'casper_scripts':    'tests/backstop/casper_scripts',
    'html_report':       'tests/backstop/html_report',
    'ci_report':         'tests/backstop/ci_report'
  },
  'casperFlags': [],
  'engine': 'puppeteer',
  'report': ['browser', 'CI'],
  'engineOptions': {
    'args': ['--no-sandbox']
  },
  'asyncCaptureLimit': 5,
  'asyncCompareLimit': 50,
  'debug': false,
  'debugWindow': false
};
