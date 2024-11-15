<h1>{$page_title}</h1>

<dl class="dl-horizontal">
	<dt>{t}ID platební transakce (lokální){/t}</dt>
	<dd>{$payment_transaction->getId()}</dd>

	<dt>{t}objednávka{/t}</dt>
	<dd>{a action="orders/detail" id=$order}{$order->getOrderNo()}{/a}</dd>

	<dt>{t}platební brána{/t}</dt>
	<dd>
		{$payment_transaction->getPaymentGateway()}
		{if $payment_transaction->testingPayment()}
			<br><small><span class="badge badge-warning">{!"exclamation"|icon}</span> {t escape=no}<em>Testovací</em> režim{/t}</small>
		{/if}
	</dd>

	<dt>current_status_code</dt>
	<dd>{$current_status_code|default:"?"}</dd>

	<dt>internal_status</dt>
	<dd>{$internal_status|default:"?"}</dd>

	<dt>data</dt>
	<dd>{dump var=$data}</dd>

	<dt>datum dotazu do API</dt>
	<dd>{$current_datetime|format_datetime_with_seconds}</dd>
</dl>
