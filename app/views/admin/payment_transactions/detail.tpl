{dropdown_menu clearfix=false}
	{a action=api_dump id=$payment_transaction}{t}Výpis z API{/t}{/a}
{/dropdown_menu}

<h1>{$page_title}</h1>

<table class="table">
	<tbody>
		<tr>
			<th>{t}ID platební transakce (lokální){/t}</th>
			<td>{$payment_transaction->getId()}</td>
		</tr>
		<tr>
			<th>{t}Objednávka{/t}</th>
			<td>{a action="orders/detail" id=$order}{$order->getOrderNo()}{/a}</td>
		</tr>
		<tr>
			<th>{t}Celková cena k úhradě{/t}</th>
			<td>{!$payment_transaction->getPriceToPay()|display_price:"$currency"}</td>
		</tr>
		<tr>
			<th>{t}Platební brána{/t}</th>
			<td>
				{$payment_gateway}
				{if $payment_transaction->testingPayment()}
					<br><small><span class="badge badge-warning">{!"exclamation"|icon}</span> {t escape=no}<em>Testovací</em> režim{/t}</small>
				{/if}
			</td>
		</tr>
		<tr>
			<th>{t}Transakční ID{/t}</th>
			<td>{$payment_transaction->getPaymentTransactionId()|default:$mdash}</td>
		</tr>
		<tr>
			<th>{t}Transakce začala{/t}</th>
			<td>{$payment_transaction->getPaymentTransactionStartedAt()|format_datetime|default:$mdash}</td>
		</tr>
		<tr>
			<th>{t}IP adresa{/t}</th>
			<td>{$payment_transaction->getPaymentTransactionStartedFromAddr()|default:$mdash}</td>
		</tr>
		<tr>
			<th>{t}Stav platby{/t}</th>
			<td>{render partial=payment_status payment_status=$payment_transaction->getPaymentStatus()}</td>
		</tr>
		<tr>
			<th>{t}Datum poslední změny stavu{/t}</th>
			<td>{$payment_transaction->getPaymentStatusUpdatedAt()|format_datetime|default:$mdash}</td>
		</tr>
	</tbody>
</table>
