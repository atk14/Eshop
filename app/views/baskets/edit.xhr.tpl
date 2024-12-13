$( "#js--edit_form_content" ).replaceWith( {jstring}{render partial="edit_form_content"}{/jstring} );
$( "#js--basket_price_summarization td" ).fadeOut( 0 ).fadeIn( 100 );
$( ".js--basket_info_content" ).replaceWith({jstring}{render partial="shared/basket_info_content" was_changed=true}{/jstring});
window.dispatchEvent( new Event( "basket_updated" ) );
