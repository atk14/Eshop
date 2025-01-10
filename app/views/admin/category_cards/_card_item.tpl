{assign rank ""}
{if $sorting->getActiveKey()=="rank" && !$searching}
	{assign rank $finder->getOffset()+$__index__+1}
{/if}
{if $sorting->getActiveKey()=="rank-desc" && !$searching}
	{assign rank $finder->getTotalAmount()-($finder->getOffset()+$__index__)}
{/if}

<tr>
	<td>{render partial="shared/list_thumbnail" image=$card->getImage()}</td>
	{highlight_search_query}
	<td>{render partial="shared/product_codes" products=$card->getProducts(["visible" => null])}</td>
	<td>{$card->getName()}</td>
	{/highlight_search_query}
	<td>
		{$rank|default:$mdash}
	</td>
	<td>
		{$card->getCreatedAt()|format_datetime}
	</td>
	<td>
		{dropdown_menu}
			{a action="cards/edit" id=$card}{!"edit"|icon} {t}Edit product{/t}{/a}
			{a action="edit" category_id=$category card_id=$card->getId() rank=$rank}{icon glyph=edit} {t}Edit ranking{/t}{/a}
			{a_destroy category_id=$category card_id=$card->getId()}{icon glyph=remove} {t}Remove{/t}{/a_destroy}
		{/dropdown_menu}
	</td>
</tr>
