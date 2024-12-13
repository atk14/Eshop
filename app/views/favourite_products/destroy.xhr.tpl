$link.tooltip("hide");
$link.replaceWith({jstring}{render partial="shared/favourite_product_icon" product=$product}{/jstring});
var $new_header_fav = $({jstring}{render partial="shared/layout/header/header_favourites"}{/jstring});
$(".js--header-favourites").replaceWith($new_header_fav);
window.dispatchEvent( new Event( "favourites_updated" ) );