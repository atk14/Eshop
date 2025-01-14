{render partial="shared/layout/content_header" title="{t}Vyhledávání{/t}"}

{form _class="form-inline"}
	{*
  <label for="id_q" class="control-label mb-2 mb-sm-0 mr-sm-2">{t}Hledat{/t}</label>
	*}
	<input type="search" name="q" value="{$form|field_value:"q"}" class="search text form-control mb-2 mb-sm-0 mr-sm-2" id="id_q">
	<button type="submit" class="btn btn-primary"> <i class="icon ion-ios-search-strong" title="{$button_text}"></i> {t}Hledat{/t}</button>
{/form}

{if $finder}

	{if $finder->isEmpty()}

		<p class="nosearchresult">{t}Nic nebylo nalezeno.{/t}</p>

	{else}
			<div class="card-grid card-deck--search">
				{foreach $finder->getItems() as $item}
					{display_search_result_item item=$item}
				{/foreach}
			</div>
		{paginator}
	{/if}

{/if}
