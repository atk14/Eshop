Forms
=====
Form markup and styling is based on Bootstrap form system. In most cases, following Bootstrap forms documentation would lead to nice and correct results.
Passwords fields are autimatically enhanced with password reveal button.

## Form layouts

### Basic form 

[example]
<form action="#" method="post" id="form_logins_create_new" novalidate="novalidate" class="" role="form">
	<fieldset>
		<div class="form-group form-group--id_login form-group--required">
			<label for="id_login" class="control-label">Přihlásit se </label>
			<input maxlength="255" required="required" type="text" name="login" class="text form-control" id="id_login">
		</div>
		<div class="form-group form-group--id_password form-group--required">
			<label for="id_password" class="control-label">Heslo </label>
			<input class="form-control" maxlength="255" required="required" type="password" name="password" id="id_password">
		</div>
		<div class="form-group">
			<span class="button-container"><button type="submit" class="btn btn-primary">Přihlásit se</button></span>
		</div>
	</fieldset>
	<div>
		<input type="hidden" name="_csrf_token_" value="">
	</div>
</form>
[/example]

### Horizontal form
Horizontal forms differ from Bootstrap horizontal forms. To avoid using grid classes and to make markup more consistent with basic vertically stacked forms, we use CSS grid to create layout of <code>form-group</code> element (which wraps one form element or logical group of form elements like radio buttons). Basic form is converted to horizontal form simply by adding <code>form-horizontal</code> class to <code>form</code> element.

[example]
<form action="#" method="post" id="form_users_create_new" class="form-horizontal" novalidate="novalidate">
	<fieldset>
		<div class="form-group form-group--id_login form-group--required">
			<label for="id_login" class="control-label">Uživatelské jméno (login) </label>
			<input maxlength="50" required="required" pattern="^[a-z0-9.-]+$" type="text" name="login" class="text form-control" id="id_login" data-original-title="" title="">

			<div class="form-text help-block">Zadávejte pouze písmena, číslice, tečky a pomlčky. Max. délka je omezena na 50 znaků.</div>
			<div class="help-hint d-none" data-title="Příklady">
				<ul class="list pl-3">
					<li>john.doe</li>
					<li>samantha92</li>
				</ul>
			</div>
			<p class="alert alert-danger" style="display: none;">Username is already taken.</p>
		</div>
		<div class="form-group form-group--id_gender_id_0 form-group--required">
			<label for="id_gender_id_0" class="control-label">Oslovení </label>
			<ul class="list list--radios">
				<li class="list__item">
					<div class="form-check"><input id="id_gender_id_0" class="form-check-input" type="radio" name="gender_id" value="1"> <label class="form-check-label" for="id_gender_id_0"><span class="label__text">pan</span></label></div>
				</li>
				<li class="list__item">
					<div class="form-check"><input id="id_gender_id_1" class="form-check-input" type="radio" name="gender_id" value="2"> <label class="form-check-label" for="id_gender_id_1"><span class="label__text">paní</span></label></div>
				</li>
				<li class="list__item">
					<div class="form-check"><input id="id_gender_id_2" class="form-check-input" type="radio" name="gender_id" value="3"> <label class="form-check-label" for="id_gender_id_2"><span class="label__text">slečna</span></label></div>
				</li>
			</ul>
		</div>
		<div class="form-group form-group--id_firstname form-group--required">
			<label for="id_firstname" class="control-label">Jméno </label>
			<input maxlength="255" required="required" type="text" name="firstname" class="text form-control" id="id_firstname">
		</div>
		<div class="form-group form-group--id_lastname form-group--required">
			<label for="id_lastname" class="control-label">Příjmení </label>
			<input maxlength="255" required="required" type="text" name="lastname" class="text form-control" id="id_lastname">
		</div>
		<div class="form-group form-group--id_email form-group--required">
			<label for="id_email" class="control-label">E-mailová adresa </label>
			<input required="required" type="email" name="email" class="email text form-control" id="id_email" value="@">
		</div>
		<div class="form-group form-group--id_company form-group--optional">
			<label for="id_company" class="control-label">Společnost <small class="tip tip--optional">(Volitelné)</small>
			</label>
			<input maxlength="255" type="text" name="company" class="text form-control" id="id_company">
		</div>
		<div class="form-group form-group--id_company_number form-group--optional">
			<label for="id_company_number" class="control-label">IČ <small class="tip tip--optional">(Volitelné)</small>
			</label>
			<input type="text" name="company_number" class="text form-control" id="id_company_number">
		</div>
		<div class="form-group form-group--id_vat_id form-group--optional">
			<label for="id_vat_id" class="control-label">DIČ <small class="tip tip--optional">(Volitelné)</small>
			</label>
			<input type="text" name="vat_id" class="text form-control" id="id_vat_id">
		</div>
		<div class="form-group form-group--id_address_street form-group--required">
			<label for="id_address_street" class="control-label">Ulice a č.p. </label>
			<input maxlength="255" required="required" type="text" name="address_street" class="text form-control" id="id_address_street">
		</div>
		<div class="form-group form-group--id_address_city form-group--required">
			<label for="id_address_city" class="control-label">Město </label>
			<input maxlength="255" required="required" type="text" name="address_city" class="text form-control" id="id_address_city">
		</div>
		<div class="form-group form-group--id_address_state form-group--optional">
			<label for="id_address_state" class="control-label">Stát / Provincie / Kraj <small class="tip tip--optional">(Volitelné)</small>
			</label>
			<input maxlength="255" type="text" name="address_state" class="text form-control" id="id_address_state">
		</div>
		<div class="form-group form-group--id_address_zip form-group--required">
			<label for="id_address_zip" class="control-label">PSČ </label>
			<input required="required" type="text" name="address_zip" class="text form-control" id="id_address_zip">
		</div>
		<div class="form-group form-group--id_address_country form-group--required">
			<label for="id_address_country" class="control-label">Země </label>
			<select name="address_country" class="form-control" id="id_address_country">
				<option value="">-- země --</option>
			</select>
		</div>
		<div class="form-group form-group--id_phone form-group--required">
			<label for="id_phone" class="control-label">Telefon </label>
			<input required="required" type="text" name="phone" class="text form-control" id="id_phone" value="+420 ">
			<div class="form-text help-block">Telefonní číslo zadejte ve formátu +420 605 123 456</div>
		</div>
		<div class="form-group form-group--id_password form-group--required">
			<label for="id_password" class="control-label">Heslo </label>
			<input class="form-control" maxlength="255" required="required" type="password" name="password" id="id_password">
		</div>
		<div class="form-group form-group--id_password_repeat form-group--required">
			<label for="id_password_repeat" class="control-label">Heslo (opakování) </label>
			<input class="form-control" maxlength="255" required="required" type="password" name="password_repeat" id="id_password_repeat">
		</div>
		<div class="form-group">
			<span class="button-container"><button type="submit" class="btn btn-primary">Registrovat</button></span>
		</div>
	</fieldset>
	<div>
		<input type="hidden" name="_csrf_token_" value="">
	</div>
</form>
[/example]

### Inline search forms
Note: search form in navbar has slightly different markup, see Navbar section of this styleguide.
[example]
<form action="#" method="get" id="" class="form-inline">
	<label for="id_q" class="control-label mb-2 mb-sm-0 mr-sm-2">Hledat</label>
	<input type="search" name="q" value="" class="search text form-control mb-2 mb-sm-0 mr-sm-2" id="id_q">
	<button type="submit" class="btn btn-primary"> <i class="icon ion-ios-search-strong" title=""></i> Hledat</button>
</form>

<form class="form-inline" autocomplete="off">
	<input class="form-control" id="stores-filter__input" placeholder="Vyhledat prodejny">
	<button class="btn btn-link d-none" id="stores-filter__clear" tabindex="-1" type="reset"><span class="fas fa-times"></span></button>
	<button class="btn btn-link" id="stores-filter__submit" tabindex="-1" type="submit"><span class="fas fa-search"></span></button>
</form>
[/example]

## Validation feedback
Markup is the same for both basic and horizontal form.
[example]
<form class="form-horizontal">

	<div class="form-group form-group--id_email form-group--required form-group--has-error">
		<label for="id_email" class="control-label">E-mailová adresa </label>
		<input required="required" type="email" name="email" class="email text form-control" id="id_email" value="@">

		<div class="feedback feedback--invalid">
			<ul class="list">
				<li class="list__item"><span class="fas fa-exclamation-circle"></span> Zadejte platnou e-mailovou adresu.</li>
			</ul>
		</div>

	</div>
	
	<div class="form-group form-group--id_company form-group--optional form-group--is-valid">
		<label for="id_company" class="control-label">Společnost </label>
		<input maxlength="255" type="text" name="company" class="text form-control" id="id_company">

		<div class="feedback feedback--valid"><span class="sr-only">OK</span></div>

	</div>

</form>
[/example]

## Special form controls
Only items different from standard Bootstrap form controls are described here.

### Radios list
[example]
<form class="form-horizontal">
	<div class="form-group">
		<ul class="list list--radios">
			<li class="list__item">
				<div class="form-check"><input id="id_0" class="form-check-input" type="radio" name="gender_id" value="1"> <label class="form-check-label" for="id_0"><span class="label__text">pan</span></label></div>
			</li>
			<li class="list__item">
				<div class="form-check"><input id="id_1" class="form-check-input" type="radio" name="gender_id" value="2"> <label class="form-check-label" for="id_1"><span class="label__text">paní</span></label></div>
			</li>
			<li class="list__item">
				<div class="form-check"><input id="id_2" class="form-check-input" type="radio" name="gender_id" value="3"> <label class="form-check-label" for="id_2"><span class="label__text">slečna</span></label></div>
			</li>
		</ul>
	</div>
</form>
[/example]

### Radios list - larger

Modifier class <code>list--radios--lg</code> adds more vertical padding. Useful when list items contain images.

[example]
<form class="form-horizontal">
	<div class="form-group">
		<ul class="list list--radios list--radios--lg">
			<li class="list__item">
				<div class="form-check"><input id="id_0" class="form-check-input" type="radio" name="gender_id" value="1"> <label class="form-check-label" for="id_0"><span class="label__text">pan</span></label></div>
			</li>
			<li class="list__item">
				<div class="form-check"><input id="id_1" class="form-check-input" type="radio" name="gender_id" value="2"> <label class="form-check-label" for="id_1"><span class="label__text">paní</span></label></div>
			</li>
			<li class="list__item">
				<div class="form-check"><input id="id_2" class="form-check-input" type="radio" name="gender_id" value="3"> <label class="form-check-label" for="id_2"><span class="label__text">slečna</span></label></div>
			</li>
		</ul>
	</div>
</form>
[/example]

### Radios list with images
Useful for building shipping methods or payment methods selectors.
[example]
<form>
	<div class="form-group form-group--id_delivery_method_id_0 form-group--required">
		<label for="id_delivery_method_id_0" class="control-label">Vyberte způsob dopravy </label>
		<ul class="list list--radios list--radios--lg">
			<li class="list__item" data-id="11">
				<div class="form-check form-check--with-image form-check--has-image"><input id="id_delivery_method_id_0"
						data-branch_needed="1" type="radio" name="delivery_method_id" value="11" class="form-check-input"><label
						for="id_delivery_method_id_0" class="form-check-label"><span class="label__image"><img
								src="http://i.pupiq.net/i/6f/6f/505/30505/264x264/yB8nvW_100x100xffffff_d74d1797ac34f370.png"
								alt=""></span><span class="label__name">Česká Pošta - Balík do ruky (platba předem)</span>
						<div class="label__description">
							<p>I'm baby before they sold out hoodie mukbang, biodiesel YOLO raw denim coloring book palo santo offal
								cliche pork belly af jianbing leggings cupping. Try-hard subway tile raw denim, schlitz chambray shaman
								woke four dollar toast. Gorpcore mustache bicycle rights scenester readymade ethical, irony bespoke green
								juice. Fingerstache tbh letterpress JOMO street art. Church-key vinyl shoreditch plaid.</p>
						</div> <span class="label__price">90,00&nbsp;Kč</span>
					</label></div>
			</li>
			<li class="list__item" data-id="12">
				<div class="form-check form-check--with-image form-check--has-image"><input id="id_delivery_method_id_1"
						data-branch_needed="1" type="radio" name="delivery_method_id" value="12" class="form-check-input"><label
						for="id_delivery_method_id_1" class="form-check-label"><span class="label__image"><img
								src="http://i.pupiq.net/i/6f/6f/505/30505/264x264/yB8nvW_100x100xffffff_d74d1797ac34f370.png"
								alt=""></span><span class="label__name">Česká Pošta - Balík do ruky (dobírka)</span> <span
							class="label__price">120,00&nbsp;Kč</span></label></div>
			</li>
			<li class="list__item" data-id="15">
				<div class="form-check form-check--with-image form-check--has-image"><input id="id_delivery_method_id_2"
						data-branch_needed="1" type="radio" name="delivery_method_id" value="15" class="form-check-input"><label
						for="id_delivery_method_id_2" class="form-check-label"><span class="label__image"><img
								src="http://i.pupiq.net/i/6f/6f/4c3/304c3/244x244/OVTx3E_100x100xffffff_c9b7be1166b0d09b.png"
								alt=""></span><span class="label__name">Česká Pošta - Balíkovna (platba předem)</span> <span
							class="label__price">55,00&nbsp;Kč</span></label></div>
				<div class="delivery_service_branch">
					<span class="branch_button"><a href="/cs/delivery_service_branches/set_branch/?delivery_method_id=15"
							class="btn btn-outline-secondary btn-xs remote_link" data-remote="true">zvolit výdejní místo</a></span>
					<span class="branch_address"></span>
					<div class="clearfix"></div>
				</div>
			</li>
			<li class="list__item">
				<div class="form-check form-check--with-image form-check--has-image"><input id="id_delivery_method_id_5"
						data-branch_needed="1" type="radio" name="delivery_method_id" value="18" class="form-check-input"><label
						for="id_delivery_method_id_5" class="form-check-label"><span class="label__image"><img
								src="http://i.pupiq.net/i/6f/6f/4c4/304c4/237x237/d2qbTL_100x100xffffff_422f7b42f3b751e4.png"
								alt=""></span><span class="label__name">Česká Pošta - Balík na poštu (dobírka)</span> <span
							class="label__price">127,00&nbsp;Kč</span></label></div>
				<div class="delivery_service_branch">
					<span class="branch_button"><a href="/cs/delivery_service_branches/set_branch/?delivery_method_id=18"
							class="btn btn-outline-secondary btn-xs remote_link" data-remote="true">zvolit výdejní místo</a></span>
					<span class="branch_address"></span>
					<div class="clearfix"></div>
				</div>
			</li>
			<li class="list__item" data-id="19">
				<div class="form-check form-check--with-image form-check--has-image"><input id="id_delivery_method_id_6"
						data-branch_needed="1" type="radio" name="delivery_method_id" value="19" class="form-check-input"><label
						for="id_delivery_method_id_6" class="form-check-label"><span class="label__image"><img
								src="http://i.pupiq.net/i/6f/6f/4c5/304c5/800x800/TJbgui_100x100xffffff_877bc8d10f859fbf.png"
								alt=""></span><span class="label__name">Zásilkovna (platba předem)</span>
						<div class="label__description"><span class="label__description__title">Test heading</span></div> <span
							class="label__price">65,00&nbsp;Kč</span>
					</label></div>
				<div class="delivery_service_branch">
					<span class="branch_button"><a href="/cs/delivery_service_branches/set_branch/?delivery_method_id=19"
							class="btn btn-outline-secondary btn-xs remote_link" data-remote="true">zvolit výdejní místo</a></span>
					<span class="branch_address"></span>
					<div class="clearfix"></div>
				</div>
			</li>
			<li class="list__item" data-id="20">
				<div class="form-check form-check--with-image form-check--has-image"><input id="id_delivery_method_id_7"
						data-branch_needed="1" type="radio" name="delivery_method_id" value="20" class="form-check-input"><label
						for="id_delivery_method_id_7" class="form-check-label"><span class="label__image"><img
								src="http://i.pupiq.net/i/6f/6f/4c5/304c5/800x800/TJbgui_100x100xffffff_877bc8d10f859fbf.png"
								alt=""></span><span class="label__name">Zásilkovna (dobírka)</span>
						<div class="label__description">
							<span class="label__description__title">Test heading</span>
							<p>I'm baby seitan offal austin stumptown cred pour-over lo-fi raclette. Live-edge umami iPhone gastropub,
								pour-over flannel pug yuccie. Forage vegan flannel, poke kinfolk yuccie vaporware.</p>

							<ul>
								<li>Franzen cliche artisan subway tile hella you probably haven't heard of them.</li>
								<li>Salvia sustainable la croix poke letterpress squid.</li>
								<li>Listicle health goth keffiyeh fanny pack raclette godard chambray tumblr put a bird on it meditation
									selfies authentic biodiesel.</li>
							</ul>
						</div> <span class="label__price">85,00&nbsp;Kč</span>
					</label></div>
				<div class="delivery_service_branch">
					<span class="branch_button"><a href="/cs/delivery_service_branches/set_branch/?delivery_method_id=20"
							class="btn btn-outline-secondary btn-xs remote_link" data-remote="true">zvolit výdejní místo</a></span>
					<span class="branch_address"></span>
					<div class="clearfix"></div>
				</div>
			</li>
			<li class="list__item" data-id="13">
				<div class="form-check form-check--with-image"><input id="id_delivery_method_id_8" data-branch_needed="1"
						type="radio" name="delivery_method_id" value="13" class="form-check-input"><label
						for="id_delivery_method_id_8" class="form-check-label"><span class="label__name">Osobní převzetí na
							prodejně</span> <span class="label__price label__price--free">Zdarma</span></label></div>
			</li>
		</ul>
	</div>
</form>
[/example]

### Quantity widget
Widget for setting quantity of items. You must provide JavaScript for handling button clicks. Also available in smaller variation by adding <code>--sm</code> modifier. Small variation is also available with non-editable number display.  

[example]
<div class="quantity-widget js-spinner js-stepper">
	<button tabindex="-1" type="button" data-spinner-button="down" class="btn btn-secondary" title="Sniž objednané množství">-</button>
	<input step="1" min="1" max="106" class="form-control form-control-number order-quantity-input js-order-quantity-input" aria-label="Množství" required="required" id="id_amount_45" type="number" name="amount" value="4">
	<button tabindex="-1" type="button" data-spinner-button="up" class="btn btn-secondary" title="Zvyš objednané množství">+</button>&nbsp;ks
</div>

<div class="quantity-widget quantity-widget--sm js-spinner js-stepper mt-3">
	<button tabindex="-1" type="button" data-spinner-button="down" class="btn btn-secondary" title="Sniž objednané množství">-</button>
	<input step="1" min="1" max="106" class="form-control form-control-number order-quantity-input js-order-quantity-input" aria-label="Množství" required="required" id="id_amount_45" type="number" name="amount" value="1">
	<button tabindex="-1" type="button" data-spinner-button="up" class="btn btn-secondary" title="Zvyš objednané množství">+</button>&nbsp;ks
</div>

<div class="quantity-widget quantity-widget--sm js-spinner js-stepper mt-3">
	<button tabindex="-1" type="button" data-spinner-button="down" class="btn btn-outline-secondary" title="Sniž objednané množství">-</button>
	<span class="quantity-widget__number">4 ks</span>
	<button tabindex="-1" type="button" data-spinner-button="up" class="btn btn-outline-secondary" title="Zvyš objednané množství">+</button>
</div>
[/example]

### Colored checkboxes and radios

The same syntax as Bootstrap custom checkboxes and radios, just add <code>custom-control--color</code> class and appropriate background and text color styles. This component is also available in admin.

[example]
<div class="custom-control custom-radio custom-control--color bg-primary text-contrast-primary">
  <input type="radio" id="customRadio1" name="customRadio1" class="custom-control-input" checked>
  <label class="custom-control-label" for="customRadio1">Toggle this custom radio</label>
</div>

<div class="custom-control custom-radio custom-control--color text-dark">
  <input type="radio" id="customRadio2" name="customRadio1" class="custom-control-input">
  <label class="custom-control-label" for="customRadio2">Or toggle this other custom radio</label>
</div>
<div class="custom-control custom-radio custom-control--color bg-success text-contrast-success">
  <input type="radio" id="customRadio3" name="customRadio1" class="custom-control-input">
  <label class="custom-control-label" for="customRadio3">Or toggle this other custom radio</label>
</div>

<div class="custom-control custom-checkbox custom-control--color bg-info text-contrast-info">
  <input type="checkbox" class="custom-control-input" id="customCheck1" checked>
  <label class="custom-control-label" for="customCheck1">Check this custom checkbox</label>
</div>

<div class="custom-control custom-checkbox custom-control--color text-light" style="background-color: palevioletred;">
  <input type="checkbox" class="custom-control-input" id="customCheck2">
  <label class="custom-control-label" for="customCheck2">Check this custom checkbox</label>
</div>

<p class="mt-4">Or display them inline using class like <code>d-inline-block</code>:</p>

<div class="custom-control custom-radio custom-control--color d-inline-block bg-primary text-contrast-primary">
  <input type="radio" id="customRadio4" name="customRadio2" class="custom-control-input" checked>
  <label class="custom-control-label" for="customRadio4">Toggle</label>
</div>

<div class="custom-control custom-radio custom-control--color d-inline-block text-dark">
  <input type="radio" id="customRadio5" name="customRadio2" class="custom-control-input">
  <label class="custom-control-label" for="customRadio5">Or toggle</label>
</div>
<div class="custom-control custom-radio custom-control--color d-inline-block bg-success text-contrast-success">
  <input type="radio" id="customRadio6" name="customRadio2" class="custom-control-input">
  <label class="custom-control-label" for="customRadio6">Or toggle</label>
</div>
<div class="custom-control custom-radio custom-control--color d-inline-block bg-info text-contrast-info">
  <input type="radio" id="customRadio7" name="customRadio2" class="custom-control-input">
  <label class="custom-control-label" for="customRadio7">Or toggle</label>
</div>

<p class="mt-4">Or put them inside flexbox container:</p>

<div style="display: flex; flex-wrap: wrap">
	
	<div class="custom-control custom-checkbox custom-control--color bg-primary text-contrast-primary">
		<input type="checkbox" id="customCheck3" class="custom-control-input" checked>
		<label class="custom-control-label" for="customCheck3">Toggle</label>
	</div>

	<div class="custom-control custom-checkbox custom-control--color bg-warning text-contrast-warning">
		<input type="checkbox" id="customCheck4" class="custom-control-input" checked>
		<label class="custom-control-label" for="customCheck4">Or toggle</label>
	</div>
	
	<div class="custom-control custom-checkbox custom-control--color bg-success text-contrast-success">
		<input type="checkbox" id="customCheck5" class="custom-control-input">
		<label class="custom-control-label" for="customCheck5">Or toggle</label>
	</div>
	
	<div class="custom-control custom-checkbox custom-control--color bg-info text-contrast-info">
		<input type="checkbox" id="customCheck6" class="custom-control-input">
		<label class="custom-control-label" for="customCheck6">Or toggle</label>
	</div>
	
</div>

<p class="mt-4">Checkbox also supports indeterminate state:</p>

<div class="custom-control custom-checkbox custom-control--color bg-info text-contrast-info">
	<input type="checkbox" id="customCheck7" class="custom-control-input">
	<label class="custom-control-label" for="customCheck7">Indeterminate</label>
</div>

<script>
	var checkbox = document.getElementById("customCheck7");
	checkbox.indeterminate = true;
</script>

<p class="mt-4">Color checkbox and radio are easily sized by setting font-size:</p>

<div class="custom-control custom-checkbox custom-control--color bg-info text-contrast-info d-inline-block" style="font-size: 0.875rem;">
	<input type="checkbox" id="customCheck8" class="custom-control-input">
	<label class="custom-control-label" for="customCheck8">Small</label>
</div>

<div class="custom-control custom-checkbox custom-control--color bg-success text-contrast-success d-inline-block" style="font-size: 1.25rem;">
	<input type="checkbox" id="customCheck9" class="custom-control-input">
	<label class="custom-control-label" for="customCheck9">Big</label>
</div>

[/example]

