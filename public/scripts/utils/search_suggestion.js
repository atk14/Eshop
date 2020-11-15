window.UTILS = window.UTILS || { };

window.UTILS.search_suggestion = {

	suggestingAreaVisible: false,
	suggestingCache: {},
	suggestingAreaNeedsToBePositioned: true,
	suggestingAreaOriginalContent: undefined,

	suggest: function( $field, $suggestingArea ) {
		var $form = $field.closest( "form" );
		var url = $form.attr( "action" );
		var search = $field.val();
		var fieldName = $field.attr( "name" );
		var data = {};

		if( window.UTILS.search_suggestion.suggestingAreaOriginalContent === undefined ){
			window.UTILS.search_suggestion.suggestingAreaOriginalContent = $suggestingArea.html();
		}
		
		search = search.trim();

		if( search === $suggestingArea.data( "suggesting-for" ) ) {
			return;
		}

		$suggestingArea.data( "suggesting-for", search );

		var searchFn = function( search ) {
			if( search === "" ) {
				$suggestingArea.html( window.UTILS.search_suggestion.suggestingAreaOriginalContent );
				return;
			}

			if ( window.UTILS.search_suggestion.suggestingCache[ search ] ) {
				$suggestingArea.html( window.UTILS.search_suggestion.suggestingCache[ search ] );
				// console.log( "replaced from cache" );
				return;
			}

			if( $suggestingArea.data( "suggesting" ) === "yes" ) {
				return;
			}

			$suggestingArea.data( "suggesting", "yes" );

			data[ "format" ] = "snippet";
			data[ fieldName ] = search;
			$.ajax( {
				dataType: "html",
				url: url,
				data: data,
				success: function( snippet ) {
					if( search === $suggestingArea.data( "suggesting-for" ) ) {
						window.UTILS.search_suggestion.suggestingCache[ search ] = snippet;
					}
					$suggestingArea.data( "suggesting", "" );
					if( search !== $suggestingArea.data( "suggesting-for" ) ) {
						searchFn( $suggestingArea.data( "suggesting-for" ) );
					} else {
						$suggestingArea.html( snippet );
						// console.log( "content replaced" );
					}
				}
			} );
		}

		searchFn( search );

		$( window ).on( "resize", function() {
			window.UTILS.search_suggestion.suggestingAreaNeedsToBePositioned = true;
			if( window.UTILS.search_suggestion.suggestingAreaVisible ) {

				// We need to delay a bit to wait for  possible transformations on the page
				setTimeOut( window.UTILS.search_suggestion.positionSuggestingArea( $field, $suggestingArea ), 5000);
			}
		} );
	},

	positionSuggestingArea: function( searchField, suggArea ) {

		// In the mobile layout the search input changes its location
		//if( !window.UTILS.search_suggestion.suggestingAreaNeedsToBePositioned ) {
		//	return;
		//}

		var fieldOffset = searchField.offset();
		suggArea.css( "top", fieldOffset.top + searchField.outerHeight() + 2 +"px");

		// Get x position of search field right edge
		var rightPos = fieldOffset.left + searchField.outerWidth();

		// Align suggestions to rightPos if there is enough room, center otherwise
		if( rightPos > suggArea.width() ) {
			suggArea.css( "left", rightPos - suggArea.width() + "px" );
		} else {
			suggArea.css( "left", ( document.body.clientWidth - suggArea.width() ) / 2 );
		}

		// console.log( "re-positioned" );

		window.UTILS.search_suggestion.suggestingAreaNeedsToBePositioned = false;
	}
};

window.UTILS.searchSuggestion = function( fieldClassName, suggestingAreaId ) {
	var $suggArea = $( "#" + suggestingAreaId );

	$( "." + fieldClassName ).on ( "keyup", function( e ) {
		window.UTILS.search_suggestion.suggest( $( this ), $suggArea );
	} );

	$( "body" ).on( "click keyup", function( e ) {
		var $activeElement = $( e.target );
		var id = $activeElement.attr( "id" );
		var searchFieldIsActiveAndEmpty = $activeElement.hasClass( fieldClassName ) && $activeElement.val().length === 0;
		if (
			searchFieldIsActiveAndEmpty || (
				!$activeElement.hasClass( fieldClassName ) &&
				id !== suggestingAreaId &&
				$activeElement.closest( "#" + suggestingAreaId ).length === 0
			)
		) {
			if( window.UTILS.search_suggestion.suggestingAreaVisible ) {
				$suggArea.fadeOut();
				window.UTILS.search_suggestion.suggestingAreaVisible = false;
				// console.log( "fadeOut" );
			}
		} else {
			window.UTILS.search_suggestion.positionSuggestingArea( $( "." + fieldClassName ), $suggArea );
			if( !window.UTILS.search_suggestion.suggestingAreaVisible ) {
				$suggArea.fadeIn();
				window.UTILS.search_suggestion.suggestingAreaVisible = true;
				// console.log( "fadeIn" );
			}
		}
	} );
};
