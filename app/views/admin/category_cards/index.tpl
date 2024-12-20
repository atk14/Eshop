<h1>{button_create_new category_id=$category}{/button_create_new} {$page_title}</h1>

{render partial="shared/search_form"}

{if $finder->isEmpty()}

	<p>{t}No product has been found.{/t}</p>

{else}

	<table class="table">

	<thead>
		<tr>
			<th></th>
			{sortable key=catalog_id}<th>{t}Catalog number{/t}</th>{/sortable}
			<th>{t}Name{/t}</th>
			{sortable key=rank}<th>{t}Ranking{/t}</th>{/sortable}
			{sortable key=created_at}<th>{t}Created at{/t}</th>{/sortable}
			<th></th>
		</tr>
	</thead>
		
		<tbody>
			{render partial="card_item" from=$finder->getRecords() item=card}
		</tbody>

	</table>

	{paginator}

	<p><a href="{$csv_export_url}">{t}Export to CSV{/t}</a></p>

{/if}
