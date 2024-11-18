( function( window, undefined ) {
	"use strict";

	var $ = window.jQuery;
	if ( !window.ATK14COMMON ) {
		window.ATK14COMMON = {};
	}
	var ATK14COMMON = window.ATK14COMMON;

	ATK14COMMON.Pager = function( $element ) {
		this.$pager = $( $element );
		this.offsetName = "offset";
		this.limitName = "count";
		this.pagerName = "pager";

		$.extend( this, this.$pager.data( "pager" ) );
		this.reinit();

		if ( this.$list.length === 0 ) {
			console.warn( "AjaxPager:",
					"List not found. Set class 'js--pager-list' to proper element"
					);
			return;
		}

		$( window ).on( "popstate",
				( function( ) { this.doPaging( window.document.location.href, undefined, { noScroll: true } ); } ).bind( this )
		);

	};

	ATK14COMMON.Pager.prototype.reinit = function( ) {
			this.$list = this.$pager.find( ".js--pager-list" );
			this.$buttons = {
				first: this.$pager.find( ".js--first" ),
				previous: this.$pager.find( ".js--previous" ),
				next: this.$pager.find( ".js--next" )
			};
			this.remains = this.$pager.find( ".js--remains" );

			Object.keys( this.$buttons ).forEach( ( function( key ) {
				var $button = this.$buttons[ key ];
				$button.pager_role = key;
				this.buttonize( $button, key );
			} ).bind( this ) );

			this.updateCount();
			this.updateNextButton();

			if ( this.form ) {
				this.$form = $( "#" + this.form );
				// console.log( "this.form: " + this.form ); // e.g. "form_categories_card_list_paging"

				if ( this.$form.length === 1 && !this.$form[ 0 ].pagerInited ) {

					$( "#paging_form" ).on( "click", function( e ) {
						e.preventDefault();
						if ( e.target.tagName.toUpperCase() === "A" ) {
							var $a = $( e.target );
							var $form = $( "#paging_form" ).find( "form" ); 
							$a.parent().siblings().removeClass( "active" );
							$a.parent().addClass( "active" );
							if( $form.data( "remote" ) ) {
								// $form is an ATK14 remote form
								var $filter_form = $( "#filter_form" );
								$form.attr( "action", $a.attr( "href" ) );
								$filter_form.attr( "action", $a.data( "filter_href" ));
								$filter_form[ 0 ].filtering++;
								console.log( $form.attr( "action" ) );
								$form.submit();
							} else {
								// $form is not an ATK14 remote form
								window.location.href = $a.attr( "href" ) + "#pager";
							}
						}
						return false;
					} );

					/*
					$form.find( "a" ).click( function() {
						var $a = $( this );
						var $filter_form = $( "#filter_form" );
						$a.parent().siblings().removeClass( "active" );
						$a.parent().addClass( "active" );
						$form.attr( "action", $a.attr( "href" ));
						$filter_form.attr( "action", $a.attr( "href" ));
						$filter_form[ 0 ].filtering++;
						$form.submit();

						return false;
					} );
					*/

					/*
					this.$form.find( "select,input[type=radio]" ).change( ( function() {
						this.$form.submit();
					} ).bind( this ) );
					*/

					/*
					this.$form.submit( ( function( e ) {
						var replaceParams = this.$form.serialize();
						var replaceUrl;
						replaceUrl = this.$form.attr( "action" );
						if ( replaceParams !== "order=default" ) {
							replaceUrl += replaceUrl.indexOf( "?" ) === -1 ? "?" : "&";
							replaceUrl += replaceParams;
						}
						this.url = replaceUrl;
						$.ajax( {
							type: "post",
							url: this.$form.attr( "action" ),
							data: this.$form.serialize(),
							dataType: "json",
							success: ( function( data ) {
								this.updatePager( data, { noScroll: true } ) ;
								window.document.activeElement.blur();
								window.history.replaceState(
									{}, "",
									replaceUrl
								);
							} ).bind( this )
						} );
						e.preventDefault();
						return false;
					}
					).bind( this ) );
					*/

					this.$form[ 0 ].pagerInited = true;
				}
			}
	};

	ATK14COMMON.Pager.init = function() {
		$( ".ajax_pager" ).each( function( k, $e ) {
			if ( $e.ajaxPager ) {
				$e.ajaxPager.reinit();
			} else {
				$e.ajaxPager = new ATK14COMMON.Pager( $e );
			}
		} );
	};

	ATK14COMMON.Pager.prototype.updateCount = function() {
		this.count = parseInt( this.$pager.data( "count" ) );
	};

	ATK14COMMON.Pager.prototype.getText = function( text ) {
		var add = [ text ] ;
		var replace = [] ;
		var char = "s".charCodeAt( 0 );

		for ( var i = 1; i < arguments.length ; i++ )  {
			var amount = arguments[ i ];
			if ( amount <= 0 ) {
				add.push( "0" );
			} else if ( amount > 1 ) {
				add.push( amount > 4 ? "ss" : "s" );
			} else {
				add.push( "" );
			}
			replace.push( { search: "%" + String.fromCharCode( char ), replace: amount } );
			char++;
		}

		var gname;
		for ( i = add.length ; i > 0 ; i-- ) {
			gname = add.join( "/" );
			if ( this.texts[ gname ] !== undefined ) {
				gname = this.texts[ gname ];
				for ( var j = 0; j < replace.length ; j++ ) {
					gname = gname.replace( replace[ j ].search, replace[ j ].replace );
				}
				return gname;
			}
			if ( i === 1 ) { break; }
			add[ i - 1 ] = "";
		}

		return gname;
	};

	ATK14COMMON.Pager.prototype.buttonize = function( button, key ) {
		button.on( "click", ( function() { return this.click( button ); } ).bind( this ) );
		button.name = key;
	};

	/* Add params to url, that are greater than 0 */
	ATK14COMMON.Pager.prototype.addToUrl = function( url, param ) {
		var addParam = {};
		if ( param.offset > 0 ) {
			addParam[ this.offsetName ] = param.offset;
		}
		if ( param.limit > 0 && param.limit !== this.pageSize ) {
			addParam[ this.limitName ] = param.limit;
		}
		if ( param.pager ) {
			addParam[ this.pagerName ] = 1;
		}
		addParam = $.param( addParam );
		if ( !addParam ) {
			return url;
		}
		var index = url.indexOf( "?" ), add = index === -1 ? "?" : "&";
		if ( url.indexOf( "//" ) === -1 ) {
			var href = window.location.href;
			var i = href.indexOf( "/", href.indexOf( "/" ) + 2 );
			url = href.substr( 0, i ) + url;
		}
		return url + add + addParam;
	};

	ATK14COMMON.Pager.prototype.updateButton = function( button, url, text, amount ) {
		button.attr( "href", url );
		button.toggleClass( "disabled", !url );
		if ( text ) {
			button.html( this.getText( text, amount ) );
			button.restoreText = null;
		}
	};

	ATK14COMMON.Pager.prototype.updateNextButton = function() {
		var url, text;
		var remain = this.total - this.offset - this.count;

		if ( remain <= 0 ) {
			url = ""; text = "next_page";
			remain = undefined;
		} else if ( this.count + this.pageSize > this.sectionSize ) {
			text = "next_page";
			url = this.addToUrl( this.url, {
				offset: this.offset + this.count,
				limit: this.newPageSize()
			} );
			remain = undefined;
			this.$buttons.next.addClass("next-page").removeClass("next-items");
		} else {
			text = "next";
			url = this.addToUrl( this.url, {
				offset: this.offset + this.count
			} );
			remain = Math.min( remain, this.pageSize );
			this.$buttons.next.addClass("next-items").removeClass("next-page");
		}
		this.updateButton( this.$buttons.next, url, text, remain );
	};

	/*Return number of items on previous/next paxe */
	ATK14COMMON.Pager.prototype.newPageSize = function() {
		return this.pagingPer === "section" ? this.sectionSize : this.pageSize ;
	};

	ATK14COMMON.Pager.prototype.updatePager = function( data, options ) {
		if ( data.pageSize ) {
			this.pageSize = data.pageSize;
		}

		var $items = $( data.items );
		var $masonry = this.$list.closest( ".masonry" );
		var origOffset = $( window ).scrollTop();
		var $body = $( "body" );
		var origOverflowAnchor = $body.css( "overflow-anchor" );

		if ( data.paginator ) {
			$( "#js--ajax_pager__paginator" ).replaceWith( data.paginator );
		}

		$body.css( "overflow-anchor", "none" );

		if ( data.offset !== this.offset + this.count ||
			   data.count + this.count > this.sectionSize ||
				 data.forceReplace
			 ) {

			// Replacing content either of a masonry or a classic list
			if ( this.$list.hasClass( "masonry__items" ) ) {
				$masonry.find( ".masonry__item" ).addClass( "d-none" );
				$masonry.colcade(
					"prepend",
					$items.filter( ".masonry__item" )
				);
				$masonry.find( ".masonry__item.d-none" ).remove();
				$masonry.find( "input[type='number']" ).stepper();
			} else {
				this.$list.html( $items );
			}

			this.offset = data.offset;
			this.count = data.count;

			if ( options.addToHistory  ) {
				window.history.pushState(
					{}, "",
					this.addToUrl( this.url, { offset: this.offset, limit: this.count } )
				);
			}
			if ( !options.noScroll ) {
				var $el = this.$pager.find( "a[id=anchor--" + this.pagerName + "-top]" );
				$( "html,body" ).animate( { scrollTop: $el.offset().top }, "slow" );
			}
		} else {

			// Appending new items either to a masonry or to a classic list
			if ( this.$list.hasClass( "masonry__items" ) ) {
				$masonry.find( ".masonry__item" ).addClass( "custom-marker" );
				this.$list.closest( ".masonry" ).colcade(
					"append",
					$items.filter( ".masonry__item" )
				);
				$masonry.find( ".masonry__item:not(.custom-marker) input[type='number']" ).stepper();
				$masonry.find( ".masonry__item" ).removeClass( "custom-marker" );
			} else {
				$items.hide().appendTo( this.$list ).fadeIn( "slow" );
			}

			this.count += data.count;

			// This is a fallback when setting the overflow-anchor doesn't help.
			// There is a small delay to make sure that the Colcade finished its job.
			setTimeout( function() {
				$( "html,body" ).scrollTop( origOffset );
			}, 10 ); // very soon

			if ( options.addToHistory ) {
				window.history.replaceState(
					{}, "",
					this.addToUrl( this.url, { offset: this.offset, limit: this.count } )
				);
			}
		}

		this.updateButton( this.$buttons.first, this.offset > this.sectionSize ?
			this.addToUrl(this.url, { limit: this.sectionSize } ) :
			null
		);
		var pSize = this.newPageSize();
		this.updateButton( this.$buttons.previous, this.offset ?
				this.addToUrl( this.url, { offset: this.offset - pSize, limit: pSize } ) : "" );
		this.updateNextButton();
		this.updateRemains();

		$body.css( "overflow-anchor", origOverflowAnchor );

		/**
		 * Pustime si callback, pokud jsme si ho s daty poslali
		 */
		if ( data.callback !== undefined ) {
			var cb = new Function( "return " + data.callback );
			( cb() )();
		}
	};

	ATK14COMMON.Pager.prototype.updateRemains = function() {
		var remain = this.total - this.offset - this.count;
		var text;
		text = this.getText( "remain", remain, this.total );
		this.remains.html( text );
	};

	ATK14COMMON.Pager.prototype.click = function( button ) {
		var href = button.attr( "href" );
		if ( !href ) {
			return false; }
		button.restoreText = button.html();

		//X button.html( this.getText( "loading" ) );
		this.doPaging( href, button );
		return false;
	};

	ATK14COMMON.Pager.prototype.doPaging = function( href, button, updatePagerOptions ) {
			updatePagerOptions = updatePagerOptions || {};
			updatePagerOptions.addToHistory = button;
			$.ajax( {
				url:     this.addToUrl( href, { "pager": 1 } ),
				success: ( function( data ) {
							this.updatePager( data, updatePagerOptions );
							} ).bind( this ),
				complete: function( ) {
					if ( button && button.restoreText ) {
						button.html( button.restoreText );
						button.restoreText = undefined;
					}
				},
				dataType: "json"
			} );
	};

	$( function() {
		ATK14COMMON.Pager.init();
	} );

} )( this );
