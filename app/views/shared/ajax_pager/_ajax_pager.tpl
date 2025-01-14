{*
 *
 *	{render partial="shared/ajax_pager/ajax_pager" list_class="card-grid product-list"}
 *}

{if !$pager}{assign var=pager value=$finder->getPager()}{/if}
{if !$list_class}{assign list_class "card-grid product-list"}{/if}

{assign var=items value=$finder->getRecords()}
{assign var=item_name value="`$pager->getItemVariable()`"} {* e.g. "item" *}

{if !$pager->isXhr()}
<div class="ajax_pager" data-count={count($finder->getRecords())} data-pager="{$pager->jsData()}" id="{$pager->getName()}">
{/if}
	<a id="anchor--{$pager->getName()}-top" class="sr-only">&nbsp;</a>
	<div class="js--empty-list">
		{if $finder->isEmpty()}
			{render partial=$finder->getPager()->getEmptyTemplate()} {* e.g. "shared/ajax_pager/empty_list"*}
		{/if}
	</div>
	<div class="{$list_class} js--pager-list js--nonempty-list">
		{render partial="shared/ajax_pager/ajax_pager_list"}
	</div>
	<div class="pagination-container">
		<div class="pager-buttons js--pager-buttons">
			{render partial="shared/ajax_pager/ajax_pager_buttons"}
		</div>
	</div>
{if !$pager->isXhr()}
</div>
{/if}
