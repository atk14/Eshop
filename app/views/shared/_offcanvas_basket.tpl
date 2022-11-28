<div id="offcanvas-basket" class="bs-offcanvas bs-offcanvas-right bg-light">
	<header class="bs-offcanvas-header bs-offcanvas-header--fixed-top">
		<button type="button" class="bs-offcanvas-close close" aria-label="Close" aria-expanded="false">{!"xmark"|icon}</button>
		<h4 class="bs-offcanvas-title">{t}Basket{/t}</h4>
	</header>
	<div class="bs-offcanvas-content">
		<div class="basket-content">

		</div>
		<div class="basket-loading">
			<div class="spinner-border text-secondary" role="status">
				<span class="sr-only">Loading...</span>
			</div>
			<p>{t}Loading{/t}</p>			
		</div>
		<div class="basket-error js--basket-error">
			Error pyco
		</div>
		<div class="basket-link">
			<a href="{link_to action="baskets/edit"}" class="btn btn-primary">{t}Basket{/t}</a>
		</div>
	</div>    
</div>
