{render partial="shared/layout/content_header" title=$page_title}
<div class="card-grid">
	{foreach $categories as $category}
	{a action="detail" path=$category->getSlug() _class="card"}
		{!$category->getImageUrl()|pupiq_img:"!400x300,enable_enlargement":"class='card-img-top'"}
		<div class="card-body">
			<h4 class="card-title">{$category->getName()}</h4>
			<div class="card-text">{!$category->getTeaser()|markdown}</div>
		</div>
	{/a}
	{/foreach}
</div>
