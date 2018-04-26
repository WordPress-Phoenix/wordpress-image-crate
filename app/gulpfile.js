var browserify  = require('browserify'),
    gulp        = require('gulp'),
    source      = require('vinyl-source-stream'),
    uglify      = require('gulp-uglify'),
    pump        = require('pump'),
    rename      = require('gulp-rename');

// Bundle the modules.
gulp.task('packup', function () {
    return browserify('assets/js/image-crate.manifest.js')
        .bundle()
        .pipe(source('image-crate.js'))
        .pipe(gulp.dest('assets/js'));
});

/*
 * Make compressed version of our app file.
 *
 * ['packup'] makes this task a dependency to run ['compress']
 */
gulp.task('compress', ['packup'], function (cb) {
    pump([
            gulp.src('assets/js/image-crate.js'),
            uglify(),
            rename({suffix: '.min'}),
            gulp.dest('assets/js')
        ],
        cb
    );
});

// Default settings for running our tasks.
gulp.task('build', ['packup', 'compress']);
gulp.task('default', ['build']);

// Watcher setup.
gulp.task('watch', ['build'], function () {
    gulp.watch('assets/js/**/*.js', ['build']);
});
