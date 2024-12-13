/* global Packeta */

/**
 * Inicializace widgetu pro Zasilkovnu na zvolenem html elementu.
 *
 * Usage:
 *
 * $("#atk14-widget-zasilkovna").Zasilkovna( {
 *    target_input_id: "id_delivery_service_branch_id",
 *    countries: [ "CZ", "SK" ],
 *    language: "en"
 * } );
 *
 */
( function( window ) {
	var $ = window.jQuery;

	$.fn.extend( {
		Zasilkovna: function( options ) {

			options = options || { };
			var defaultOptions = {
				target_input_id: null,
				countries: [ "CZ" ], // [ "CZ", "SK" ]
				language: "cs"
			};

			options = $.extend( defaultOptions, options );

			var apiKey = this.data( "api_key" );
			var zasilkovnaDiv = this;
			var targetElement = window.document.getElementById( options.target_input_id );
			var country = options.countries.join( "," ).toLowerCase();

			if ( targetElement === null ) {
				console.warn( "no target input set" );
			}

			if ( zasilkovnaDiv.length === 0 ) {
				console.warn( "no element usable for Zasilkovna widget" );
				return false;
			}

			$.getScript( "https://widget.packeta.com/v6/www/js/library.js" )
			.done( function() {
				console.log( "loading Zasilkovna library finished" );

					$( targetElement.form ).hide();

					Packeta.Widget.pick(
						apiKey,
						function( point ) {
							if ( point ) {
								this.value = point.id;
								targetElement.value = point.id;
								targetElement.form.submit();
							}
							$( "#delivery_service_branch_select" ).modal( "hide" );
						},
						{
							country: country, language: options.language
						},
						zasilkovnaDiv[ 0 ]
					);
			} )
			.fail( function() {
				console.log( "loading Zasilkovna failed" );
			} );
		},

		GLS: function( options ) {
			options = options || { };
			var defaultOptions = {
				target_input_id: null,
				countries: [ "CZ" ], // [ "CZ", "SK" ]
				language: "cs"
			};
			options = $.extend( defaultOptions, options );

			var targetElement = window.document.getElementById( options.target_input_id );

			$( targetElement.form ).hide();

			window.addEventListener( "message", function( event ) {
				var parcelshopData = event.data.parcelshop;
				var dataDisplay = $( "#atk14-widget-branch" );

				// $( targetElement.form ).show();

				if ( dataDisplay.length ) {
					dataDisplay.find( ".branch-name" ).text( parcelshopData.detail.name );
					var addressAry = [
						parcelshopData.detail.address,
						parcelshopData.detail.zipcode,
						parcelshopData.detail.city
					];
					dataDisplay.find( ".branch-address" ).text( addressAry.join( ", " ) );
					if ( parcelshopData.psFoto ) {
						var photoSrc = "data:image/jpg;base64, " + parcelshopData.psFoto;
						dataDisplay.find( "img" ).attr( "src", photoSrc );
					} else {
						dataDisplay.find( "img" ).attr( "src", null );
					}
				}
				targetElement.value = parcelshopData.detail.pclshopid;
				$( targetElement.form ).submit();
			}, false );
		},

		CzechPost: function( options ) {
			options = options || { };
			var defaultOptions = {
				target_input_id: null,
				countries: [ "CZ" ], // [ "CZ", "SK" ]
				language: "cs"
			};
			options = $.extend( defaultOptions, options );

			var targetElement = window.document.getElementById( options.target_input_id );

			$( targetElement.form ).hide();

			window.addEventListener( "message", function( event ) {
				if ( event.data.message === "pickerResult" ) {
					var parcelshopData = event.data.point;

					targetElement.value = parcelshopData.zip;
					targetElement.form.submit();
				}
			}, false );
		},

		PPL: function( options ) {

			options = options || { };
			var defaultOptions = {
				target_input_id: null,
				countries: [ "CZ" ], // [ "CZ", "SK" ]
				language: "cs"
			};

			options = $.extend( defaultOptions, options );

			var targetDiv = this;
			var targetElement = window.document.getElementById( options.target_input_id );
			// eslint-disable-next-line no-unused-vars
			var country = options.countries.join( "," ).toLowerCase();

			if ( targetElement === null ) {
				console.warn( "no target input set" );
			}

			if ( targetDiv.length === 0 ) {
				console.warn( "no element usable for PPL widget" );
				return false;
			}

			//
			// Create a link element to load the main.css file
			var link = document.createElement( "link" );
			link.rel = "stylesheet";
			link.href = "https://www.ppl.cz/sources/map/main.css";

			// Add the script+href link to the document head
			document.head.appendChild( link );

			$.getScript( "https://www.ppl.cz/sources/map/main.js" )
			.done( function() {
				console.log( "loading PPL library finished" );

				$( targetElement.form ).hide();
				document.addEventListener( "ppl-parcelshop-map", function( event ) {
					var parcelshopData = event.detail;

					targetElement.value = parcelshopData.code;
					targetElement.form.submit();
					}
				);
			} )
			.fail( function() {
				console.log( "loading PPL failed" );
			} );
		}
	} );

} )( this );
