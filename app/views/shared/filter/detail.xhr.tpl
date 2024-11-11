{*//#TODO js
//pokud bezi dalsi request, tak nic neprekreslujeme*}
(function() {
var form=$("#filter_form");
if( --form[0].filtering == 0 ) {
	var view=$('#cards');
	var empty=view.find('.js--empty-list');
	var nonempty=view.find;
	document.title = {jstring}{$page_title}{/jstring};
	$( "h1" ).html( {jstring}{$page_title}{/jstring} );
	$('head title').html(document.title);
	{if $fulltext_objects}
			var fo=$('#js--fulltext_objects');
			fo.show();
			fo.html({jstring}{render partial='search/fulltext_objects'}{/jstring});
		  Activa.hideSearchResults( );
	{else}
			$('#js--fulltext_objects').hide();
	{/if}

	{if $finder->isEmpty()}
			empty.html({jstring}{render partial=$finder->getPager()->getEmptyTemplate()}{/jstring});
			empty.show();
			view.find('.js--nonempty-list').hide();
			view.find('.js--pager-buttons').hide();
	{else}
			var $list = view.find('.js--pager-list');

			// nahrazeni obsahu budto v masonry nebo v normalnim seznamu
			var content={jstring}{render partial='shared/ajax_pager/ajax_pager_list'}{/jstring};
			if ( $list.hasClass( "masonry__items" ) ) {
				// vzhledem k tomu, ze knihovna colcade.js nema metodu pro prekresleni,
				// je zde tato podivnost...
				var $masonry = $list.closest( ".masonry" );
				$masonry.find(".masonry__item").addClass("d-none");
				$masonry.colcade(
					"prepend",
					$(content).filter(".masonry__item")
				);
				$masonry.find(".masonry__item.d-none").remove();
				$masonry.find('input[type="number"]').stepper();
			} else {
				$list.html(content);
			}

			view.find('.js--pager-buttons').html({jstring}{render partial='shared/ajax_pager/ajax_pager_buttons'}{/jstring});
			view.find('.js--nonempty-list').show();
			empty.hide();
	{/if}

	{if !$finder->getPager()->isXhr() || $finder->getPager()->isXhrOrdered()}
		{if !$doNotrerenderFilters}
			form.find('.js--filter_fields').html({jstring}{render partial="shared/filter/filter_fields"}{/jstring});
			form.find('.js--filter_head').html({jstring}{render partial="shared/form_field" fields=$form->top_fields() no_label_rendering=true}{/jstring});
			form.find('.js--active_filters').html({jstring}{render partial='shared/filter/active_filters' filter=$finder->filter}{/jstring});
			ATK14COMMON.filter_init( '#filter_form', true );
		{/if}
		form.find('.js--products-count').html({jstring}{render partial='shared/paging_count'}{/jstring});
		$('.js--products-count-number').html({$finder->getRecordsCount()});
		{*$('#child-categories').html({jstring}{render partial='shared/categories/child_categories'}{/jstring});*}
		if("NoUISlider" in window) {
			NoUISlider.Init();
		}
		var ajaxPager = view.find('.ajax_pager')[0].ajaxPager;
		$.extend(ajaxPager, {!$finder->getPager()->jsUpdate()});
		ajaxPager.count = {$finder->getRecords()|count};
		view.find('.ajax_pager').data( "count", {$finder->getRecords()|count});
		ajaxPager.reinit();
	{/if}


	var fce;
	var form=$('#paging_form form');
	if(form.length) {
		{if $paging_form}
			form.attr('action',{jstring}{!$paging_form->get_action()}{/jstring});
			//ATK14COMMON.Pager.init();
		{/if}
		fce = form[0].wasFiltered ? window.history.replaceState : window.history.pushState;
	} else {
		fce = window.history.pushState;
	}
	fce = fce.bind( window.history );
	fce({}, "", {jstring}{!$pageUrl}{/jstring});
	//form[0].wasFiltered = true;

	{if $add_searched_query}
		ACTIVA.UTIL.add_searched_query({jstring}{$add_searched_query}{/jstring});
	{/if}

	$( "#paging_form" ).html( {jstring}{render partial="shared/paging_form"}{/jstring} );
}
})();
