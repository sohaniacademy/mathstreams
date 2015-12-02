var gulp = require('gulp');
var minifyInline = require('gulp-minify-inline');
var minifyHTML = require('gulp-minify-inline');
var concat = require('gulp-concat');
var vulcanize = require('gulp-vulcanize');
var removeHTMLComments = require('gulp-remove-html-comments');

gulp.task('minify-js', function() {
return gulp.src(['./src/Magnets/MathgymBundle/Resources/misc/CDN/js/jquery.min.js',
		 './src/Magnets/MathgymBundle/Resources/misc/CDN/js/jquery-ui.min.js',
		 './src/Magnets/MathgymBundle/Resources/misc/CDN/js/bootstrap.min.js',
		 './src/Magnets/MathgymBundle/Resources/misc/CDN/js/angular.min.js',
		 './src/Magnets/MathgymBundle/Resources/misc/CDN/js/underscore.min.js',
		 './src/Magnets/MathgymBundle/Resources/misc/CDN/js/datatables.min.js',
		 './src/Magnets/MathgymBundle/Resources/misc/CDN/js/jquery.qtip.min.js',
		 './src/Magnets/MathgymBundle/Resources/misc/CDN/js/Chart.min.js',
		])
    .pipe(concat('vulcanized.js'))
    .pipe(gulp.dest('./src/Magnets/MathgymBundle/Resources/public/js/CDN/'));
});

gulp.task('minify-css', function() {
return gulp.src(['./src/Magnets/MathgymBundle/Resources/misc/CDN/css/ubuntu.css',
		 './src/Magnets/MathgymBundle/Resources/misc/CDN/css/ubuntuMono.csxs',
		 './src/Magnets/MathgymBundle/Resources/misc/CDN/css/bootstrap.min.css',
		 './src/Magnets/MathgymBundle/Resources/misc/CDN/css/jquery-ui.min.css',
		 './src/Magnets/MathgymBundle/Resources/misc/CDN/css/jquery.qtip.min.css',
		 './src/Magnets/MathgymBundle/Resources/misc/CDN/css/datatables.min.css',
		])
    .pipe(concat('vulcanized.css'))
    .pipe(gulp.dest('./src/Magnets/MathgymBundle/Resources/public/css/CDN/'));
});

gulp.task('default', ['minify-js', 'minify-css']);

//    .pipe(vulcanize({inlineScripts:true}))
//    .pipe(removeHTMLComments())
//    .pipe(minifyHTML({empty:true}))
//    .pipe(minifyInline())
