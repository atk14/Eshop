window.basketOffcanvas.redrawBasket( {jstring}{render partial="shared/offcanvas_basket/detail"}{/jstring} );

$( ".js--basket_info_content" ).replaceWith({jstring}{render partial="shared/basket_info_content"}{/jstring});

window.dispatchEvent( new Event( "basket_updated" ) );

