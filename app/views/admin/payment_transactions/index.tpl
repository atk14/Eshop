<h1>{$page_title}</h1>

{render partial="shared/search_form"}

{if $finder->isEmpty()}

	<p>{t}Nebyla nelezena ani jedna transakce.{/t}</p>

{else}

	<table class="table">
		
		<thead>
			<tr>
				<th>#</th>
				<th>{t}Objednávka{/t}</th>
				<th>{t}Částka{/t}</th>
				<th>{t}Platební brána{/t}</th>
				<th>{t}Transakční id{/t}</th>
				<th>{t}Zákazník{/t}</th>
				{sortable key=created_at}<th>{t}Datum vytvoření{/t}</th>{/sortable}
				<th>{t}Stav{/t}</th>
				{sortable key=payment_status_updated_at}<th>{t}Datum stavu{/t}</td>{/sortable}
				<th></th>
			</tr>
		</thead>

		<tbody>
			{render partial="payment_transaction_item" from=$finder->getRecords()}
		</tbody>
	</table>

	{paginator}

{/if}

