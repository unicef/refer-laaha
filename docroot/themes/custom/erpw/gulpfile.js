const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const sassLint = require('gulp-sass-lint');
const shell = require('gulp-shell');
const sourcemaps = require('gulp-sourcemaps');
const autoprefixer = require('gulp-autoprefixer');
const stripCssComments = require('gulp-strip-css-comments');
const del = require('del');
const concat = require('gulp-concat');
const babel = require('gulp-babel');
const uglify = require('gulp-uglify');
const tslint = require("gulp-tslint");

/**
 * @task default
 * Show arguments helper.
 */
gulp.task('default', function (done) {
  console.log('Please pass parameters. Available parameters are themes, watchthemes & lintthemes');
  done();
});


gulp.task('clean:css', function () {
  return del([
    './css/style.css'
  ]);
});

/**
 * @task sass
 * Compile files from _scss.
 */
gulp.task('compile:scss', function (done) {
  gulp.src('theme-resources/scss/style.scss')
    .pipe(sassLint())
    .pipe(sassLint.format())
    .pipe(sassLint.failOnError())
    .pipe(stripCssComments())
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(autoprefixer('last 2 version'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./css/'));
  done();
});

gulp.task('scss', gulp.series(['clean:css', 'compile:scss']));


gulp.task("uglify", function (done) {
  gulp.src('theme-resources/js/**/*.*',)
    .pipe(concat('build.js'))
    .pipe(uglify())
    .pipe(gulp.dest('./js/'));
    done();
});
gulp.task('js', gulp.series(['uglify']));

gulp.task("tslint", () =>
  gulp.src('theme-resources/typescript/**/*.*',)
    .pipe(tslint({
      formatter: "verbose",
      rules: {
        "class-name": true,
      }
    },
    ))
    .pipe(tslint.report())
);

gulp.task('compile:ts', function (cb) {
  return gulp.src([
    'theme-resources/typescript/**/*.*',
  ])
    .pipe(concat('babel.js'))
    .pipe(babel({
      presets: [
        '@babel/flow',
        '@babel/env',
        '@babel/typescript',
      ],
      plugins: [
        '@babel/proposal-class-properties',
        '@babel/transform-classes',
        '@babel/syntax-typescript',
        '@babel/transform-typescript',
      ]
    }))
    .pipe(gulp.dest('theme-resources/' + 'tmp/babel'));
});

gulp.task('typescript', gulp.series([
  'tslint', 'compile:ts'
])
);

var scripts = {
  vendors: [
    'theme-resources/vendor/js/**/*.js',
  ],
  app: [
    'theme-resources/tmp/' + 'babel/babel.js',
    'theme-resources/tmp/' + 'js/vendor.js',
  ]
};

gulp.task('concatvendor', function () {
  return gulp.src(scripts.vendors)
    .pipe(concat('vendor.js'))
    .on('error', console.error.bind(console))
    .pipe(gulp.dest('theme-resources/tmp/' + 'js/'))
});

gulp.task('concatapp', function () {
  console.log("JS created at " + '/js');
  return gulp.src(scripts.app, {"allowEmpty": true})
    .pipe(concat('app.js'))
    .on('error', console.error.bind(console))
    .pipe(uglify())
    .pipe(gulp.dest('js/'))
});

gulp.task('concatjs', gulp.series([
  'concatvendor', 'concatapp'
])
);

/**
 * @task watch
 * Watch _scss files for changes & recompile
 * Clear cache when Drupal related files are changed
 */


/**
 * @task lintthemes
 * Show arguments helper.
 */
gulp.task('lintthemes', shell.task([
  'stylelint --color docroot/themes/custom/**/scss/**/**/*.scss'
]));


gulp.task('default',
  gulp.series([
    'scss',
    'typescript',
    'concatjs',
    'js'
  ])
);


gulp.task('watch', function () {
  global.waitingWatch = false;
  // Watch for css changes
  gulp.watch('theme-resources/' + 'scss/**/*', gulp.series(['scss']));

  // Watch for js changes
  gulp.watch(['theme-resources/' + 'js/**/*'], gulp.series(['js']));

});

var fontIcon = require("gulp-font-icon");

gulp.task("fontIcon", function() {
	return gulp.src(["assets/icons/*.svg"])
		.pipe(fontIcon({
			fontName: "unicefFonts",
			fontAlias: "uf",
      normalize:true,
      fontHeight: 1001
		}))
		.pipe(gulp.dest("assets/res/icons/"));
});
