/* global require, exports */

const gulp = require('gulp');
const stylelint = require('gulp-stylelint');
const eslint = require('gulp-eslint');
const js = require('gulp-uglify-es').default;
const concat = require('gulp-concat');
const scss = require('gulp-sass');
const cleancss = require('gulp-clean-css');
const sourcemaps = require('gulp-sourcemaps');
const backstop = require('backstopjs');
const notify = require('gulp-notify');
const plumber = require('gulp-plumber');
const livereload = require('gulp-livereload');
const svgsprite = require('gulp-svg-sprite');

const path_js = 'assets/js/partials/*.js';
const path_scss = 'assets/scss/**/*.scss';
const path_style = 'assets/scss/style.scss';
const path_icons = 'images/icons/*.svg';
const path_img = 'images/';
const path_dest = './';
const path_git_hooks = '.githooks/*';

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
  return gulp.src(path_scss)
    .pipe(plumber(error_handler))
    .pipe(stylelint({
      reporters: [{ formatter: 'string', console: true}]
    }))
    .pipe(livereload());
}

function lint_js() {
  return gulp.src(path_js)
    .pipe(plumber(error_handler))
    .pipe(eslint())
    .pipe(eslint.format())
    .pipe(eslint.failAfterError())
    .pipe(livereload());
}

function sass() {
  return gulp.src(path_style)
    .pipe(plumber(error_handler))
    .pipe(sourcemaps.init())
    .pipe(scss().on('error', scss.logError))
    .pipe(cleancss({rebase: false}))
    .pipe(sourcemaps.write(path_dest))
    .pipe(gulp.dest(path_dest))
    .pipe(livereload());
}

function uglify() {
  return gulp.src(path_js)
    .pipe(plumber(error_handler))
    .pipe(sourcemaps.init())
    .pipe(concat('main.js'))
    .pipe(js())
    .pipe(sourcemaps.write(path_dest))
    .pipe(gulp.dest(path_dest))
    .pipe(livereload());
}

function icons() {
  return gulp.src(path_icons)
    .pipe(svgsprite(icons_config))
    .pipe(gulp.dest(path_img));
}

function watch() {
  livereload.listen({'port': 35730});
  gulp.watch(path_scss, gulp.series(lint_css, sass));
  gulp.watch(path_js, gulp.series(lint_js, uglify));
}

function backstop_reference(done) {
  backstop('reference', {config: './backstop.js'});
  done();
}

function backstop_test(done) {
  backstop('test', {config: './backstop.js'});
  done();
}

function git_hooks() {
  return gulp.src(path_git_hooks)
    .pipe(plumber(error_handler))
    .pipe(gulp.dest('.git/hooks/', {'mode': '755', 'overwrite': true}))
    .pipe(notify('Copied git hooks'));
}

exports.icons = icons;
exports.sass = sass;
exports.uglify = uglify;
exports.backstop_reference = backstop_reference;
exports.backstop_test = backstop_test;
exports.watch = watch;
exports.git_hooks = git_hooks;
exports.test = gulp.parallel(lint_css, lint_js);
exports.default = gulp.series(lint_css, lint_js, sass, uglify);
