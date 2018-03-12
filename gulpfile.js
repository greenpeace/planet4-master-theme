/* global require */

var gulp = require('gulp');

gulp.task('assets', function(){
    var p = require('./package.json');
    var assets = p.assets;
    return gulp.src(assets, {cwd : 'node_modules/**'})
        .pipe(gulp.dest('assets/lib'));
});

gulp.task('default', function() {
    gulp.start('assets');
});
