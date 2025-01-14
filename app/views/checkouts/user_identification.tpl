{render partial="shared/checkout_navigation"}

{render partial="shared/layout/content_header" title=$page_title}

<div class="card-grid--login_options cards--login-options">
	<div class="card bg-light">
		<div class="card-body">
			<h2 class="title title--h4">{t}Už mám účet{/t}</h2>
		</div>
		<div class="card-footer">
			{render partial="shared/form" form=$login_form button_class="btn btn-primary"}
		</div>
	</div>
	<div class="card bg-light">
		<div class="card-body">
			<h2 class="title title--h4">{t}Zaregistrujte se{/t}</h2>
			<ul class="ul ul--base">
				<li>{t}osobní nastavení a zabezpečený přístup{/t}</li>
				<li>{t}rychlý a snadný nákup{/t}</li>
				<li>{t}rozdílná fakturační a dodací adresa{/t}</li>
			</ul>
		</div>
		<div class="card-footer">
			{a action="users/create_new" return_uri=$request->getUri() _class="btn btn-primary"}{t}Zaregistrujte se{/t}{/a}
		</div>
	</div>
	<div class="card bg-light">
		<div class="card-body">
			<h2 class="title title--h4">{t}Nákup bez registrace{/t}</h2>
		</div>
		<div class="card-footer">
			{a action="checkouts/set_billing_and_delivery_data" _class="btn btn-primary"}{t}Pokračovat bez registrace{/t}{/a}
		</div>
	</div>
</div>