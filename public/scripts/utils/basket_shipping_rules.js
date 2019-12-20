window.UTILS = window.UTILS || { };

window.UTILS.shipping_rules = { };

window.UTILS.shipping_rules.checkDependent = function( options ) {

	var $ = window.jQuery;

	var $determinants = $( "input[name='" + options.determinantName + "']" ),
	$determined = $( "input[name='" + options.determinedName + "']" ),
	$determinedRadio = $determined.closest( ".radio" );

	$determinants.each( function() {
		var $input = $( this ),
		rule = options.rules[ $input.val() ];

		if ( !rule ) {
			return;
		}

		$input.on( "click", function() {
			var enabled = 0;

			$determined.prop( "disabled", true );
			$determinedRadio.addClass( "radio--disabled" );

			$.each( rule, function( i, val ) {
				var value = val.toString();

				$determined
					.filter( "[value='" + value + "']" ).prop( "disabled", false )
					.closest( ".radio" ).removeClass( "radio--disabled" );
			} );

			enabled = $determined.filter( ":enabled" ).length;

			if ( options.checkFirstEnabled || enabled === 1 ) {
				$determined.filter( ":enabled:first" ).prop( "checked", true );
			}
		} );
	} );

	$determinants.filter( ":checked" ).click();
};
