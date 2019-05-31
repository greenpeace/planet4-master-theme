/* global require, exports */

const cleancss = require('gulp-clean-css');
const connect = require('gulp-connect');
const gulp = require('gulp');
const kss = require('kss');
const notify = require('gulp-notify');
const plumber = require('gulp-plumber');
const scss = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const stylelint = require('gulp-stylelint');

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

function render() {
  return kss({
    source: 'src/',
    destination: 'dist/',
    builder: 'theme/',
    css: [
      'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/css/bootstrap.min.css?ver=4.1.1',
      'index.css'
    ],
    title: 'Planet 4 Styleguide'
    });
}

function watch() {
  gulp.watch([kss_scss, style_scss], gulp.series(lint_css, kss_sass, style_sass, render));
}

function serve() {
  connect.server({
    root: 'dist',
    livereload: true
  });
}

exports.build = gulp.series(lint_css, kss_sass, style_sass, render);
exports.default = gulp.parallel(watch, serve);
