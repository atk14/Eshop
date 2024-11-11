{admin_menu for=$category}
{if $category->getTeaser()}
	{assign teaser $category->getTeaser()|markdown}
{/if}
{capture assign=title}
	{$category->getLongName()} {* <small>({$finder->getRecordsCount()})</small> *}
{/capture}
{assign image $category->getImageUrl()|img_url:"600x600"}
{if !$teaser|trim|strlen}
	{* no teaser? -> do not display the image *}
	{assign image ""}
{/if}
{render partial="shared/layout/content_header" title=$title teaser=$teaser image=$image}

<section class="border-top-0">
	{!$category->getDescription()|markdown}
</section>

{if $child_categories && $child_categories|@count>0}
	{render partial="child_categories"}
{/if}

{render partial='shared/filter/filter_form' form=$form}

{remove_if_contains_no_text}
<div id="paging_form">
	{render partial="shared/paging_form" paging_form=$paging_form}
</div>
{/remove_if_contains_no_text}

<section class="section--list-products" id="cards">
	{render partial='shared/ajax_pager/ajax_pager'}
</section>
