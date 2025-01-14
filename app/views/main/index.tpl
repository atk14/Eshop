{render partial="shared/slider" slider=$slider}


{if $category_recommended_cards}
	{admin_menu for=$category_recommended_cards}
	{render partial="shared/layout/content_header" title=$category_recommended_cards->getName() teaser=$category_recommended_cards->getTeaser()|markdown title_tag="h2"}
	
	{if $category_recommended_cards->getDescription()}
		{!$category_recommended_cards->getDescription()|markdown}
	{/if}
	
	{render partial="shared/card_list" cards=$category_recommended_cards->getVisibleCards() title=""}
{/if}

<article class="main-article">

	{if $page}
		{admin_menu for=$page}
	
		{render partial="shared/layout/content_header" title=$page->getTitle() teaser=$page->getTeaser()|markdown}
		
		<div class="row">
			<div class="col-12 col-md-7 col-lg-6">
				{!$page->getBody()|markdown}
			</div>
				<div class="col-12 col-md-5 col-lg-6">
					<a href="/prodejny/showroom-praha/" class="banner banner--image-text--halfwidth">
						<div class="banner__image">
							<img src="/public/dist/images/hp-shop-banner.jpg" class="img-fluid" alt="" width="500" height="356">
						</div>
						<div class="banner__text">{t escape=no}Navštivte naši novou prodejnu{/t}</div>
					</a>
				</div>
		</div>
	{else}
		{render partial="shared/layout/content_header" title=$page_title}
	{/if}
</article>

{assign var="logo_grid_items" LinkList::GetInstanceByCode("hp_logo_grid")}
{if $logo_grid_items && !$logo_grid_items->isEmpty($current_region)}
	{render partial="shared/logo_grid" link_list=$logo_grid_items show_titles=false fixed_size=true}
{/if}

{if $recent_articles}
	<section class="section--recent-articles">
		{capture assign=recent_articles_title}{t}Aktuality{/t}{/capture}
		{render partial="shared/layout/content_header" title=$recent_articles_title title_tag="h2"}
		<div class="card-grid">
			{foreach $recent_articles as $article}
				{a controller=articles action=detail id=$article _class="card"}
					<div class="card__image">
					{if $article->getImageUrl()}
						<img {!$article->getImageUrl()|img_attrs:"400x300xcrop"} class="card-img-top" alt="{$article->getTitle()}">
					{else}
						<img src="{$public}dist/images/default_image_400x300.svg" width="400" height="300" alt="" title="{t}no image{/t}" class="card-img-top default-image">
					{/if}
					</div>
					<div class="card-body">
						<div class="h2 card-title">{$article->getTitle()}</div>
						<div class="card-text">{$article->getTeaser()|markdown|strip_html}</div>
					</div>
				{/a}
			{/foreach}
		</div>
	</section>
{/if}
