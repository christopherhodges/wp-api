/*

Run these commands 

## once:
npm install gulp
npm install bower
npm install gulp-livereload 
npm install gulp-imagemin 
npm install gulp-jshint -g
npm install gulp-concat 
npm install gulp-plumber
npm install gulp-autoprefixer 
npm install gulp-minify-css 
npm install gulp-jshint 
npm install jshint-stylish 
npm install gulp-uglify 
npm install gulp-rename 
npm install gulp-notify 
npm install gulp-include 
npm install gulp-ruby-sass
npm install gulp-watch
npm install gulp-sourcemaps

## always ( to compile/watch/etc )
bower
gulp

*/


// Config for theme
var themePath = './public_html/wp-content/themes/myTheme/';

// Gulp Nodes
var gulp = require( 'gulp' ),
	plumber = require( 'gulp-plumber' ),
	watch = require( 'gulp-watch' ),
	livereload = require( 'gulp-livereload' ),
	minifycss = require( 'gulp-minify-css' ),
	jshint = require( 'gulp-jshint' ),
	uglify = require( 'gulp-uglify' ),
	rename = require( 'gulp-rename' ),
	notify = require( 'gulp-notify' ),
	include = require( 'gulp-include' ),
	sass = require( 'gulp-ruby-sass' ),
	autoprefixer = require('gulp-autoprefixer'),
	concat = require('gulp-concat'),
	imagemin = require('gulp-imagemin'),
	sourcemaps = require('gulp-sourcemaps');

// Error Handling
var onError = function( err ) {
	console.log( 'An error occurred:', err.message );
	this.emit( 'end' );
}

gulp.task('scss', function () {
	return sass(themePath + '/style.scss', { sourcemap: false })
		.on('error', sass.logError)
		.pipe(gulp.dest(themePath))
		//.pipe(notify({ message: 'Scss task complete' }));
});


gulp.task('scripts', function() {
	return gulp.src( themePath + '/js/**/*.js')
		.pipe(jshint('.jshintrc'))
		.pipe(jshint.reporter('default'))
		.pipe(concat('main.js'))
		.pipe(gulp.dest(themePath))
		.pipe(rename({suffix: '.min'}))
		.pipe(uglify())
		.pipe(gulp.dest(themePath +'/js'))
		//.pipe(notify({ message: 'Scripts task complete' }));
});

gulp.task('images', function() {
	return gulp.src(themePath + '/images/*')
		.pipe(imagemin({ optimizationLevel: 5, progressive: true, interlaced: true }))
		.pipe(gulp.dest(themePath + '/img'))
		//.pipe(notify({ message: 'Images task complete' }));
});

// Watch task -- this runs on every save.
gulp.task( 'watch', function() {
	livereload.listen();

	// Only Watch the main style.scss
	gulp.watch( themePath + '/style.scss', [ 'scss' ] );
	gulp.watch( themePath + '/**/*.scss', [ 'scss' ] );

	// Watch js files
	gulp.watch( themePath + '/js/*.js', [ 'scripts' ] );
	
	// Watch images
	gulp.watch( themePath + '/images/*', [ 'images' ] );

	// Whenever a php file is saved, reload
	gulp.watch( themePath + '*.php' ).on( 'change', function( file ) {
		livereload.changed( file );
	});

});


// Default task -- runs scss and watch functions
gulp.task( 'default', [ 'scss', 'watch' ], function() {
	gulp.start('images');
} );