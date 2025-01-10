window.UTILS = window.UTILS || { };

window.UTILS.OffcanvasBasket = function() {
	var $ = window.jQuery;
	var $this = this;
	var lang = $( "html" ).attr( "lang" );
	this.element = $( "#offcanvas-basket .offcanvas-content .basket-content" );
	this.timeoutID = undefined;

	// Load basket content from server
	this.loadBasket = function() {
		$this.element.html( "" );
		if( $this.timeoutID ) {
			clearTimeout( $this.timeoutID );
		}
		$this.updateCountDisplay( null );
		$this.element.attr( "data-status", "loading" );
		$this.element.load( "/" + lang + "/baskets/detail", function( response, status, jqXHR ) {
			switch( status ) {
				case "success" :
					$this.element.attr( "data-status", "loaded" );
					var itemsCount = $this.getCountDisplay();
					console.log( "itemsCount", itemsCount );
					$this.updateCountDisplay( itemsCount );
					break;
				case "error" :
					$this.element.attr( "data-status", "error" );
					$this.element.parent().find( ".js--basket-error" ).html( "Error " +  jqXHR.status + ": " + jqXHR.statusText );
					break;
			}
		} );
	};

	// Replaces the offcanvas basket with the given HTML content and restores its scroll position
	this.redrawBasket = function( content ) {
		var pos = $( "#offcanvas-basket .basket-content__items" ).scrollTop();
		$this.element.html( content );
		$( "#offcanvas-basket .basket-content__items" ).scrollTop( pos );
	}

	// Show basket with custom content
	// window.basketOffcanvas.showCustomBasket( "this is <strong>custom html content</strong>", 3000 );
	this.showCustomBasket = function ( content, timeout ) {
		$this.element.html( content );
		var itemsCount = $this.getCountDisplay();
		console.log( "itemsCount", itemsCount );
		$this.updateCountDisplay( itemsCount );
		$this.element.attr( "data-status", "loaded" );
		window.offCanvas.showOffCanvas( "#offcanvas-basket", false );
		if( timeout ) {
			$this.timeoutID = setTimeout( function() { window.offCanvas.hideOffCanvas( "#offcanvas-basket" ); }, timeout );
		}
	};

	// Get items count from html attr
	this.getCountDisplay = function() {
		var countElem = $this.element.find( "*[data-items-count]" );
		if( countElem.length ) {
			var count = countElem.attr( "data-items-count" );
			return count;
		} else {
			return null;
		}
	};

	// Update items count
	this.updateCountDisplay = function ( count ) {
		var n;
		if( count ) {
			n = count;
			$( "#offcanvas-basket" ).find( ".basket-content" ).removeClass( "basket-content--empty" );
		} else {
			n = "";
			$( "#offcanvas-basket" ).find( ".basket-content" ).addClass( "basket-content--empty" );
		}
		$( "#offcanvas-basket" ).find( ".js--cart-num-items" ).text( n );
	};

	// Set handler for basket show event
	//$( "#offcanvas-basket" ).on( "bs-offcanvas-show", $this.loadBasket );
	if( document.querySelector( "#offcanvas-basket" ) ) {
		document.getElementById( "offcanvas-basket" ).addEventListener( "show.bs.offcanvas", $this.loadBasket );
	}

	// Update basket view when basket changed in another window
	window.addEventListener( "basket_remote_updated", function(){
		if( document.getElementById( "offcanvas-basket" ) && document.getElementById( "offcanvas-basket" ).classList.contains( "show" ) ) {
			$this.loadBasket();
		};
	} );

};
