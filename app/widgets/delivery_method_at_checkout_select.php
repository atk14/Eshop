<?php
/**
 * Vyrenderuje RadioSelect pro vyber dorucovaci metody.
 * Dorucovaci metoda muze poskytovat vyber vydejniho mista.
 *
 * Dedi SelectWithImages.
 *
 * Napsano tak, ze v choices jsou objekty
 *
 * 	$this->add_field("agreement",new ChoiceField(array(
 * 		"label" => "Do you agree?",
 * 		"widget" => new SelectWithImages(array(
 * 			"choices" => array(
 * 				"1" => Product,
 * 				"2" => Product,
 * 				"3" => Product,
 * 			),
 * 		))
 * 	)));
 *
 */
class DeliveryMethodAtCheckoutSelect extends SelectWithImages {

	function _renderer($name, $value, $attrs, $choices) {
		$output = array();
		$i = 0;
		foreach ($choices as $k => $v) {
			$ch = new DeliveryMethodInputWithBranch($name, $value, $attrs, $k, $v, $i);
			$ch->options = $this->options;

			$output[] = "<li class=\"list__item\" data-id=\"$k\">".$ch->render()."</li>";
			$i++;
		}
		$data_api_url = "";
		//$ch && ($data_api_url = Atk14Url::BuildLink(array("namespace" => "api", "controller" => "cards", "action" => "detail", "id" => $ch->object->getCard())));
		//return "<ul class=\"radios\" data-api_detail_url=\"".$data_api_url."\">\n".implode("\n", $output)."\n</ul>";
		return "<ul class=\"list list--radios list--radios--lg\">\n".implode("\n", $output)."\n</ul>";
	}
}


/**
 * Widget pro radio button pro zobrazeni dorucovaci metody s vybranym vydejnim mistem
 *
 */
class DeliveryMethodInputWithBranch extends RadioInputWithImage {

	public $options;

	function __construct($name, $value, $attrs, $key, $choice, $index) {
		$attrs["data-branch_needed"] = true;
		return parent::__construct($name, $value, $attrs, $key, $choice, $index);
	}

	function branchAddress() {
		if (is_null($this->options["controller"])) {
			return "";
		}
		$basket = $this->options["controller"]->basket;
		$current_dm = $this->object->dm;
		$basket_dm = $basket->getDeliveryMethod();

		$branch = null;

		if ($current_dm && $basket_dm && !is_null($current_dm->getDeliveryServiceId()) && ($current_dm->getDeliveryServiceId()===$basket_dm->getDeliveryServiceId())) {
			$branch = $basket->getDeliveryServiceBranch();
		}

		$out = smarty_function_render(array("partial" => "delivery_service_branch_field", "branch" => $branch, "delivery_method" => $current_dm), $this->options["controller"]->_get_smarty());
		return $out;
	}

	function render() {
		return parent::render().$this->branchAddress();
	}
}
