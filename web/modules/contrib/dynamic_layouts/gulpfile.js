var gulp = require('gulp');
var $ = require('gulp-load-plugins')();
var del = require('del');

/**
 * @task sass-lint
 * Lint sass, abort calling task on error
 */
gulp.task('sass-lint', function () {
  return gulp.src('static/sass/**/*.s+(a|c)ss')
  .pipe($.sassLint({configFile: '.sass-lint.yml'}))
  .pipe($.sassLint.format())
  .pipe($.sassLint.failOnError());
});

gulp.task('sass-compile', ['sass-lint'], function () {
  // postCss plugins and processes
  var pcPlug = {
    autoprefixer: require('autoprefixer'),
    mqpacker: require('css-mqpacker')
  };
  var pcProcess = [
    pcPlug.autoprefixer({
      browsers: ['last 2 versions', 'IE 11']
    }),
    pcPlug.mqpacker()
  ];

  return gulp.src('static/sass/**/*.s+(a|c)ss') // Gets all files ending
  .pipe($.sourcemaps.init())
  .pipe($.sass())
  .on('error', function (err) {
    console.log(err);
    this.emit('end');
  })
  .pipe($.postcss(pcProcess))
  .pipe($.uglifycss({
    "maxLineLen": 80,
    "uglyComments": true
  }))
  .pipe($.sourcemaps.write())
  .pipe(gulp.dest('static/css'));
});

gulp.task('sass-compile-prod', ['clean'], function () {
  // postCss plugins and processes
  var pcPlug = {
    autoprefixer: require('autoprefixer'),
    mqpacker: require('css-mqpacker')
  };
  var pcProcess = [
    pcPlug.autoprefixer({
      browsers: ['last 2 versions', 'IE 11']
    }),
    pcPlug.mqpacker()
  ];

  return gulp.src('static/sass/**/*.s+(a|c)ss') // Gets all files ending
    .pipe($.sass())
    .on('error', function (err) {
      console.log(err);
      this.emit('end');
    })
    .pipe($.postcss(pcProcess))
    .pipe($.uglifycss({
      "maxLineLen": 80,
      "uglyComments": true
    }))
    .pipe(gulp.dest('static/css'));
});

/**
 * @task watch
 * Watch files and do stuff.
 */
gulp.task('watch', ['clean', 'sass-compile'], function () {
  gulp.watch('static/sass/**/*.+(scss|sass)', ['sass-compile']);
});

/**
 * @task clean
 * Clean the dist folder.
 */
gulp.task('clean', function () {
  return del(['static/css/*']);
});

/**
 * @task prod
 * Prepare everything for production
 */
gulp.task('prod', ['clean', 'sass-compile-prod']);

/**
 * @task default
 * Watch files and do stuff.
 */
gulp.task('default', ['watch']);
