{render partial="shared/layout/content_header" title=$page_title}
<div class="card-grid">
{foreach $collections as $collection}
		{a action="detail" id=$collection _class="card"}
		{!$collection->getImageUrl()|pupiq_img:"!400x400":"class='card-img-top'"}
		<div class="card-body">
			<h3 class="card-title">{$collection->getName()}</h3>
			<p class="card-text">{$collection->getTeaser()}</p>
		</div>
		{/a}
{/foreach}
</div>