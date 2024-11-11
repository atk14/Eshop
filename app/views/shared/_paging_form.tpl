{assign sorting_possibilities $pager->getSortingPossibilities()}

{if $paging_form && sizeof($sorting_possibilities)>1}
{form_remote _novalidate="novalidate" _class='cards_paging_form' form=$paging_form}

	{if sizeof($sorting_possibilities)>1}
	<div class="cards_sorting">
		<div class="cards_sorting__title">{t}Seřadit dle{/t}</div>
		<ul class="cards_sorting__options">
		{foreach $sorting_possibilities as $sorting_possibility}
			<li class="cards_sorting__item{if $sorting_possibility->isActive()} active{/if}"><a href="{$sorting_possibility->getUrl()}" data-filter_href="{$sorting_possibility->getFilterFormAction()}">{$sorting_possibility->getTitle()}</a></li>
		{/foreach}
		</ul>
	</div>
	{/if}

{/form_remote}
{/if}
