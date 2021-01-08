{assign current_category ""}
{if $controller=="categories" && $action=="detail"}
	{assign current_category $category}
{/if}

{assign root Category::GetInstanceByCode("catalog")}
{assign tree CategoryTree::GetInstance($root,["visible" => true, "is_filter" => false])}

<ul class="nav accordion nav--sidebar" id="sidebar_menu">

	{foreach $tree as $node}
		{assign category $node->getCategory()}
		{assign active $current_category && $current_category->isDescendantOf($category)}

		<li class="nav-item">
			{if $node->hasChilds()}

				<a href="{link_to action="categories/detail" path=$node->getPath()}" class="nav-link{if $active} active{else} collapsed{/if}" id="sidebar_menu_item_{$node->getId()}" data-toggle="collapse" data-target="#sidebar_submenu_{$node->getId()}" aria-expanded="false" aria-controls="sidebar_submenu_{$node->getId()}">{$category->getName()}</a>

				{render partial="shared/layout/sidebar_nav_submenu" tree=$node active=$active}

			{else}

				<a href="{link_to action="categories/detail" path=$node->getPath()}" class="nav-link{if $active} active{/if}" id="sidebar_menu_item_{$node->getId()}">{$category->getName()}</a>

			{/if}
		</li>

	{/foreach}
	
</ul>
