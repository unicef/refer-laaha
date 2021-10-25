let gulp = require('gulp');
let sass = require('gulp-sass');
let shell = require('gulp-shell');
let sourcemaps = require('gulp-sourcemaps');
let autoprefixer = require('gulp-autoprefixer');
var stripCssComments = require('gulp-strip-css-comments');

/**
 * @task default
 * Show arguments helper.
 */
 gulp.task('default', function(done) {
  console.log('Please pass parameters. Available parameters are themes, watchthemes & lintthemes');
  done();
});

/**
 * @task sass
 * Compile files from _scss.
 */
gulp.task('themes', function (done) {
  gulp.src('docroot/themes/custom/vss/scss/*.scss')
    .pipe(stripCssComments())
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(autoprefixer('last 2 version'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('docroot/themes/custom/vss/css'));
  done();
});

/**
 * @task watch
 * Watch _scss files for changes & recompile
 * Clear cache when Drupal related files are changed
 */
gulp.task('watchthemes', function (done) {
  gulp.watch('docroot/themes/custom/vss/scss/*.scss', gulp.series('themes'));
  done();
});

/**
 * @task lintthemes
 * Show arguments helper.
 */
gulp.task('lintthemes', shell.task([
  'stylelint --color docroot/themes/custom/**/scss/**/**/*.scss'
]));