{*
 * Template for header.
 * $use_large_search_bar determines if large ful width search bar will be used.
 * $show_search_in_mobile - if true search field is always shown on mobile.
 * 												if false, search is shown as icon (except on homepage)
 * 
 *}
{assign "use_large_search_bar" 0}
{assign show_search_in_mobile 0}
{if $controller=="main" && $action=="index"}
	{assign show_search_in_mobile 1}
{/if}
{capture assign="basket_info"}
	{render partial="shared/layout/header/basket_info" nav_class="navbar-nav"}
{/capture}
{* from which breakpoint up should menu be visible - value should be the same as in scss! *}
{assign var=nav_breakpoint value="md"}
{assign var=appname value="ATK14_APPLICATION_NAME"|dump_constant}

<header class="header-main" id="header-main">

	{render partial="shared/layout/header/navbar_top_mobile"}	
	{render partial="shared/layout/header/navbar_top_desktop"}
	
	<div class="container-fluid header-main__mainbar">
		<div class="mainbar__controls">
			<div class="mainbar__top mainbar__links">
			</div>
			{if !$use_large_search_bar}
			<div class="mainbar__middle mainbar__search_cart">
				<form class="form-inline" action="{link_to namespace="" action="searches/index"}" id="js--main_search_field">
					<input name="q" type="text" class="form-control js--search" placeholder="{t}Hledat{/t}" autocomplete="off">
					<button type="submit" class="btn btn-primary" title="{t}Hledat{/t}">{!"search"|icon}</button>
				</form>
				{render partial="shared/layout/header/header_favourites"}
				<div class="mainbar__cartinfo js--mainbar__cartinfo">
					{render partial="shared/layout/header/basket_info"}
				</div>
			</div>
			{/if}
			<div class="mainbar__bottom">
			</div>
	</div>
	<div class="logospace">
		{a action="main/index" namespace="" _title=$link_title _class="logospace__logo"}<img src="/public/dist/images/header-logo.svg" alt="{$appname}" height="80">{/a}
	</div>
</div>






	<nav class="navbar navbar-dark bg-brand navbar-expand-{$nav_breakpoint} d-none d-{$nav_breakpoint}-flex navbar-main navbar--hoverable-dropdowns">
		<div class="container-fluid">

			
			<div class="collapse navbar-collapse justify-content-center" id="mainNavDropdown">
				{render partial="shared/layout/header/nav_menu" menu="main_menu" nav_class="navbar-nav" dropdown_class="dropdown-menu--dark bg-brand dropdown-menu--transparent"}
			</div>
		</div>
	</nav>
	
	{if $controller=="main" && $action=="index" }
		<nav class="navbar navbar-dark bg-brand navbar-expand d-{$nav_breakpoint}-none navbar-main--mobile">
			<div class="collapse navbar-collapse">
				{render partial="shared/layout/header/nav_menu" menu="main_menu" enable_dropdown_menus=false nav_class="navbar-nav"}
			</div>
		</nav>
	{/if}
	
	{if $use_large_search_bar}
	{render partial="shared/layout/header/search_bar"}
	{/if}

</header>