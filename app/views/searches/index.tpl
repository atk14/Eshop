<div class="section__header">
	<h1>{$page_title}</h1>
</div>
{content for="title_bar_title"}{t}Vyhledávání{/t}{/content}

{form _class="form-inline"}
  <label for="id_q" class="control-label mb-2 mb-sm-0 mr-sm-2">{t}Hledat{/t}</label>
	<input type="search" name="q" value="{$form|field_value:"q"}" class="search text form-control mb-2 mb-sm-0 mr-sm-2" id="id_q">
	<button type="submit" class="btn btn-primary"> <i class="icon ion-ios-search-strong" title="{$button_text}"></i> {t}Hledat{/t}</button>
{/form}

{if $finder}

	{if $finder->isEmpty()}

		<p class="nosearchresult">{t}Nic nebylo nalezeno.{/t}</p>

	{else}

		<ul class="search-results-list">
			{foreach $finder->getItems() as $item}
				{display_search_result_item item=$item}
			{/foreach}
		</ul>

		{paginator}
	
	{/if}

{/if}
