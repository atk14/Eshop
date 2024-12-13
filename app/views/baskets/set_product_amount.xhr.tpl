$link.parent("p").hide().html({jstring}{!"check"|icon} {t amount=$amount unit=$product->getUnit()}Množství bylo upraveno na %1 %2{/t}{/jstring}).fadeIn("fast");

$( ".js--basket_info_content" ).replaceWith({jstring}{render partial="shared/basket_info_content" was_changed=true}{/jstring});

window.dispatchEvent( new Event( "basket_updated" ) );