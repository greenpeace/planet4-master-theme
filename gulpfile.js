/* global require, exports */

const gulp = require('gulp');
const stylelint = require('gulp-stylelint');
const eslint = require('gulp-eslint');
const js = require('gulp-uglify-es').default;
const concat = require('gulp-concat');
const scss = require('gulp-sass');
const cleancss = require('gulp-clean-css');
const sourcemaps = require('gulp-sourcemaps');
const notify = require('gulp-notify');
const plumber = require('gulp-plumber');
const livereload = require('gulp-livereload');

const path_js = 'assets/js/**/*.js';
const path_scss = 'assets/scss/**/*.scss';
const path_style = 'assets/scss/style.scss';
const path_dest = './';

const path_admin_dest = './admin/';
const path_scss_admin = './admin/scss/**/*.scss';
const path_style_admin = './admin/scss/blocks-admin.scss';

const blocks_javascripts = [
  'admin/js/Select2FieldsSetup.js',
  'admin/js/P4FieldsSetup.js',
  'admin/js/WPShortcakeHooksSetup.js',
  'admin/js/blocks/*.js',
  'admin/js/blocks-admin.js',
];

const path_git_hooks = '.githooks/*';

let error_handler = {
  errorHandler: notify.onError({
    title: 'Gulp',
    message: 'Error: <%= error.message %>'
  })
};

function lint_css() {
  return gulp.src([ path_scss, path_scss_admin ])
    .pipe(plumber(error_handler))
    .pipe(stylelint({
      reporters: [{ formatter: 'string', console: true}]
    }))
    .pipe(livereload());
}

function lint_js() {
  return gulp.src([ path_js, ...blocks_javascripts ])
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

function sass_admin() {
  return gulp.src(path_style_admin)
    .pipe(plumber(error_handler))
    .pipe(sourcemaps.init())
    .pipe(scss().on('error', scss.logError))
    .pipe(cleancss({rebase: false}))
    .pipe(sourcemaps.write(path_admin_dest))
    .pipe(gulp.dest(path_admin_dest))
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

function uglify_admin() {
  return gulp.src(blocks_javascripts)
    .pipe(plumber(error_handler))
    .pipe(sourcemaps.init())
    .pipe(concat('blocks-admin.min.js'))
    .pipe(js())
    .pipe(sourcemaps.write(path_admin_dest))
    .pipe(gulp.dest(path_admin_dest))
    .pipe(livereload());
}

function watch() {
  livereload.listen({ 'port': 35729 });
  gulp.watch(path_scss, gulp.series(lint_css, sass));
  gulp.watch(path_scss_admin, gulp.series(lint_css, sass_admin));
  gulp.watch(path_js, gulp.series(lint_js, uglify));
  gulp.watch(blocks_javascripts, gulp.series(lint_js, uglify_admin));
}

function git_hooks() {
  return gulp.src(path_git_hooks)
    .pipe(plumber(error_handler))
    .pipe(gulp.dest('.git/hooks/', {'mode': '755', 'overwrite': true}))
    .pipe(notify('Copied git hooks'));
}

exports.sass = sass;
exports.uglify = uglify;
exports.uglify_admin = uglify_admin;
exports.watch = watch;
exports.git_hooks = git_hooks;
exports.test = gulp.parallel(lint_css, lint_js);
exports.default = gulp.series(lint_css, lint_js, sass, sass_admin, uglify, uglify_admin);
