{if !$nav_class}
	{assign var="nav_class" "nav"}
{/if}
<ul class="{$nav_class} js--basket_info">
	<li class="nav-item">
		<a href="{link_to namespace="" action="baskets/edit"}" class="nav-link">
			{!"shopping-cart"|icon} {t}Košík{/t}
			{if !$basket->isEmpty()}
			<span class="cart-num-items">{$basket->getItems()|sizeof}</span>
			{/if}
		</a>
	</li>
</ul>
