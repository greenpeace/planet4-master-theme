const React = require('react');
const ReactDOMServer = require('react-dom/server');

const { CarouselHeaderStaticContent } = require('./CarouselHeaderStaticContent');

const attributes = JSON.parse(process.argv[2]);

const renderedCarousel = ReactDOMServer.renderToString(React.createElement(
  CarouselHeaderStaticContent,
  attributes
));

console.log(renderedCarousel);
