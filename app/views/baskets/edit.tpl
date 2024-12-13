{render partial="shared/checkout_navigation"}

<div class="basket-edit-header-wrapper">
	{capture assign=page_title}{t}Košík{/t}{/capture}
	{render partial="shared/layout/content_header" title=$page_title}
	{render partial="regions/set_region_form" form=$set_region_form}
</div>

{assign currency $basket->getCurrency()}
{assign basket_vouchers $basket->getBasketVouchers()}
{assign basket_campaigns $basket->getBasketCampaigns()}
{assign vouchers_anchor_set 0}

{form} {* Tento formular nesmi mit nastaveno novalidate *}

{* Defaultni button musi byt na privnim miste *}
<button type="submit" class="btn btn-secondary btn-lg nojs-only">{t}Recalculate basket content{/t}</button>

{render partial="edit_form_content"}

<div class="form__footer">
	{a action="categories/index" _class="btn btn-lg btn-secondary btn--back btn--arrow-l"}{t}Back to catalog{/t}{/a}
	<button type="submit" class="btn btn-secondary btn-lg nojs-only">{t}Recalculate basket content{/t}</button>
	<button type="submit" name="continue" class="btn btn-primary btn-lg">{t}Select shipping and payment{/t}</button>
</div>

{/form}

{render partial="shared/basket_changed_modal"}