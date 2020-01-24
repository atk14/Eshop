{admin_menu for=$category}
{if $category->getTeaser()}
	{assign teaser $category->getTeaser()|markdown}
{/if}
{capture assign=title}
	{$category->getName()} <small>({$finder->getRecordsCount()})</small>
{/capture}
{render partial="shared/layout/content_header" title=$title teaser=$teaser image=$category->getImageUrl()|img_url:"200x200" }

<section class="border-top-0">
	{!$category->getDescription()|markdown}
</section>

{if $child_categories}
	<section class="child-categories">
		<h4>{t}Subcategories{/t}</h4>
		<nav>
			<div class="list-group list-group--categories">
				{foreach $child_categories as $c}
						{assign var=cc value=$c->getCategory()}
						{a path=$cc->getPath() _class="list-group-item list-group-item-action"}
						<div class="list-group-item-product">
							<div class="list-group-item-thumbnail">
							{if $cc->getImage()}
								{!$cc->getImage()|pupiq_img:"!60x60":"class='child-category__image'"}
							{/if}
							</div>
						<div class="child-category__text">
							<h4 class="child-category__text__title">{$cc->getName()} <small>({$c->getCardsCount()})</small> {!"angle-right"|icon}</h4>
							{if $cc->getTeaser()}
								<p class="child-category__text__teaser">{$cc->getTeaser()}</p>
							{/if}
						</div>
					{/a}
				{/foreach}
			</div>
		</nav>
	</section>
{/if}

{render partial='shared/filter/filter_form' form=$form}

<section class="products" id='cards'>
	<h4>{t}Products{/t}</h4>
		{if $finder->isEmpty()}
			<p>{t}No product has been found.{/t}</p>
		{else}
		<div class="card-deck card-deck--sized product-list" data-record_count="{$finder->getRecordsCount()}">
			{render partial='shared/ajax_pager/ajax_pager'}
		</div>
	{/if}
</section>

{if $canonical_path}
	{content for=head}
		<link rel="canonical" href="{link_to action=detail path=$canonical_path}" />
	{/content}
{/if}
