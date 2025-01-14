{assign var=related_cards value=$card->getViewableRelatedCards()}

{if $related_cards}
	<section class="linked-cards linked-cards--related-cards">
		<h3 class="h3">{t}Related products{/t}</h3>
		<div class="card-grid--mini">
			{foreach $related_cards as $c}
				{render partial="linked_product_item"}
			{/foreach}
		</div>
	</section>
{/if}
