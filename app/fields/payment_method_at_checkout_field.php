<?php
/**
 * Pole pro vyber platebni metody behem dokoncovani objednavky
 */
class PaymentMethodAtCheckoutField extends ChoiceFieldWithImages {

	public $basket;

	function __construct($options = []){
		$options += [
			"basket" => null,
		];

		$this->basket = $options["basket"];

		parent::__construct($options);
	}

	function getChoices($options){
		list($delivery_methods,$payment_methods) = ShippingCombination::GetAvailableMethods4Basket($this->basket);

		$choices = [];
		foreach($payment_methods as $o){
			$choices[$o->getId()] = new PaymentMethodChoice($o, $options);
		}
		return $choices;
	}
}

class PaymentMethodChoice {

	public $options;
	public $pm;

  function __construct($pm, $options) {
    $this->options = $options;
    $this->pm = $pm;
  }

  function getLabel() {
		return $this->pm->getLabel();
    return $this->pm->getLabel()." (".join(", ",$this->pm->getRegions()).")";
  }

  function getHint() {
    return $this->pm->getDescription();
  }

  function getHintTitle() {
    return $this->pm->getTitle();
  }

	function getPrice() {
		if ($this->options["display_prices"]!==true) {
			return null;
		}

		$basket = $this->options["basket"];
		$currency = $basket ? $basket->getCurrency() : null;
		$incl_vat = $basket->displayPricesInclVat();

		$rate = $this->options['currency_rate'];
		$price = $incl_vat ? $this->pm->getPriceInclVat() : $this->pm->getPrice();
		$price = $price / $rate;
		if($price==0.0 || $this->options["free_shipping"]){
			$price = 0;
		}else{
			$price = smarty_modifier_display_price($price,["format" => "plain", "currency" => $currency]);
			$price = str_replace(" ",html_entity_decode("&nbsp;"),$price);
		}
		return $price;
	}

	function getImage() {
		return $this->pm->getLogo();
	}
}
