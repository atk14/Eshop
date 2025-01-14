{assign var=consumables value=$card->getConsumables()}

{if $consumables}
	<section class="linked-cards linked-cards--consumables">
		<h3 class="h3">{t}Consumables{/t}</h3>
		<div class="card-grid--mini">
			{foreach $consumables as $c}
				{render partial="linked_product_item"}
			{/foreach}
		</div>
	</section>
{/if}