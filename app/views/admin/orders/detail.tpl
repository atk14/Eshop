{assign var=user value=$order->getUser()}
{assign var=vouchers value=$order->getVouchers()}
{assign var=currency value=$order->getCurrency()}
{assign var=default_currency value=Currency::GetDefaultCurrency()}
{assign var=responsible_user value=$order->getResponsibleUser()}
{assign var=payment_transaction value=$order->getPaymentTransaction()}
{assign var=order_history_items value=$order->getOrderHistory()}
{assign var=status_ready value=OrderStatus::FindByCode("ready")}
{assign var=status_reminder value=OrderStatus::FindByCode("ready_reminder")}
{assign var=status_delivered value=OrderStatus::FindByCode("delivered")}
{assign var=order_status_code value=$order->getOrderStatus()->getCode()}

<h1>
	{$page_title}

	{render partial="action_buttons"}
</h1>

<table class="table">
	<tbody>
		{*
		<tr>
			<th>{t}Číslo objednávky{/t}</th>
			<td>{$order->getOrderNo()}</td>
		</tr>
		*}

		<tr>
			<th>{t}Zodpovědná osoba{/t}</th>
			<td>
				{if $responsible_user}{$responsible_user}{else}{t escape=false}Není určena &rarr; {/t}{a action="set_responsible_user" id=$order _class="btn btn-outline-primary btn-sm mb-2"}přiřadit{/a}{/if}
			</td>
		</tr>

		<tr>
			<th>{t}Stav objednávky{/t}</th>
			<td>
				{if $order->getAllowedNextOrderStatuses()}
					{a action="order_order_statuses/create_new" order_id=$order _class="btn btn-outline-primary btn-sm mb-2"}{t}Změnit stav objednávky{/t}{/a}
				{/if}
				<ul>
						{if $order_history_items}
							{foreach $order_history_items as $k => $history}
								<li>
									{$history->getOrderStatusSetAt()|format_datetime} <strong title="{t code=$history->getOrderStatus()->getCode()}kód: %1{/t}">{render partial="shared/order_status" order_status=$history->getOrderStatus() order=null}</strong>
									{assign created_by $history->getOrderStatusSetByUser()}
									{if $created_by} ({$created_by->getName()}){else}({t}automatický stav{/t}){/if}
									{assign responsible $history->getResponsiblePerson()}
									{if $responsible} &mdash; přiřazeno: {$responsible->getName()}{/if}
									{if $history->getNote()}
										<br>
										<code>
										{!$history->getNote()|h|nl2br}
										</code>
									{/if}
								</li>
							{/foreach}
						{/if}
				</ul>
			</td>
		</tr>

		{*
		<tr>
			<th>{t}Datum vytvoření{/t}</th>
			<td>{$order->getCreatedAt()|format_datetime}</td>
		</tr>
		*}
		<tr>
			<th>{t}Zákazník{/t}</th>
			<td>
				{if $user}
					<ul>
						<li>Id: {a action="users/detail" id=$user}{$user->getId()}{/a}</li>
						<li>Login: {$user->getLogin()}</li>
					</ul>
				{else}
					<em>{t}nákup bez registrace{/t}</em><br>
				{/if}
				{t}Jméno:{/t} {$order->getFirstname()} {$order->getLastname()}<br>
				{t}E-mail:{/t} {$order->getEmail()}<br>
				{t}Telefon:{/t} {$order->getPhone()|default:$mdash}
			</td>
		</tr>
		<tr>
			<th>{t}Fakturační adresa{/t}</th>
			<td>{render partial="shared/order/invoice_address"}
				{if $show_cross_border_transactions_within_eu_info}
					<br>
					{if $order->isVatIdValidForCrossBorderTransactionsWithinEu()}
						{!"ok"|icon} {t}validní DIČ pro zahraniční transakce v rámci EU{/t}
					{elseif $order->isVatIdValidForCrossBorderTransactionsWithinEu()===false}
						{!"remove"|icon} {t}nevalidní DIČ pro zahraniční transakce v rámci EU{/t}
					{else}
						{t}validace DIČ pro zahraniční transakce v rámci EU nebyla provedena{/t}
					{/if}
					&rarr; <a href="{$vat_id_validation_url}" class="popup" data-width="800" data-height="600">{t}validovat DIČ{/t}</a>
				{/if}
			</td>
		</tr>
		<tr>
			<th>{t}Doručovací adresa{/t}</th>
			<td>{render partial="shared/order/delivery_address"}</td>
		</tr>


		<tr>
			<th>{t}Cena{/t}</th>
			<td>
				<ul>
					<li>{t}Celková cena bez DPH:{/t} {!$order->getTotalPrice()|display_price:"$currency"}</li>
					<li>{t}Celková cena s DPH:{/t} {!$order->getTotalPriceInclVat()|display_price:"$currency"}</li>
					{foreach $order->getCampaigns() as $campaign}
						<li>{$campaign}: -{!$campaign->getDiscountAmount()|display_price:"$currency"}</li>
					{/foreach}
					{foreach $order->getVouchers() as $voucher}
						<li>{t code=$voucher}Dárkový poukaz %1{/t}: -{!$voucher->getDiscountAmount()|display_price:"$currency"}</li>
					{/foreach}
					<li><strong>{t}Celková cena k úhradě:{/t} {!$order->getPriceToPay()|display_price:"$currency,summary=auto"}</strong></li>
					<li>{t}Celkem uhrazeno:{/t} {!$order->getPricePaid()|display_price:"$currency"|default:$mdash}</li>
				</ul>
			</td>
		</tr>

		<tr>
			<th>{t}Způsob dopravy{/t}</th>
			<td>
				<ul>
					<li>{$order->getDeliveryMethod()}{render partial="shared/order/delivery_method_data"}</li>
					<li>{t}Poplatek:{/t} {!$order->getDeliveryFee()|display_price:"$currency"}</li>
					<li>{t}Poplatek s DPH:{/t} {!$order->getDeliveryFeeInclVat()|display_price:"$currency"}</li>
					<li>{t}Číslo zásilky pro sledování:{/t}
						{assign tracking_number $order->getTrackingNumber()}
						{if $tracking_number}
							{if $order->getTrackingUrl()}
								<a href="{$order->getTrackingUrl()}">{$tracking_number}</a>
							{else}
								{$tracking_number} ({t}vzor pro sledovací URL není nastaven{/t} &rarr; {a action="delivery_methods/edit" id=$order->getDeliveryMethod()}nastavit?{/a})
							{/if}
						{else}
							{t}Není vyplněno{/t}{/if}
						</li>
				</ul>
			</td>
		</tr>

		<tr>
			<th>{t}Platební metoda{/t}</th>
			<td>
				<ul>
					<li>{$order->getPaymentMethod()}</li>
					<li>{t}Poplatek:{/t} {!$order->getPaymentFee()|display_price:"$currency"}</li>
					<li>{t}Poplatek s DPH:{/t} {!$order->getPaymentFeeInclVat()|display_price:"$currency"}</li>
					{if $payment_transaction}
						<li>
							{if $payment_transaction->testingPayment()}
								 <span class="text-warning">{!"circle-exclamation"|icon}</span> {t escape=no}<em>Testovací</em> platební transakce{/t}
							{else}
								{t}Platební transakce{/t}
							{/if}
							<ul>
								<li>{a action="payment_transactions/detail" id=$payment_transaction}#{$payment_transaction->getId()}{/a}</li>
								<li>{t}Platební brána:{/t} {$payment_transaction->getPaymentGateway()}</li>
								<li>{t}Transakční ID:{/t} {$payment_transaction->getPaymentTransactionId()|default:"?"}</li>
								<li>{t}Stav platby:{/t} {$payment_transaction->getPaymentStatus()|default:"?"}</li>
								<li>{t}Stav platby aktualizován:{/t} {$payment_transaction->getPaymentStatusUpdatedAt()|format_datetime|default:$mdash}</li>
							</ul>
						</li>
					{/if}
				</ul>
			</td>
		</tr>

		<tr>
			<th>{t}Poznámka{/t}</th>
			<td>
				{!$order->getNote()|h|nl2br}
			</td>
		</tr>

	</tbody>
</table>


<h3>{t}Položky objednávky{/t}</h3>

{if $has_digital_contents}
	{if $digital_contents_url}
		<p>{t}Odkaz pro stažení digitálních produktů:{/t}<br><a href="{$digital_contents_url}">{$digital_contents_url}</a></p>
	{else}
		<p><em>{t}Odkaz pro stažení digitálních produktů ještě není připraven.{/t}</em></p>
	{/if}	
{/if}

{render partial="shared/basket_or_order_items" object=$order}

{if $ordered_vouchers}
	
	<h4>{t}Objednané poukazy{/t}</h4>

	<table class="table">
		<thead>
			<th>{t}Kód{/t}</th>
			<th>{t}Hodnota{/t}</th>
			<th>{t}Je aktivní?{/t}</th>
			<th>{t}Byl použit?{/t}</th>
			<th>{t}Platnost do{/t}</th>
			<th></th>
		</thead>
		<tbody>
			{foreach $ordered_vouchers as $voucher}
				<tr>
					<td>{$voucher}</th>
					<td>{!$voucher->getDiscountAmount()|display_price}</th>
					<td>{render partial="shared/active_state" object=$voucher}</td>
					<td>
						{$voucher->hasBeenUsed()|display_bool}
						{if $voucher->hasBeenUsed()}
							<span title="{t}Najít objednávky s tímto kupónem{/t}">{a action="orders/index" search=$voucher->getVoucherCode()}{!"external-link-alt"|icon} {/a}</span>
						{/if}
					</td>
					<td>{$voucher->getValidTo()|format_datetime|default:$mdash}</td>
					<td>
						{dropdown_menu}
							<a href="{$voucher->getUrl()}">{!"eye-open"|icon} {t}Zobrazit náhled{/t}</a>
							{a action="vouchers/edit" id=$voucher}{!"edit"|icon} {t}Edit{/t}{/a}
						{/dropdown_menu}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>

{/if}

{render partial="action_buttons"}
