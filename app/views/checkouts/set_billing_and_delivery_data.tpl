{render partial="shared/checkout_navigation"}

{render partial="shared/layout/content_header" title=$page_title}

{if $delivery_addresses}
	<ul class="card-grid cards--addresses">
		{foreach $delivery_addresses as $da name=addresscounter}
			{assign addresscounter $smarty.foreach.addresscounter.iteration}
			<li class="card bg-light">
				<div class="card-body js--card-address {*if $addresscounter == 1}card--active{/if*}">
					{render partial="shared/delivery_address" delivery_address=$da}
				</div>
				<div class="card-footer card__actions">
					<button class="js--predefined-address card__action btn btn-primary btn-sm" data-json="{$da->toJson()}">{!"check"|icon} <span>{t}Použít{/t}</span></button>
						{a_destroy action="delivery_addresses/destroy" id=$da->getId() _title="{t}Smazat adresu{/t}" _confirm="{t}Opravdu chcete smazat tuto adresu?{/t}" _class="card__action btn btn-secondary btn-sm"}{!"remove"|icon} <span>{t}Smazat{/t}</span>{/a_destroy}
				</div>
			</li>
		{/foreach}
	</ul>
{/if}

{form _class="form form-horizontal" _novalidate="novalidate"}
	{render partial="shared/form_error"}

	{if !$delivery_address_editable_by_user}
		{render partial="invoice_data"}

		{render partial="delivery_address" delivery_address_editable_by_user=$delivery_address_editable_by_user}
		{*render partial="billing_and_delivery_data_form__delivery_point_selected"*}
	{else}
		{render partial="delivery_address"}
		
		<div class="form__body">
			{render partial="shared/form_field" fields="fill_in_invoice_address"}
		</div>

		{render partial="invoice_data"}
	{/if}

	<div class="form__footer">
		{a action="checkouts/set_payment_and_delivery_method" _class="btn btn-lg btn-secondary btn--back btn--arrow-l"}{t}Zpět na dopravu a platbu{/t}{/a}
		{render partial="shared/form_button" class="btn btn-primary btn-lg"}
	</div>
{/form}
