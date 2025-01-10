{assign favourites_count $favourite_products_accessor->getFavouriteProductsCount()}
{capture assign=link_class}nav-link header-favourites js--header-favourites{if $product_just_added} header-favourites--just-added{/if}{/capture}
{capture assign=fav_label}{t}Favourite items{/t}{/capture}
{a namespace="" action="favourite_products/index" _class=$link_class _title=$fav_label _aria-label=$fav_label}
  {if $favourites_count > 0}
    <span class="header-favourites__icon">
      {!"heart"|icon}<span class="header-favourites__icon__text">{if $favourites_count>999}999+{else}{!$favourites_count}{/if}</span>
    </span>
  {else}
    <span class="header-favourites__icon header-favourites__icon--empty">{!"heart"|icon:"regular"}</span>
  {/if}
{/a}
