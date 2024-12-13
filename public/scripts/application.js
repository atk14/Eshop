/* global window */
( function( window, $, undefined ) {
	"use strict";
	var document = window.document,
	UTILS = window.UTILS, // Uncomment this if you need something from UTILS

	APPLICATION = {
		common: {

			// Application-wide code.
			init: function() {

				// Restores email addresses misted by the no_spam helper
				UTILS.unobfuscateEmails();

				// Links with the "blank" class are pointing to new window
				UTILS.linksTargetBlank();

				// Form hints.
				UTILS.formHints();

				// Init Swiper
				UTILS.initSwiper();

				// Navbar dropdowns work on mouseover
				UTILS.initNavbar();
				
				// Hide header on scroll - disabled by default, more info in utils/scroll_hide_header.js
				// UTILS.hideHeaderOnScroll();

				// Floating cart info show/hide 
				new UTILS.floatingCart();

				window.UTILS.searchSuggestion( "js--search", "js--suggesting" );

				// Expanding/collapsing FAQ items
				UTILS.initFAQ();

				// Set proper scale for product card image scaling on hover
				UTILS.setCardHoverScale();
				window.addEventListener( "resize", UTILS.setCardHoverScale );

				// Init NoUiSlider

				// Sticky Scroll Sidebar
				// To make it work enable sticky-sidebar.js in vendorScripts list in gulpfile.js
				window.UTILS.initStickySidebar();

				// Init offvanvas component
				window.offCanvas = new window.UTILS.BSOffCanvas();

				// Init offcanvas basket preview
				window.basketOffcanvas = new window.UTILS.OffcanvasBasket();

				// Add-to-cart animation on cards button
				$( ".js--card-add-to-cart-btn" ).on( "click", function() {
					$( this ).closest( ".card" ).addClass( "card--in-basket" );
				} );

				// Tlacitka +/- mnozstvi u stepper inputu
				if( document.querySelector( "[data-spinner-button]" ) ) {
					new UTILS.numericStepperHandler();
				}

				// Scroll to top button handler
				UTILS.scrollToTopBtn();

				// Image placeholders ( for design + development purposes );
				if( UTILS.SVGPlaceholders ){
					UTILS.SVGPlaceholders.SVGPlaceholders();
				}

				// sync
				new UTILS.WindowSync();
				new UTILS.LiveStatusRefresher();

			}

		},

		categories: {

			// Action-specific code
			detail: function() {
				window.ATK14COMMON.filter_init( "#filter_form" );
			}
		},

		main: {

			// Controller-wide code.
			init: function() {
			},

			// Action-specific code
			index: function() {
			}
		},

		logins: {
			create_new: function() {
				document.getElementById( "id_login" ).focus();
			}
		},

		users: {

			// Controller-wide code.
			init: function() {
			},

			// Action-specific code
			create_new: function() {

				// Check whether login is available.
				UTILS.loginAvaliabilityChecker();
				
			}
		},

		cards: {

			// Controller-wide code.
			init: function() {
			},

			// Action-specific code
			detail: function() {
				UTILS.initCardDetail();

			}
		},

		baskets: {

			// Controller-wide code.
			init: function() {
			},

			// Action-specific code
			edit: function() {
				var $basketForm = $( "#form_baskets_edit" );
				var autoRefreshinterval = 1000; // 1 sec

				// Odstranit polozku
				/*$( ".js--basket-destroy" ).click( function( e ) {
					var $e = $( e.currentTarget );
					$.ajax( {
						"url": $e.data( "url" ),
						"type": "POST",
						"dataType": "json",
						"success": function( data ) {
										if ( data.emptyBasket ) {
											return location.reload();
										}
										var $table = $e.closest( "table" );
										// UTILS.update_basket( data, $table );
										var $tr = $e.closest( "tr" );
										$tr.fadeOut( $tr.remove );
										}
					} );
					return false;
				} );*/

				// Automaticke prepocitavani kosiku
				setTimeout( function() {
					UTILS.edit_basket_form.auto_refresh( autoRefreshinterval );
				}, autoRefreshinterval );

				// Zachyceni stisku klavesy enter v inputu na polozce kosiku a prekresleni kosiku.
				// Atribut data-ininitial maji pouze inputy na polozkach v kosiku.
				$basketForm.on( "keydown", "input[type=number][data-initial]", function( event ) {
					if ( event.which === 13 ) {
						// console.log( "about to refresh from keydown" );
						UTILS.edit_basket_form.refresh();
						return false;
					}
				} );

				// Auto submission of the set-region form
				$("#form_regions_set_region select").on( "change",  function() {
					$( document.body ).addClass( "loading" );
					$(this).parent( "form" ).submit();
				} );

				// Emit basket update event (because page may be loaded after basket item deletion)
				window.dispatchEvent( new Event( "basket_updated" ) );
			},

			empty_basket: function() {
				// Emit basket update event (because page may be loaded after basket item deletion)
				window.dispatchEvent( new Event( "basket_updated" ) );
			}
		},

		checkouts: {

			// Controller-wide code.
			init: function() {
			},

			set_payment_and_delivery_method: function() {
				UTILS.shipping_rules.checkDependent( {
					determinantName: "delivery_method_id",
					determinedName: "payment_method_id",
					rules: $( "#form_checkouts_set_payment_and_delivery_method" ).data( "rules" )
				} );

				$( "#form_checkouts_set_payment_and_delivery_method :checked" )
					.parents( "li" ).each( function() {
					$( this ).last().addClass( "checked" );
				} );

				$( "#form_checkouts_set_payment_and_delivery_method :radio" ).change( function() {
					if ( $( this ).is( ":checked" ) ) {
						$( this ).closest( ".list--radios" )
							.find( ".checked input[name='group_checkbox']" )
							.attr( "checked", false );
						$( this ).closest( ".list--radios" )
							.find( ".checked" )
							.removeClass( "checked" );
						$( this ).parents( "li" ).last().addClass( "checked" );
					}
				} );

				// Auto submission of the set-region form
				$("#form_regions_set_region select").on( "change",  function() {
					$( document.body ).addClass( "loading" );
					$(this).parent( "form" ).submit();
				} );
			},

			// Action-specific code
			set_billing_and_delivery_data: function() {

				// Checkbox na zadavani fakturacni adresy
				if( $( "#id_fill_in_invoice_address" ).length && $( "#id_fill_in_invoice_address" ).is( ":checked" ) === false ){
					$( "#invoice-address-fields" ).css( "display", "none" );
				}

				$( "#id_fill_in_invoice_address" ).on( "change", function() {
					if( $( "#id_fill_in_invoice_address" ).is( ":checked" ) === true ) {
						$( "#invoice-address-fields" ).show( 150 );
					} else {
						$( "#invoice-address-fields" ).hide( 150 );
					}
				} );



				// Vyber dorucovacich adres
				$( ".js--predefined-address" ).click( function() {
					var data = $( this ).data( "json" ),
						$card = $( this ).closest( ".card" ).find( ".js--card-address" ),
						$cards = $( ".js--card-address" );
					$cards.removeClass( "card--active" );
					$card.addClass( "card--active" );

					$( "#form_checkouts_set_billing_and_delivery_data input, #form_checkouts_set_billing_and_delivery_data select" ).each( function() {
							var name = this.name;
							var $input = $( this );
							var origColor = $input.css( "color" );
							var backgroundColor = $input.css( "background-color" );

							if ( name.substr( 0, 9 ) === "delivery_" ) {
								name = name.substr( 9 );
							} else if ( name !== "phone" ) {
								return;
							}
							if ( data[ name ] !== undefined ) {
								this.value = data[ name ];
								this.animate( [{ color: backgroundColor }, { color: origColor }], { duration: 400, iterations: 1 } );
							}
					} );
				} );
			},

			summary: function() {
				// Before order submit, check if confirmation checkbox is checked
				// If not show reminder
				var btn = $( "form#form_checkouts_summary .btn[type='submit']" );
				var confirmationFormGroup = $( "form#form_checkouts_summary .form-group--id_confirmation" );
				var confirmationChkBox = $( "form#form_checkouts_summary #id_confirmation" );
				var reminderTimeout;
				$( "form#form_checkouts_summary" ).on( "submit", function( e ){
					var errMsg = confirmationChkBox.parents().find( "*[data-confirmation-reminder]" ).data( "confirmation-reminder" );
					btn.popover( {
						customClass: "popover--danger popover--bold",
						placement: "top",
						content: errMsg,
					} );
					if( confirmationChkBox.prop( "checked" ) !== true ) {
						e.preventDefault();
						btn.popover( "show" );
						confirmationFormGroup.addClass( "form-group--has-error" );
						reminderTimeout = setTimeout( hideReminderPopover, 3000 );
					}else{
						//e.preventDefault();
						hideReminderPopover();
						confirmationFormGroup.removeClass( "form-group--has-error" );					
					}
				} );
				confirmationChkBox.on( "change", function(){
					if( confirmationChkBox.prop( "checked" ) ){
						hideReminderPopover();
						confirmationFormGroup.removeClass( "form-group--has-error" );
					}
				} );
				var hideReminderPopover = function() {
					clearTimeout( reminderTimeout );
					btn.popover( "hide" );
				}
			}

		},

		stores: {

			// Controller-wide code.
			init: function() {
			},

			// Action-specific code
			index: function() {

				// Init map
				new UTILS.MultiMap( document.querySelector( ".stores_v2" ) );
				
				// eslint-disable-next-line no-unused-vars
				var storeList = new UTILS.filterableList( {
					searchInput: 	$( "#stores-filter__input" ),
					clearButton: 	$( "#stores-filter__clear" ),
					submitButton: $( "#stores-filter__submit" ),
					listItems:		$( ".js-stores-cards > .js-store-item" ),
					searchTextSelector: ".js-search-data",
				} );

			},

			// Action-specific code
			detail: function() {

				// Init map
				new UTILS.SimpleMap( document.querySelector( ".map_v2" ) );
			}

		},

		styleguides: {

			// Controller-wide code.
			init: function() {
				UTILS.initStyleguides();
			}

		},

		orders: {
			index: function() {

				// Toggle visibility of remaining hidden card thumbnails
				$( ".js--card-thumbnails__more" ).on( "click", function( e ) {
					e.preventDefault();

					// Fix parent td width to prevent layout shifting
					var parentTableCell = $( this ).closest( "td" );
					parentTableCell.css( "width", ( parentTableCell.width() + 2 * parseFloat( parentTableCell.css("padding") ) ) + "px" );
					$( this ).parent().find( ".d-none" ).removeClass( "d-none" ).addClass("shown");
					$( this ).remove();
				} );

				// Reset fixed parent td width on resize
				$( window ).on( "resize", function() {
					$( "table.table--orders td.order__thumbnails" ).css( "width", "auto" );
				} );
			},

			finish: function() {
				// Emit basket update event
				window.dispatchEvent( new Event( "basket_updated" ) );
			}
		},

		// In this json, the actions for namespace "api" can be defined
		api: {
			common: {

				// Application-wide code.
				init: function() {

					// Restores email addresses misted by the no_spam helper
					UTILS.unobfuscateEmails();

					// Links with the "blank" class are pointing to new window
					UTILS.linksTargetBlank();

					// Form hints.
					UTILS.formHints();
				}
			}

		}

	};

	/*
	 * Garber-Irish DOM-based routing.
	 * See: http://goo.gl/z9dmd
	 */
	APPLICATION.INITIALIZER = {
		exec: function( namespace, controller, action ) {
			var ns = APPLICATION,
				c = controller,
				a = action;

			if( namespace && namespace.length > 0 && ns[ namespace ] ) {
				ns = ns[ namespace ];
			}

			if ( a === undefined ) {
				a = "init";
			}

			if ( c !== "" && ns[ c ] && typeof ns[ c ][ a ] === "function" ) {
				ns[ c ][ a ]();
			}
		},

		init: function() {
			var body = document.body,
			namespace = body.getAttribute( "data-namespace" ),
			controller = body.getAttribute( "data-controller" ),
			action = body.getAttribute( "data-action" );

			APPLICATION.INITIALIZER.exec( namespace, "common" );
			APPLICATION.INITIALIZER.exec( namespace, controller );
			APPLICATION.INITIALIZER.exec( namespace, controller, action );
		}
	};

	// Expose APPLICATION to the global object.
	window.APPLICATION = APPLICATION;

	// Initialize application.
	APPLICATION.INITIALIZER.init();
} )( window, window.jQuery );
