var gulp = require( "gulp" );
var del = require( "del" );
var rename = require( "gulp-rename" );
var babel = require( "gulp-babel");
var $ = require( "gulp-load-plugins" )();
var browserSync = require( "browser-sync" ).create();
require( "./gulpfile-admin" );

var vendorStyles = [
	"node_modules/@fortawesome/fontawesome-free/css/all.css",
	"node_modules/swiper/swiper-bundle.css",
	"node_modules/photoswipe/dist/photoswipe.css",
	// "node_modules/jquery-ui-bundle/jquery-ui.min.css",
	"node_modules/cookieconsent/build/cookieconsent.min.css",
	"node_modules/nouislider/dist/nouislider.min.css",
	"node_modules/leaflet/dist/leaflet.css",
	"node_modules/leaflet.markercluster/dist/MarkerCluster.css",
	"node_modules/leaflet-gesture-handling/dist/leaflet-gesture-handling.css",
	//"node_modules/leaflet.markercluster/dist/MarkerCluster.Default.css"
];

var vendorScripts = [
	"node_modules/jquery/dist/jquery.js",
	//"node_modules/jquery-ui-bundle/jquery-ui.js",
	"node_modules/bootstrap/dist/js/bootstrap.bundle.js", // Bootstrap + Popper
	"node_modules/atk14js/src/atk14.js",
	"node_modules/unobfuscatejs/src/jquery.unobfuscate.js",
	"node_modules/swiper/swiper-bundle.js",
	"node_modules/cookieconsent/build/cookieconsent.min.js",
	"node_modules/bootbox/dist/bootbox.all.min.js",
	"node_modules/nouislider/dist/nouislider.min.js",
	"node_modules/leaflet/dist/leaflet.js",
	"node_modules/leaflet.markercluster/dist/leaflet.markercluster.js",
	"node_modules/leaflet-gesture-handling/dist/leaflet-gesture-handling.js",
	"node_modules/sticky-sidebar-v2/dist/sticky-sidebar.js" // Enable this if site uses sidebar nav
];

var applicationScripts = [
	"public/scripts/utils/utils.js",
	"public/scripts/utils/swiper.js",
	"public/scripts/pager.js",
	"public/scripts/filter.js",
	"public/scripts/nouislider.js",
	"public/scripts/utils/basket_shipping_rules.js",
	"public/scripts/utils/maps.js",
	"public/scripts/utils/edit_basket_form.js",
	"public/scripts/utils/filterable_list.js",
	"public/scripts/utils/handle_suggestions.js",
	"public/scripts/delivery_service_branch_select.js",
	"public/scripts/delivery_service_widgets.js",
	"public/scripts/utils/search_suggestion.js",
	"public/scripts/utils/cookie_consent.js",
	"public/scripts/utils/offcanvas.js",
	"public/scripts/utils/styleguides.js",
	"public/scripts/utils/floating_cart.js",
	"public/scripts/utils/navbar.js",
	"public/scripts/utils/numeric_stepper.js",
	"public/scripts/utils/card_detail.js",
	//"public/scripts/utils/scroll_hide_header.js",
	"public/scripts/utils/scroll_to_top.js",
	"public/scripts/utils/swiper_custom_config.js",
	"public/scripts/utils/svg_placeholders.js",
	"public/scripts/utils/window_sync.js",
	"public/scripts/utils/live_status_refresher.js",
	"public/scripts/application.js"
];

var applicationESModules = [
	"public/scripts/modules/application_es6.js"
]

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

	// ES6 modules need different processing
	gulp.src( applicationESModules )
		.pipe( $.sourcemaps.init() )
		.pipe( babel() )
		.pipe( $.uglify() )
		.pipe( $.sourcemaps.write( "." ) )
		.pipe( $.rename( { suffix: ".min" } ) )
		.pipe( gulp.dest( "public/dist/scripts/modules" ) )
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
	gulp.src( "node_modules/photoswipe/dist/photoswipe.esm.min.js" )
		.pipe( gulp.dest( "public/dist/scripts/modules" ) );
	gulp.src( "node_modules/photoswipe/dist/photoswipe-lightbox.esm.min.js" )
		.pipe( gulp.dest( "public/dist/scripts/modules" ) );
	// Flags for languages
	gulp.src( "node_modules/svg-country-flags/svg/*" )
		.pipe( gulp.dest( "public/dist/images/languages" ) )
		.on( "end", function() {

			// Some corrections in language flags
			var renameTr = {
				"cz": "cs",
				"gb": "en",
				"rs": "sr", // sr: Srpski
				"si": "sl", // sl: Slovenščina
				"ee": "et", // et: eesti
				"kz": "kk" // kk: Қазақ
			};
			Object.keys( renameTr ).forEach( function( key ) {
				gulp.src( "public/dist/images/languages/" + key + ".svg" )
					.pipe( rename( renameTr[ key ] + ".svg" ) )
					.pipe( gulp.dest( "public/dist/images/languages" ) );
			} );
		} );

	// The following alternative place for fontawesome files was added
	// after the vendor script node_modules/jquery-ui-bundle/jquery-ui.js has beed added.
	// See changeset 85cee100.
	// TODO: to be investigated & solved & removed...
	gulp.src( "node_modules/@fortawesome/fontawesome-free/webfonts/*" )
		.pipe( gulp.dest( "public/dist/@fortawesome/fontawesome-free/webfonts/" ) );
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
} );

// Build
var buildTasks = [
	"lint",
	"styles",
	"styles-vendor",
	"scripts",
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
