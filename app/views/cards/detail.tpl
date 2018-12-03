{foreach $categories as $category}
	<ol class="breadcrumb">
		<li class="breadcrumb-item">{a action="main/index"}{"ATK14_APPLICATION_NAME"|dump_constant}{/a}</li>
			{foreach $category->getPathOfCategories() as $c}
				<li class="breadcrumb-item">
					{a action="categories/detail" path=$c->getPath()}{$c->getName()}{/a}
				</li>
			{/foreach}
		<li class="breadcrumb-item active">{$card->getName()}</li>
	</ol>
{/foreach}

<section class="product-basic-info border-top-0">
	<h1>{$page_title}</h1>

	<p class="lead">{$card->getTeaser()}</p>

	{assign brand $card->getBrand()}
	{if $brand}
		{t}Brand:{/t} {a action="brands/detail" id=$brand}{$brand->getName()}{/a}
	{/if}
</section>

{render partial="shared/photo_gallery" object=$card}

{render partial="shared/attachments" object=$card}

{foreach $card->getCardSections() as $section}
	<section>
	<h3>{$section->getName()}</h3>

	{!$section->getBody()|markdown}

	{*** Variants ***}
	{if $section->getTypeCode()=="variants"}
		{render partial=variants}
	{/if}

	{*** Technical Specifications ***}
	{if $section->getTypeCode()=="tech_spec"}
		{render partial="technical_specifications"}
	{/if}

	{render partial="shared/photo_gallery" object=$section}

	{render partial="shared/attachments" object=$section}
	</section>
{/foreach}

{render partial="related_cards"}
{render partial="consumables"}
{render partial="accessories"}

{a action="information_requests/create_new" card_id=$card _class="btn btn-primary"}{t}Are you interested in this product?{/t}{/a}
