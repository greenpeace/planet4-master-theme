/* global require, exports */

const cleancss = require('gulp-clean-css');
const connect = require('gulp-connect');
const fs = require('fs');
const gulp = require('gulp');
const kss = require('kss');
const notify = require('gulp-notify');
const plumber = require('gulp-plumber');
const scss = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const stylelint = require('gulp-stylelint');
const svgsprite = require('gulp-svg-sprite');

const icons_dest = './dist/images/';
const icons_src = 'src/icons/*.svg';
const kss_dest = 'theme/kss-assets/css';
const kss_scss = 'theme/kss-assets/css/*.scss';
const kss_style = 'theme/kss-assets/css/kss.scss';
const style_dest = './dist'
const style_scss = 'src/**/*.scss';
const style = 'src/index.scss';


let error_handler = {
  errorHandler: notify.onError({
    title: 'Gulp',
    message: 'Error: <%= error.message %>'
  })
};

const icons_config = {
  shape: {
    dimension: {
      maxWidth: 64,
      maxHeight: 64
    },
    spacing: {
      padding: 0,
      box: 'content'
    }
  },
  mode: {
    inline: true,
    symbol: true
  }
};

function lint_css() {
  return gulp.src([kss_scss, style_scss])
    .pipe(plumber(error_handler))
    .pipe(stylelint({
      reporters: [{ formatter: 'string', console: true}]
    }))
    .pipe(connect.reload());
}

function kss_sass() {
  return gulp.src(kss_style)
    .pipe(plumber(error_handler))
    .pipe(sourcemaps.init())
    .pipe(scss().on('error', scss.logError))
    .pipe(cleancss({rebase: false}))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(kss_dest))
    .pipe(connect.reload());
}

function style_sass() {
  return gulp.src(style)
    .pipe(plumber(error_handler))
    .pipe(sourcemaps.init())
    .pipe(scss().on('error', scss.logError))
    .pipe(cleancss({rebase: false}))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(style_dest))
    .pipe(connect.reload());
}

function icons() {
  return gulp.src(icons_src)
    .pipe(svgsprite(icons_config))
    .pipe(gulp.dest(icons_dest));
}

function render() {
  const version = fs.readFileSync('version.txt');
  const hostname = fs.readFileSync('hostname.txt');
  const hostpath = fs.readFileSync('hostpath.txt');
  let path;

  if (hostname.length) {
    path = `//${hostname}/`;
    if (hostpath.length) {
      path += `${hostpath}/`;
    }
  }

  return kss({
    source: 'src/',
    destination: 'dist/',
    builder: 'theme/',
    css: [
      'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/css/bootstrap.min.css?ver=4.1.1',
      'index.css'
    ],
    title: 'Planet 4 Styleguide',
    version: version,
    path: path
  });
}

function a11y_test(done) {
  const report_dest = './pa11y/';
  const report_filename = 'report.json';

  const pa11yCi = require('pa11y-ci');
  const htmlReporter = require('pa11y-ci-reporter-html');
  const a11yConf = JSON.parse(fs.readFileSync('.pa11y'));

  Promise.resolve()
    .then(() => {
      return pa11yCi(a11yConf.urls, a11yConf.defaults);
    })
    .then(results => {
      if(!fs.existsSync(report_dest)) {
        fs.mkdirSync(report_dest);
      }

      fs.writeFileSync(report_dest + report_filename, JSON.stringify(results));
      htmlReporter(report_dest + report_filename, report_dest, {includeZeroIssues: true});
    });
  
  done();
}

function watch() {
  gulp.watch([kss_scss, style_scss], gulp.series(lint_css, kss_sass, style_sass, render));
}

function serve() {
  connect.server({
    root: 'dist',
    livereload: true,
    port: 9000
  });
}

exports.build = gulp.series(lint_css, kss_sass, style_sass, icons, render);
exports.test = a11y_test;
exports.default = gulp.parallel(watch, serve);
