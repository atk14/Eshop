{if $payment_status && $payment_status->getCode()=="pending"}

<span class="text-muted">{!"hourglass-half"|icon} {$payment_status}</span>

{elseif $payment_status && $payment_status->getCode()=="paid"}

	<span class="text-success">{!"check"|icon} {$payment_status}</span>

{elseif $payment_status && $payment_status->getCode()=="cancelled"}

	<span class="text-danger">{!"remove"|icon} {$payment_status}</span>

{elseif $payment_status}

	{$payment_status}

{else}

	<span class="text-muted">{!"question"|icon} {t}neznámý stav{/t}</span>

{/if}
