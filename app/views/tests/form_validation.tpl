<form action="/registrace/" method="post" id="form_users_create_new" class="form-horizontal" novalidate="novalidate">
  <fieldset>

    <div class="form-group form-group--id_login form-group--required">
      <label for="id_login" class="control-label">Uživatelské jméno (login) </label>
      <input maxlength="50" required="required" pattern="^[a-z0-9.-]+$" type="text" name="login"
        class="text form-control" id="id_login" />

      <div class="form-text help-block">Povolena jsou pouze písmena, číslice, tečky a pomlčky. Maximálně 50 znaků.</div>
      <div class="help-hint d-none" data-title="Příklady:" data-bs-title="Příklady:">
        <ul class="list pl-3">
          <li>john.doe</li>
          <li>samantha92</li>
        </ul>
      </div>
    </div>

    <div class="form-group form-group--id_gender_id_0 form-group--required">
      <label for="id_gender_id_0" class="control-label">Oslovení </label>
      <ul class="list list--radios">
        <li class="list__item">
          <div class="form-check"><input id="id_gender_id_0" class="form-check-input" type="radio" name="gender_id"
              value="1" /> <label class="form-check-label" for="id_gender_id_0"><span
                class="label__text">pan</span></label></div>
        </li>
        <li class="list__item">
          <div class="form-check"><input id="id_gender_id_1" class="form-check-input" type="radio" name="gender_id"
              value="2" /> <label class="form-check-label" for="id_gender_id_1"><span
                class="label__text">paní</span></label></div>
        </li>
        <li class="list__item">
          <div class="form-check"><input id="id_gender_id_2" class="form-check-input" type="radio" name="gender_id"
              value="3" /> <label class="form-check-label" for="id_gender_id_2"><span
                class="label__text">slečna</span></label></div>
        </li>
      </ul>
    </div>

    <div class="form-group form-group--id_firstname form-group--required">
      <label for="id_firstname" class="control-label">Jméno </label>
      <input maxlength="255" required="required" type="text" name="firstname" class="text form-control"
        id="id_firstname" />
    </div>
    <div class="form-group form-group--id_lastname form-group--required">
      <label for="id_lastname" class="control-label">Příjmení </label>
      <input maxlength="255" required="required" type="text" name="lastname" class="text form-control"
        id="id_lastname" />
    </div>

    <div class="form-group form-group--id_email form-group--required">
      <label for="id_email" class="control-label">E-mailová adresa </label>
      <input required="required" type="email" name="email" class="email text form-control" id="id_email" value="@" />
    </div>

    <div class="form-group form-group--id_company form-group--optional">
      <label for="id_company" class="control-label">Společnost <small class="tip tip--optional">(Volitelné)</small>
      </label>
      <input maxlength="255" type="text" name="company" class="text form-control" id="id_company" />
    </div>

    <div class="form-group form-group--id_company_number form-group--optional">
      <label for="id_company_number" class="control-label">IČ <small class="tip tip--optional">(Volitelné)</small>
      </label>
      <input type="text" name="company_number" class="text form-control" id="id_company_number" />
    </div>

    <div class="form-group form-group--id_vat_id form-group--optional">
      <label for="id_vat_id" class="control-label">DIČ <small class="tip tip--optional">(Volitelné)</small>
      </label>
      <input type="text" name="vat_id" class="text form-control" id="id_vat_id" />
    </div>

    <div class="form-group form-group--id_address_street form-group--required">
      <label for="id_address_street" class="control-label">Ulice a č.p. </label>
      <input maxlength="255" required="required" type="text" name="address_street" class="text form-control"
        id="id_address_street" />
    </div>

    <div class="form-group form-group--id_address_city form-group--required">
      <label for="id_address_city" class="control-label">Město </label>
      <input maxlength="255" required="required" type="text" name="address_city" class="text form-control"
        id="id_address_city" />
    </div>

    <div class="form-group form-group--id_address_zip form-group--required">
      <label for="id_address_zip" class="control-label">PSČ </label>
      <input required="required" type="text" name="address_zip" class="text form-control" id="id_address_zip" pattern="\d*" xxxxxpattern="^\s*?\d{5}(?:[-\s]\d{4})?\s*?$" />
    </div>

    <div class="form-group form-group--id_address_country form-group--required">
      <label for="id_address_country" class="control-label">Země </label>
      <select name="address_country" class="form-control" id="id_address_country" required>
        <option value="" selected="selected">-- vyberte zemi --</option>
        <option value="BE">Belgie</option>
        <option value="BG">Bulharsko</option>
        <option value="CZ">Česká republika</option>
        <option value="DK">Dánsko</option>
        <option value="EE">Estonsko</option>
        <option value="FI">Finsko</option>
        <option value="FR">Francie</option>
        <option value="IE">Irsko</option>
        <option value="IT">Itálie</option>
        <option value="LT">Litva</option>
        <option value="LV">Lotyšsko</option>
        <option value="HU">Maďarsko</option>
        <option value="DE">Německo</option>
        <option value="NL">Nizozemsko</option>
        <option value="PL">Polsko</option>
        <option value="PT">Portugalsko</option>
        <option value="AT">Rakousko</option>
        <option value="RO">Rumunsko</option>
        <option value="GR">Řecko</option>
        <option value="SK">Slovensko</option>
        <option value="SI">Slovinsko</option>
        <option value="ES">Španělsko</option>
        <option value="SE">Švédsko</option>
        <option value="GB">Velká Británie</option>
      </select>
    </div>

    <div class="form-group form-group--id_phone form-group--required">
      <label for="id_phone" class="control-label">Telefon </label>
      <input required="required" type="text" name="phone" class="text form-control" id="id_phone" value="+420 " />

      <div class="form-text help-block">Zadejte telefonní číslo ve formátu +420 605 123 456</div>
    </div>

    <div class="form-group form-group--id_password form-group--required">
      <label for="id_password" class="control-label">Heslo </label>
      <input class="form-control" maxlength="255" required="required" type="password" name="password"
        id="id_password" />
    </div>

    <div class="form-group form-group--id_password_repeat form-group--required">
      <label for="id_password_repeat" class="control-label">Heslo (opakování) </label>
      <input class="form-control" maxlength="255" required="required" type="password" name="password_repeat"
        id="id_password_repeat" />
    </div>

    <div class="form-group">
      <span class="button-container"><button type="submit" class="btn btn-primary">Registrovat</button></span>
    </div>
  </fieldset>

  <div>
    <input type="hidden" name="_csrf_token_" value="3f1f2acef050031b5453f668e3bc7a2dfccf1ca9" />
  </div>

</form>