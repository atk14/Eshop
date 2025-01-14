{admin_menu for=$page}
<article>
	{*<header class="content-header">
		<h1>{$page->getTitle()}</h1>
		<div class="teaser">
		{!$page->getTeaser()|markdown}
		</div>
	</header>*}
	{assign var="colorbg" false}
	{if $creator}
		{assign var="image" $creator->getImageUrl()}
		{if !$image}
			{assign var="image" $page->getImageUrl()}
		{/if}
		{assign var="colorbg" true}
	{/if}
	{render partial="shared/layout/content_header" title=$page->getTitle() teaser=$page->getTeaser()|markdown}
	
	<section class="page__body">
		{!$page->getBody()|markdown}
	</section>
	
</article>

{if $page->getCode()=="contact"}
	{render_component controller="contact_messages" action="create_new"}
{/if}

{if $creator}
	{render_component controller="creator_cards" action="index" page_id=$page->getId()}
{/if}

{if $child_pages}
	<section class="section--child-pages">
		{*<h4>{t}Subpages{/t}</h4>*}
		{*<ul class="list-unstyled">
		{foreach $child_pages as $child_page}
			<li>
				{a action=detail id=$child_page}
					{if $child_page->getImageUrl()}
						<img {!$child_page->getImageUrl()|img_attrs:"80x80"} alt="" class="img-thumbnail">
					{/if}
					{$child_page->getTitle()}
				{/a}
			</li>
		{/foreach}
		</ul>*}
		
		<div class="card-grid--mini">
			{foreach $child_pages as $child_page}
				{a action=detail id=$child_page _class="card"}
					{if $child_page->getImageUrl()}
						<img {!$child_page->getImageUrl()|img_attrs:"300x225xcrop"} alt="" class="card-img-top">
					{/if}
					<div class="card-body">
						<h5>{$child_page->getTitle()}</h5>
						<p>{$child_page->getTeaser()|markdown|strip_html|truncate:300}</p>
					</div>
				{/a}
			{/foreach}
		</div>
		
	</section>
{/if}

