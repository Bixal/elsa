// Include gulp
var gulp = require('gulp');

// Include Our Plugins
var jshint = require('gulp-jshint');
var sass = require('gulp-sass');
var sassGlob = require('gulp-sass-glob');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var postcss = require('gulp-postcss');
var cssmin = require('gulp-cssmin');
var plumber = require('gulp-plumber');
var autoprefixer = require('autoprefixer');
var notify = require('gulp-notify');
var scsslint = require('gulp-scss-lint');
var scssLintStylish = require('gulp-scss-lint-stylish');
var imagemin = require('gulp-imagemin');
var insert = require('gulp-insert');
var styleGuide = require('postcss-style-guide')
// TODO: consider removing these if not needed
// var nano = require('cssnano')
// var customProperties = require('postcss-custom-properties')
// var Import = require('postcss-import')


// Compile Our Sass
gulp.task('sass', function() {
  return gulp.src('components/scss/elsa.scss')
    // Globbing all imported files with the path
    .pipe(sassGlob())
    // Plumber allows us to bypass errors and pipe them to the Mac notification center, if possible
    .pipe(plumber({
      errorHandler: notify.onError("Error: <%= error.message %>")
    }))
    // Running SASS to compile to CSS
    .pipe(sass())
    // Prefixing ONLY our CSS file with vendor prefixes so that we don't need them in our SCSS files
    .pipe(postcss([
      autoprefixer({
      browsers: ['last 2 versions']
    }),
    // Generate our styleguide
    styleGuide({
        project: 'ELSA Living Styleguide',
        dest: 'style-guide/style-guide.html',
        showCode: true,
        theme: 'psg-theme-minimal',
        themePath: 'node_modules/psg-theme-minimal',
    }),
  ]))
    .pipe(gulp.dest('css'))
    // Minifying CSS
    .pipe(cssmin())
    // Renaming this minified file
    .pipe(rename('elsa.min.css'))
    // Piping this minfied CSS file to the CSS folder
    .pipe(gulp.dest('css'));
});

// Concatenate & Minify JS
gulp.task('scripts', function() {
  return gulp.src('components/**/*.js')
    // Plumber allows us to bypass errors and pipe them to the Mac notification center, if possible
    .pipe(plumber({
      errorHandler: notify.onError("Error: <%= error.message %>")
    }))
    // Hinting and linting our JS files
    .pipe(jshint())
    // Using JSHint Stylish to display linting messages for an easier read in terminal
    .pipe(jshint.reporter('jshint-stylish'))
    // Concatenates all /scripts/*.js files into one JS file
    .pipe(concat('elsa.js'))
    // Wraps this main.js file with jQuery closure (and/or any other wrappers we need)
    .pipe(insert.wrap('(function ($) {', '}(jQuery));')) // prepends 'string1' and appends 'string2' to the contents
    .pipe(gulp.dest('js'))
    // Start uglify
    .pipe(uglify())
    // Renames this uglified, concatenated file to a min version.
    .pipe(rename('elsa.min.js'))
    // Pipes this minified js file to the JS folder
    .pipe(gulp.dest('js'));
});

// SCSS Linting
gulp.task('scsslint', function() {
  // Lints all scss/**/*.scss files, but excludes all utility SCSS files, as these often are from external libraries or legacy content, and here there be dragons
  return gulp.src(['components/**/*.scss'])
    .pipe(scsslint({
      'config': 'components/scss/scss_linters.yml',
      // Using SCSS Lint Stylish to display linting messages for an easier read in terminal
      customReport: scssLintStylish
    }))
});

// Style Guide
// gulp.task('style-guide', function () {
//
//   return gulp.src('css/elsa.css')
//       .pipe(postcss([
//           Import,
//           customProperties({ preserve: true }),
//           styleGuide({
//               project: 'ELSA Living Styleguide',
//               dest: '../../../style-guide.html',
//               showCode: true,
//               //remove theme (https://www.npmjs.com/package/psg-theme-minimal) and path to retun to default
//               theme: 'psg-theme-minimal',
//               themePath: 'node_modules/psg-theme-minimal',
//           }),
//       ]))
// });

// Image minification
// gulp.task('imagemin', function() {
//     return gulp.src('images/**/*')
//         .pipe(imagemin())
//         .pipe(gulp.dest('images/'))
// });

// Watch files for changes on JS or SCSS change.
gulp.task('watch', function() {
  gulp.watch('components/**/*.js', ['scripts']);
  gulp.watch('components/**/*.scss', ['sass']);
  // gulp.watch('components/**/*.scss', ['style-guide']);
  // gulp.watch('images/**/*', ['imagemin']);
});

// Default Task
gulp.task('default', ['sass','scsslint','scripts','watch']);
