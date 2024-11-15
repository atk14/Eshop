<h1>{$page_title}</h1>

<dl class="dl-horizontal">
	<dt>current_status_code</dt>
	<dd>{$current_status_code|default:"?"}</dd>

	<dt>internal_status</dt>
	<dd>{$internal_status|default:"?"}</dd>

	<dt>data</dt>
	<dd>{dump var=$data}</dd>

	<dt>datum dotazu do API</dt>
	<dd>{$current_datetime|format_datetime_with_seconds}</dd>
</dl>
