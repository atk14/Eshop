var $new_link = $({jstring}{render partial="shared/favourite_product_icon" product=$product product_just_added=1}{/jstring});
var $new_header_fav = $({jstring}{render partial="shared/layout/header/header_favourites" product_just_added=1}{/jstring});
$(".js--header-favourites").replaceWith($new_header_fav);
$link.tooltip("hide");
$link.replaceWith($new_link);
$new_link.tooltip( {
	trigger: "manual",
	container: "html",
	html: true
} );
$new_link.tooltip("show");
setTimeout(function(){ $new_link.tooltip("hide"); }, 2000);
window.dispatchEvent( new Event( "favourites_updated" ) );
