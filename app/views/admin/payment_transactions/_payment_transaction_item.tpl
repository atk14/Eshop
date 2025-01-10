{assign order $payment_transaction->getOrder()}
{assign currency $order->getCurrency()}

<tr>
	{highlight_search_query}
	<td>{$payment_transaction->getId()}</td>
	<td>{a action="orders/detail" id=$order}{$order->getOrderNo()}{/a}</td>
	{/highlight_search_query}
	<td align="right">{!$payment_transaction->getPriceToPay()|display_price:"$currency"}</td>
	<td>
		{$payment_transaction->getPaymentGateway()}
		{if $payment_transaction->testingPayment()}
			<br><small><span class="badge badge-warning">{!"exclamation"|icon}</span> {t escape=no}<em>Testovací</em> režim{/t}</small>
		{/if}
	</td>
	{highlight_search_query}
	<td>{$payment_transaction->getPaymentTransactionId()|default:"—"}</td>
	{/highlight_search_query}
	{highlight_search_query}
	<td>
		{if $order->getCompany()}{$order->getCompany()}, {/if}
		{$order->getFirstname()} {$order->getLastname()}
	</td>
	{/highlight_search_query}
	<td>{$payment_transaction->getCreatedAt()|format_datetime}</td>
	<td>{render partial=payment_status payment_status=$payment_transaction->getPaymentStatus()}</td>
	<td>{$payment_transaction->getPaymentStatusUpdatedAt()|format_datetime|default:$mdash}</td>
	<td>
		{dropdown_menu}
			{a action="detail" id=$payment_transaction}{t}Detail{/t}{/a}
			{a action=api_dump id=$payment_transaction}{t}Výpis z API{/t}{/a}
		{/dropdown_menu}
	</td>
</tr>
