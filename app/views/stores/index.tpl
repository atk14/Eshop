{render partial="shared/layout/content_header" title=$page_title}

{if !$stores}

	<p>{t}Currently we have no store.{/t}</p>

{else}

{render partial="map_tiles_provider"}

{capture assign="jsdata"}
var storeLocatorData = [
	{foreach from=$stores item=$store}
	{if $store->getLocationLat() && $store->getLocationLng()}
	{
		id: {$store->getID()},
		image: "{!$store->getImageUrl()|img_url:"140x140xcrop"}",
		title: "{$store->getName()}",
		address: "{$store->getAddress()|h|nl2br|replace:'<br />':'<br>'|escape:"url"|strip}",
		detailURL: "{$store|link_to_store}",
		lat: {$store->getLocationLat()},
		lng: {$store->getLocationLng()},
		isOpen: {if $store->isOpen()}"{t}Otevřeno{/t}"{else}false{/if},
	},
	{/if}
	{/foreach}
];
{/capture}
			{*<pre>{!$jsdata}</pre>*}
			{javascript_tag}
				{!$jsdata}
			{/javascript_tag}
			{* uncomment line below for testing map with 1000 markers dataset*}
			{*render partial="xlarge_dataset_test"*}
	
	{*
		Map container: 
		data-enable_clusters: set true to enable marker clusters
		data-cluster_distance: set max distance for markers to make cluster in px (default: 80)
	*}

	<div class="stores-index__map stores_v2" data-enable_clusters="true" data-cluster_distance="80">
		<div class="preloader" id="stores-index__maploader">
			<div class="spinner-border text-secondary" role="status">
				<span class="sr-only">{t escape="no"}Loading map&hellip;{/t}</span>
			</div>
			<div>{t escape="no"}Loading map&hellip;{/t}</div>
		</div>
	</div>

	{if $stores|count > 5}
		<form class="form-inline stores-filter" id="stores-filter" autocomplete="off">
			<input class="form-control" id="stores-filter__input" placeholder="{t}Search stores{/t}" aria-label="{t}Search stores{/t}">
			<button class="btn btn-link d-none" id="stores-filter__clear" tabindex="-1" type="reset" aria-label="{t}Reset search{/t}">{!"times"|icon}</button>
			<button class="btn btn-link" id="stores-filter__submit" tabindex="-1" type="submit" aria-label="{t}Search stores{/t}">{!"search"|icon}</button>
		</form>
	{/if}
		<div class="card-grid js-stores-cards">
			{render partial="store_item" from=$stores item=store}
		</div>
{/if}

