{admin_menu for=$category}
{if $category->getTeaser()}
	{assign teaser $category->getTeaser()|markdown}
{/if}
{capture assign=title}
	{$category->getLongName()} <small>({$finder->getRecordsCount()})</small>
{/capture}
{assign image $category->getImageUrl()|img_url:"200x200"}
{if !$teaser|trim|strlen}
	{* no teaser? -> do not display the image *}
	{assign image ""}
{/if}
{render partial="shared/layout/content_header" title=$title teaser=$teaser image=$image}

<section class="border-top-0">
	{!$category->getDescription()|markdown}
</section>

{if $child_categories && $child_categories|@count>0}
	<section class="section--child-categories">
		{assign var="cat_list_class" ""}
		{foreach $child_categories as $c}
			{assign var=cc value=$c->getCategory()}
			{if $cc->getTeaser()}
				{assign var="cat_list_class_1" "list--categories--teasers"}
			{/if}
			{if $cc->getImage()}
				{assign var="cat_list_class_2" "list--categories--images"}
			{/if}
		{/foreach}
		<ul class="list-unstyled list--categories {$cat_list_class_1} {$cat_list_class_2}">
			{foreach $child_categories as $c}
			{assign var=cc value=$c->getCategory()}
			<li class="list-item">
				{a path=$cc->getPath()}
					{if $cc->getImage()}
						{!$cc->getImage()|pupiq_img:"!60x60":"class='child-category__image'"}
					{/if}
				<div class="child-category__text">
					<h4 class="child-category__text__title">{$cc->getName()} <small>({$c->getCardsCount()})</small>&nbsp;{!"angle-right"|icon}</h4>
					{if $cc->getTeaser()}
						<p class="child-category__text__teaser">{$cc->getTeaser()}</p>
					{/if}
				</div>
				{/a}
			</li>
			{/foreach}
		</ul>
	</section>
{/if}

{render partial='shared/filter/filter_form' form=$form}

<section class="section--list-products" id="cards">
	{*<h4>{t}Products{/t}</h4>*}
	{if $finder->isEmpty()}
		<p>{t}No product has been found.{/t}</p>
	{else}
		{render partial='shared/ajax_pager/ajax_pager'}
	{/if}
</section>

{if $canonical_path}
	{content for=head}
		<link rel="canonical" href="{link_to action=detail path=$canonical_path}" />
	{/content}
{/if}
