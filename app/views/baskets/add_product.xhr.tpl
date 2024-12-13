{gtm_datalayer}
$("#basket_modal_dialog").remove();
$(".modal-backdrop").remove();

var $modal = $({jstring}{modal id=basket_modal_dialog title="{t}Produkt byl přidán do košíku{/t}"}
	{render partial="product_added"}
{/modal}{/jstring});

$modal.appendTo("body");

$("#basket_modal_dialog").modal("show");

$( ".js--basket_info_content" ).replaceWith({jstring}{render partial="shared/basket_info_content" was_changed=true}{/jstring});

window.dispatchEvent( new Event( "basket_updated" ) );
