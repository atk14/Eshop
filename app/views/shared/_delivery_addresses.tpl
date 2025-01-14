	<ul class="card-grid cards--addresses">
		{foreach $delivery_addresses as $da}
			{capture assign="confirm"}{t escape=false}Doručovací adresa bude smazána. Pokračovat?{/t}{/capture}
			<li class="card bg-light">
				<div class="card-body js--card-address {if $addresscounter == 1}card--active{/if}">
					{render partial="shared/delivery_address" delivery_address=$da}
				</div>
				<div class="card-footer card__actions">
						{a _class="card__action btn btn-secondary btn-sm" action="edit" id=$da}{!"edit"|icon} <span>{t}upravit{/t}</span>{/a}
						{a_destroy _class="card__action btn btn-secondary btn-sm" action="destroy" id=$da _confirm=$confirm}{!"remove"|icon} <span>{t}smazat{/t}</span>{/a_destroy}
				</div>
			</li>
		{/foreach}
	</ul>
