var gulp = require('gulp'),
    sass = require('gulp-sass'),
    notify = require("gulp-notify"),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    plumber = require('gulp-plumber'),
    livereload = require('gulp-livereload');

var paths = {
    styles: {
        src: './web/assets/styles/scss',
        files: './web/assets/styles/scss/**/*.scss',
        dest: './web/assets/styles/'
    }
}

gulp.task('sass', function() {
  gulp.src(paths.styles.files)
    .pipe(plumber({
        errorHandler: notify.onError("Sass Error: <%= error.message %>")}
    ))
    .pipe(sass({
        outputStyle: 'compressed',
        sourceComments: false,
        includePaths: [paths.styles.src],
        errLogToConsole: true
    }))
    .pipe(gulp.dest(paths.styles.dest))
    .pipe(livereload());
});

// JS
var scripts = [
    './web/assets/lib/src/app.js',
    './web/assets/lib/src/factories.js',
    './web/assets/lib/src/controllers.js',
    './web/assets/lib/src/helpers.js'
];

gulp.task('concat', function() {
  return gulp.src(scripts)
    .pipe(concat('app.js'))
    .pipe(uglify())
    .on('error', function errorHandler (error) {
      console.log(error.toString());
      this.emit('end');
    })
    .pipe(rename('app.min.js'))
    .pipe(gulp.dest('./web/assets/lib/'))
});

// Watch
gulp.task('watch', function() {
  gulp.watch('./assets/img/*.*', ['img']);
  gulp.watch([paths.styles.files], ['sass']);
  gulp.watch(scripts, ['concat']);

  livereload.listen();
});

gulp.task('default', ['watch', 'concat']);
