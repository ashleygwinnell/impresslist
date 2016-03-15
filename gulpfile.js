var gulp = require('gulp');
var concat = require('gulp-concat');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var ngAnnotate = require('gulp-ng-annotate');
var order = require('gulp-order');

var file = require('file');
var path = require('path');

function errorHandler (error) {
	console.log("ERROR");
  	console.log(error.toString());
}

// http://stackoverflow.com/questions/25876936/gulp-how-do-i-control-processing-order-with-gulp-concat
function getModules(src, app, ignore) {

    var modules = [];

    file.walkSync(src, function(dirPath, dirs, files) {

        if(files.length < 1)
            return;

        var dir = path.basename(dirPath)
            module;

        if(ignore.indexOf(dir) === -1) {
            module = dirPath === src ? app : dir;

            files = files.sort(function(a, b) {
                return path.basename(a, '.js') === module ? -1 : 1;
            })
            .filter(function(value) {
                return value.indexOf('.') !== 0;
            })
            .map(function(value) {
                return path.join(dirPath, value);
            })
            // remove html files
            for(var i = 0; i < files.length; i++) {
              if (files[i].indexOf('.html') > 0) {
                files.splice(i, 1);
                i = -1;
              }
            }

            modules = modules.concat(files);

            //console.log(modules);

        }
    })

    return modules;
}

// Make impress-dev.js with no minification.
// gulp.task('js-dev', function () {
//   gulp.src(['app/components/**/*.js', '!app/components/**/*.*.js', 'app/app.js'])
//   .pipe(sourcemaps.init())
// 	.pipe(concat('impress-dev.js')).on('error', errorHandler)
// 	.pipe(sourcemaps.write())
//     .pipe(gulp.dest('.')).on('error', errorHandler)
// });

// Compile!
gulp.task('js', function () {

  var orderedComponents = getModules('app/components', 'app', []);
  var orderedViews = getModules('app/views', 'app', []);
  var allfiles = ['app/app.js', 'app/app.controller.js', 'app/app.service.js'].concat(orderedComponents).concat(orderedViews);

  console.log('allfiles');
  console.log(allfiles);

  gulp.src( allfiles )
    .pipe(sourcemaps.init())
      //.pipe(uglify()).on('error', errorHandler)
	    .pipe(concat('impress.js')).on('error', errorHandler)
	    .pipe(uglify()).on('error', errorHandler)
    .pipe(sourcemaps.write())
  .pipe(gulp.dest('.')).on('error', errorHandler)
});

// Watch
gulp.task('watch', ['js'], function () {
  gulp.watch('app/**/*.js', ['js'])
})

