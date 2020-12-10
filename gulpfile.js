var gulp = require( "gulp" );
var del = require( "del" );
var rename = require( "gulp-rename" );
var $ = require( "gulp-load-plugins" )();
var browserSync = require( "browser-sync" ).create();
var mjml = require( "gulp-mjml" );
var mjmlEngine = require( "mjml" );
var replace = require( "gulp-replace" );
require( "./gulpfile-admin" );

var vendorStyles = [
	"node_modules/@fortawesome/fontawesome-free/css/all.css",
	"node_modules/swiper/css/swiper.css",
	"node_modules/photoswipe/dist/photoswipe.css",
	"node_modules/cookieconsent/build/cookieconsent.min.css"
];

var vendorScripts = [
	"node_modules/jquery/dist/jquery.js",
	"node_modules/bootstrap/dist/js/bootstrap.bundle.js", // Bootstrap + Popper
	"node_modules/atk14js/src/atk14.js",
	"node_modules/unobfuscatejs/src/jquery.unobfuscate.js",
	"node_modules/swiper/js/swiper.js",
	"node_modules/photoswipe/dist/photoswipe.js",
	"node_modules/photoswipe/dist/photoswipe-ui-default.js",
	"node_modules/cookieconsent/build/cookieconsent.min.js",
	"node_modules/bootbox/src/bootbox.js"
];

var applicationScripts = [
	"public/scripts/utils/utils.js",
	"public/scripts/pager.js",
	"public/scripts/filter.js",
	"public/scripts/utils/photoswipe.js",
	"public/scripts/utils/basket_shipping_rules.js",
	"public/scripts/utils/maps.js",
	"public/scripts/utils/edit_basket_form.js",
	"public/scripts/utils/filterable_list.js",
	"public/scripts/utils/search_suggestion.js",
	"public/scripts/application.js"
];

// CSS
gulp.task( "styles", function() {
	return gulp.src( "public/styles/application.scss" )
		.pipe( $.sourcemaps.init() )
		.pipe( $.sass( {
			includePaths: [
				"public/styles"
			]
		} ) )
		.pipe( $.autoprefixer( { grid: true } ) )
		.pipe( $.cssnano() )
		.pipe( $.rename( { suffix: ".min" } ) )
		.pipe( $.sourcemaps.write( ".", { sourceRoot: null } ) )
		.pipe( gulp.dest( "public/dist/styles" ) )
		.pipe( browserSync.stream( { match: "**/*.css" } ) );
} );

gulp.task( "styles-vendor", function() {
	return gulp.src( vendorStyles )
		.pipe( $.sourcemaps.init() )
		.pipe( $.concatCss( "vendor.css" ) )
		.pipe( $.autoprefixer() )
		.pipe( $.cssnano( { svgo: false } ) )
		.pipe( $.rename( { suffix: ".min" } ) )
		.pipe( $.sourcemaps.write( ".", { sourceRoot: null } ) )
		.pipe( gulp.dest( "public/dist/styles" ) )
		.pipe( browserSync.stream( { match: "**/*.css" } ) );
} );

// JS
gulp.task( "scripts", function() {
	gulp.src( vendorScripts )
		.pipe( $.sourcemaps.init() )
		.pipe( $.concat( "vendor.js" ) )
		.pipe( $.uglify() )
		.pipe( $.rename( { suffix: ".min" } ) )
		.pipe( $.sourcemaps.write( "." ) )
		.pipe( gulp.dest( "public/dist/scripts" ) );

	gulp.src( applicationScripts )
		.pipe( $.sourcemaps.init() )
		.pipe( $.concat( "application.js" ) )
		.pipe( $.uglify() )
		.pipe( $.rename( { suffix: ".min" } ) )
		.pipe( $.sourcemaps.write( "." ) )
		.pipe( gulp.dest( "public/dist/scripts" ) )
		.pipe( browserSync.stream() );
} );

// Lint & Code style
gulp.task( "lint", function() {
	return gulp.src( [ "public/scripts/**/*.js", "gulpfile.js" ] )
		.pipe( $.eslint() )
		.pipe( $.eslint.format() )
		.pipe( $.eslint.failAfterError() );
} );

// Copy
gulp.task( "copy", function() {
	gulp.src( "node_modules/html5shiv/dist/html5shiv.min.js" )
		.pipe( gulp.dest( "public/dist/scripts" ) );
	gulp.src( "node_modules/respond.js/dest/respond.min.js" )
		.pipe( gulp.dest( "public/dist/scripts" ) );
	gulp.src( "node_modules/@fortawesome/fontawesome-free/webfonts/*" )
		.pipe( gulp.dest( "public/dist/webfonts" ) );
	gulp.src( "public/fonts/*" )
		.pipe( gulp.dest( "public/dist/fonts" ) );
	gulp.src( "public/images/**/*" )
		.pipe( gulp.dest( "public/dist/images" ) );
	gulp.src( "node_modules/photoswipe/dist/default-skin/*" )
		.pipe( gulp.dest( "public/dist/styles/default-skin/" ) );

	// Flags for languages
	gulp.src( "node_modules/svg-country-flags/svg/*" )
		.pipe( gulp.dest( "public/dist/images/languages" ) )
		.on( "end", function() {

			// Some corrections in language flags
			var renameTr = {
				"cz": "cs",
				"gb": "en"
			};
			Object.keys( renameTr ).forEach( function( key ) {
				gulp.src( "public/dist/images/languages/" + key + ".svg" )
					.pipe( rename( renameTr[ key ] + ".svg" ) )
					.pipe( gulp.dest( "public/dist/images/languages" ) );
			} );
		} );
} );

// MJML emails
gulp.task( "mjml", function(){
	gulp.src( "public/mjml/*.mjml" )
		.pipe( mjml( mjmlEngine, { minify: false, fileExt: ".tpl" } ) )

		// Replace ##...## with Smarty tags
		.pipe( replace( /##(.+?)##/g, "{$1}" ) )
	
		// fileExt option seems to be not working, so rename files to .tpl this way
		.pipe( rename( function( path ) {
			path.extname = ".tpl"
		}))
		.pipe( gulp.dest( "public/dist/emails" ) );
} );

// Clean
gulp.task( "clean", function() {
	del.sync( "public/dist" );
} );

// Server
gulp.task( "serve", [ "styles" ], function() {
	browserSync.init( {
		proxy: "localhost:8000"
	} );

	// If these files change = reload browser
	gulp.watch( [
		"app/**/*.tpl",
		"public/images/**/*"
	] ).on( "change", browserSync.reload );

	// If javascript files change = run 'scripts' task, then reload browser
	gulp.watch( "public/scripts/**/*.js", [ "scripts" ] ).on( "change", browserSync.reload );

	// If styles files change = run 'styles' task with style injection
	gulp.watch( "public/styles/**/*.scss", [ "styles" ] );

	// If mjml files change = run 'mjml' task, then reload browser
	gulp.watch( "public/mjml/**/*.mjml", [ "mjml" ] ).on( "change", browserSync.reload );
} );

// Build
var buildTasks = [
	"lint",
	"styles",
	"styles-vendor",
	"scripts",
	"mjml",
	"copy"
];

gulp.task( "build", buildTasks, function() {
	return gulp.src( "public/dist/**/*" )
		.pipe( $.size( { title: "build", gzip: true } ) );
} );

// Default
gulp.task( "default", [ "clean" ], function() {
	gulp.start( "build" );
} );
