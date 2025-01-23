<?php
/**
 * Converts color in any css color format into rgba notation with the given alpha value
 *
 *	{"#ff0000"|color_to_rgba:"0.5"} -> rgba(255,0,0,0.5)
 *	{"rgb(100, 150, 170)"|color_to_rgba} -> rgba(100,150,170,1.0)
 *	{"hotpink"|color_to_rgba}
 *	{"hsl(120,60%,70%)"|color_to_rgba}
 */
function smarty_modifier_color_to_rgba($color,$alpha = "1.0"){
	if(!$color){
		return;
	}

	$alpha = (float)$alpha;
	
	$color_object = ariColor::newColor( $color );
	$new_color_object = $color_object->getNew( "alpha", $alpha );
	return $new_color_object->toCSS( "rgba" );
}
