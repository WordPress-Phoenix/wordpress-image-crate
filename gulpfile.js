var browserify = require('browserify'),
    gulp = require('gulp'),
    source = require('vinyl-source-stream'),
    uglify = require('gulp-uglify'),
    pump = require('pump'),
    rename = require('gulp-rename');

// bundle the modules
gulp.task('packup', function () {
    return browserify('assets/js/image-crate-admin.manifest.js')
        .bundle()
        .pipe(source('image-crate-admin.js'))
        .pipe(gulp.dest('assets/js'));
});

// make compressed version of our app file
// ['packup'] makes this task a dependency to run ['compress']
gulp.task('compress', ['packup'], function (cb) {
    pump([
            gulp.src('assets/js/image-crate-admin.js'),
            uglify(),
            rename({suffix: '.min'}),
            gulp.dest('assets/js')
        ],
        cb
    );
});

// default settings for running our tasks
gulp.task('build', ['packup', 'compress']);
gulp.task('default', ['build']);

// watcher setup
gulp.task('watch', ['build'], function () {
    // gulp.watch('src/js/app.manifest.js', ['packup', 'compress']);
    gulp.watch('assets/js/**/*.js', ['build']);
});
