{render partial="shared/layout/content_header" title=$page_title}
<div class="card-grid">
{foreach $brands as $brand}
	
	{a action="detail" id=$brand _class="card"}
		<div class="card__logo">
			{if $brand->getLogoUrl()}
				{assign "geometry" "400x400"}
				{assign "basePadding" 20}
				{assign "img_w" $brand->getLogoUrl()|img_width:$geometry}
				{assign "img_h" $brand->getLogoUrl()|img_height:$geometry}
				{assign "img_ratio" $img_w/$img_h}
				<div class="card__logo__wrap" style="padding: {$basePadding|calculate_logogrid_padding:$img_ratio}%;">
					{!$brand->getLogoUrl()|pupiq_img:"400x400x#ffffff,format=png":"class='card-img-top'"}
				</div>
			{/if}
		</div>
		<div class="card-body">
			<h4 class="card-title">{$brand->getName()}</h4>
			<div class="card-text">{$brand->getTeaser()}</div>
		</div>
	{/a}
	
{/foreach}
</div>
