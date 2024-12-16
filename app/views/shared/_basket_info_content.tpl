{assign incl_vat $basket->displayPricesInclVat()}
<div class="js--basket_info_content">
  <span class="cart__icon">{!"shopping-cart"|icon}</span><span class="d-none d-sm-inline cart__name"> {t}Košík{/t}</span>
  {if !$basket->isEmpty()}
    {assign currency $basket->getCurrency()}
    <span class="cart-num-items{if $was_changed} cart-num-items--changed{/if}">{$basket->getItems()|sizeof}</span>
    <div class="cart__price">{!$basket->getItemsPrice($incl_vat)|display_price:"$currency,summary"}</div>
  {/if}
</div>
