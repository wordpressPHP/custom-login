var project = 'CustomLogin'; // Name

/**
 * Load Plugins.
 *
 * Load gulp plugins and assessing the semantic names.
 */
var gulp = require('gulp'); // Gulp of-course

// CSS related plugins.
var sass = require('gulp-sass'); // Gulp plugin for Sass compilation
var autoprefixer = require('gulp-autoprefixer'); // Auto-prefixing magic
var minifycss = require('gulp-uglifycss'); // Minifies CSS files

// JS related plugins.
var concat = require('gulp-concat'); // Concatenates JS files
var uglify = require('gulp-uglify'); // Minifies JS files

// Utility related plugins.
var rename = require('gulp-rename'); // Renames files E.g. style.css -> style.min.css
var sourcemaps = require('gulp-sourcemaps'); // Maps code in a compressed file (E.g. style.css) back to it’s original position in a source file (E.g. structure.scss, which was later combined with other css files to generate style.css)
var notify = require('gulp-notify'); // Sends message notification to you

var styleSRC = './assets/css/style.scss'; // Path to main .scss file
var styleDestination = './'; // Path to place the compiled CSS file
// Default set to root folder

var jsVendorSRC = './assets/js/vendors/*.js'; // Path to JS vendors folder
var jsVendorDestination = './assets/js/'; // Path to place the compiled JS vendors file
var jsVendorFile = 'vendors'; // Compiled JS vendors file name
// Default set to vendors i.e. vendors.js

var jsCustomSRC = './assets/js/custom/*.js'; // Path to JS custom scripts folder
var jsCustomDestination = './assets/js/'; // Path to place the compiled JS custom scripts file
var jsCustomFile = 'custom'; // Compiled JS custom file name
// Default set to custom i.e. custom.js

var styleWatchFiles = './assets/css/**/*.scss'; // Path to all *.scss files inside css folder and inside them
var vendorJSWatchFiles = './assets/js/vendors/*.js'; // Path to all vendors JS files
var customJSWatchFiles = './assets/js/custom/*.js'; // Path to all custom JS files

// File paths to various assets are defined here.
var PATHS = {
  css: [
    'bower_components/animate.css/*.css'
  ],
  sass: [
    'bower_components/foundation-sites/scss',
    'bower_components/motion-ui/src',
    'bower_components/fontawesome/scss'
  ],
  javascript: [
    // Custom JS files
    'assets/js/vendor/*.js',
    'assets/js/theme/theme.js'
  ],
  fonts: [
    'bower_components/fontawesome/fonts'
  ]
};

/**
 * Task: styles
 *
 * Compiles Sass, Autoprefixes it and Minifies CSS.
 *
 * This task does the following:
 *    1. Gets the source scss file
 *    2. Compiles Sass to CSS
 *    3. Writes Sourcemaps for it
 *    4. Autoprefixes it and generates style.css
 *    5. Renames the CSS file with suffix .min.css
 *    6. Minifies the CSS file and generates style.min.css
 */
gulp.task('styles', function () {
  gulp.src(styleSRC)
    .pipe(sourcemaps.init())
    .pipe(sass({
      errLogToConsole: true,
      outputStyle: 'compact',
      //outputStyle: 'compressed',
      //outputStyle: 'nested',
      //outputStyle: 'expanded',
      precision: 10
    }))
    .pipe(sourcemaps.write({includeContent: false}))
    .pipe(sourcemaps.init({loadMaps: true}))
    .pipe(autoprefixer(
      'last 2 version',
      '> 1%',
      'safari 5',
      'ie 9',
      'opera 12.1',
      'ios 6',
      'android 4'))

    .pipe(sourcemaps.write(styleDestination))
    .pipe(gulp.dest(styleDestination))
    .pipe(rename({suffix: '.min'}))
    .pipe(minifycss({
      maxLineLen: 10
    }))
    .pipe(gulp.dest(styleDestination))
    .pipe(notify({message: 'TASK: "styles" Completed!', onLast: true}))
});


/**
 * Task: vendorJS
 *
 * Concatenate and uglify vendor JS scripts.
 *
 * This task does the following:
 *    1. Gets the source folder for JS vendor files
 *    2. Concatenates all the files and generates vendors.js
 *    3. Renames the JS file with suffix .min.js
 *    4. Uglifes/Minifies the JS file and generates vendors.min.js
 */
gulp.task('vendorsJs', function () {
  gulp.src(jsVendorSRC)
    .pipe(concat(jsVendorFile + '.js'))
    .pipe(gulp.dest(jsVendorDestination))
    .pipe(rename({
      basename: jsVendorFile,
      suffix: '.min'
    }))
    .pipe(uglify())
    .pipe(gulp.dest(jsVendorDestination))
    .pipe(notify({message: 'TASK: "vendorsJs" Completed!', onLast: true}));
});


/**
 * Task: customJS
 *
 * Concatenate and uglify custom JS scripts.
 *
 * This task does the following:
 *    1. Gets the source folder for JS custom files
 *    2. Concatenates all the files and generates custom.js
 *    3. Renames the JS file with suffix .min.js
 *    4. Uglifes/Minifies the JS file and generates custom.min.js
 */
gulp.task('customJS', function () {
  gulp.src(jsCustomSRC)
    .pipe(concat(jsCustomFile + '.js'))
    .pipe(gulp.dest(jsCustomDestination))
    .pipe(rename({
      basename: jsCustomFile,
      suffix: '.min'
    }))
    .pipe(uglify())
    .pipe(gulp.dest(jsCustomDestination))
    .pipe(notify({message: 'TASK: "customJs" Completed!', onLast: true}));
});

/**
 * Watch Tasks.
 *
 * Watches for file changes and runs specific tasks.
 */
gulp.task('default', ['styles', 'vendorsJs', 'customJS'], function () {
  gulp.watch(styleWatchFiles, ['styles']);
  gulp.watch(vendorJSWatchFiles, ['vendorsJs']);
  gulp.watch(customJSWatchFiles, ['customJS']);
});
