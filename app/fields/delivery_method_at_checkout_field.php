<?php
/**
 * Pole pro vyber dorucovaci metody behem dokoncovani objednavky
 */
class DeliveryMethodAtCheckoutField extends ChoiceFieldWithImages {

	public $basket;

	function __construct($options = []){
		$options += [
			"basket" => null,
			"widget" => new DeliveryMethodAtCheckoutSelect($options),
		];

		$this->basket = $options["basket"];

		parent::__construct($options);
	}

	function getChoices($options) {
		list($delivery_methods,$payment_methods) = ShippingCombination::GetAvailableMethods4Basket($this->basket);

		$choices = [];
		foreach($delivery_methods as $o){
			$choices[$o->getId()] = new DeliveryMethodChoice($o, $options);
		}
		return $choices;
	}

	function clean($value) {
		list($err,$value) = parent::clean($value);

		if(!is_null($err) || is_null($value)){
			return [$err,$value];
		}

		if (is_null($_dm = DeliveryMethod::FindById($value))) {
			return [_("There is no such delivery method"), null];
		}

		# pokud je zvolena dorucovaci metoda s vyberem vydejniho mista,
		# tak kontrola kombinace dorucovaci sluzby a vydejniho mista, ze patri k sobe
		$delivery_method_data = $this->basket->getDeliveryMethodData();
		if (
			$_dm && $_dm->getDeliveryServiceId() &&
			(is_null($delivery_method_data) || ( $delivery_method_data && ($_dm->getDeliveryServiceId() != $delivery_method_data["delivery_service_id"])))
		) {
			return [_("Choose pickup place for selected delivery method"), null];
		}
		return [$err,$value];
	}
}

class DeliveryMethodChoice {

	public $options;
	public $dm;

	function __construct($dm, $options) {
		$this->options = $options;
		$this->dm = $dm;
	}

	function getLabel() {
		return $this->dm->getLabel();
	}

	function getHint() {
		return $this->dm->getDescription();
	}

	function getHintTitle() {
		return $this->dm->getTitle();
	}

	function getPrice() {
		$o = $this->dm;
		$rate = $this->options['currency_rate'];
		if ($this->options["display_prices"]!==true) {
			return null;
		}

		$basket = $this->options["basket"];
		$incl_vat = $basket->displayPricesInclVat();
		$delivery_countries = $basket->getDeliveryCountriesAllowed();

		$price = $incl_vat ? $o->getPriceInclVat() : $o->getPrice();
		if(is_null($price)){
			return null;
		}

		$price = $price / $rate;

		$lowest_price  = $incl_vat ? $o->getLowestPriceInclVat($delivery_countries) : $o->getLowestPrice($delivery_countries);
		$lowest_price  = $lowest_price / $rate;
		$highest_price = $incl_vat ? $o->getHighestPriceInclVat($delivery_countries) : $o->getHighestPrice($delivery_countries);
		$highest_price = $highest_price / $rate;
		$price = $lowest_price;
		if($price == 0.0 || $basket->freeShipping($this->dm)){
			$price = 0;
		}elseif($lowest_price!=$highest_price){
			$lowest = $this->_display_price($lowest_price);
			$highest = $this->_display_price($highest_price);
			//$price = "<span class=\"v-price--long\">" . sprintf(_("od %s do %s dle země doručení") . "</span>",$lowest,$highest);
			//$price = "<span class=\"v-price--long\">" . sprintf("%s &ndash; %s",$lowest,$highest) . "<br><small>"._("dle země doručení")."</small>" . "</span>";
			$price = "<span class=\"v-price--long\">" . sprintf(_("cena od %s"),$lowest) . "<br><small>"._("dle země doručení")."</small>" . "</span>";
		}else{
			$price = $this->_display_price($price);
		}
		return $price;
	}

	function getImage() {
		return $this->dm->getLogo();
	}

	function _display_price($price,$options = []){
		$basket = $this->options["basket"];
		$currency = $basket->getCurrency();

		$options += [
			"format" => "plain",
			"currency" => $currency,
			"show_decimals" => ($price !== (float)round($price)),
			"show_currency" => true,
		];

		$price = smarty_modifier_display_price($price,$options);
		$price = str_replace(" ",html_entity_decode("&nbsp;"),$price);
		return $price;
	}
}
