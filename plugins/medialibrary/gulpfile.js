/* global require, exports */

const gulp = require('gulp');
const stylelint = require('gulp-stylelint');
const eslint = require('gulp-eslint');
const notify = require('gulp-notify');
const plumber = require('gulp-plumber');
const livereload = require('gulp-livereload');

const path_js = 'assets/js/*.js';
const path_css = 'admin/css/*.css';
const path_git_hooks = '.githooks/*';

let error_handler = {
  errorHandler: notify.onError({
    title: 'Gulp',
    message: 'Error: <%= error.message %>'
  })
};

function lint_css() {
  return gulp.src([path_css])
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

function watch() {
  livereload.listen({'port': 35731});
  gulp.watch(path_css, gulp.series(lint_css));
  gulp.watch(path_js, gulp.series(lint_js));
}

function git_hooks() {
  return gulp.src(path_git_hooks)
      .pipe(plumber(error_handler))
      .pipe(gulp.dest('.git/hooks/', {'mode': '755', 'overwrite': true}))
      .pipe(notify('Copied git hooks'));
}

exports.watch = watch;
exports.git_hooks = git_hooks;
exports.test = gulp.parallel(lint_css, lint_js);
exports.default = gulp.series(lint_css, lint_js);
