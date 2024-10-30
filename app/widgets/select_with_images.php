<?php
/**
 * Vyrenderuje RadioSelect pro vyber varianty i s obrazkem a cenou
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
class SelectWithImages extends RadioSelect {

	public $input_type = "radio";
	public $options;

	function __construct($options = array()){
		$options += array(
			"image_geometry" => "100x100x#ffffff",
			"price_fetcher" => null,
			"show_prices" => false,
			"show_sales" => false,
			"prices_with_vat" => true,
		);
		$this->options = $options;

		parent::__construct($options);
	}

	function _renderer($name, $value, $attrs, $choices) {
		$output = array();
		$i = 0;
		foreach ($choices as $k => $v) {
			$ch = new RadioInputWithImage($name, $value, $attrs, $k, $v, $i);
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
 * Widget pro radio button s obrazkem varianty produktu
 *
 */
class RadioInputWithImage {

	var $input_type = "radio";
	var $object;
	var $key;
	var $name;
	var $value;
	var $attrs;
	var $index;
	var $options;

	function __construct($name, $value, $attrs, $key, $choice, $index) {
		$this->object = $choice;
		$this->key = $key;
		$this->name = $name;
		$this->value = $value;
		$this->attrs = $attrs;
		$this->index = $index;
	}

	function is_checked() {
		return $this->value == $this->key;
	}

	function tag() {
		if (isset($this->attrs['id'])) {
			$this->attrs['id'] = $this->attrs['id'].'_'.$this->index;
		}
		$final_attrs = forms_array_merge($this->attrs, array(
			'type' => $this->input_type,
			'name' => $this->name,
			'value' => $this->key,
			'class' => "form-check-input",
		));
		if ($this->is_checked()) {
			$final_attrs['checked'] = 'checked';
		}

		return '<input'.flatatt($final_attrs).' />';
	}

	function image() {
		$image = $this->object->getImage();
		$title = forms_htmlspecialchars($this->object->getLabel());
		if ($image) {
			if(is_object($image)) {
				$image = $image->getUrl();
			}
			$p = new Pupiq($image,$api_key);
			return sprintf('<span class="label__image"><img src="%s" alt=""></span>',$p->getUrl($this->options["image_geometry"]));
		} else {
			return '';
		}
	}

	function caption() {
		return forms_htmlspecialchars($this->object->getLabel());
	}

	function hint() {
		Atk14Require::Helper("modifier.markdown");
		$hint_title = (string)$this->object->getHintTitle();
		$hint = (string)$this->object->getHint();
		$hint = smarty_modifier_markdown($hint);
		if(!strlen($hint_title) && !strlen($hint)) { return ''; };
		if($hint_title){
			$hint_title = '<span class="label__description__title">'.$hint_title.'</span>';
		}
		return '<div class="label__description">'.$hint_title.$hint.'</div>';
	}

	function price() {
		$price = $this->object->getPrice();
		if($price === null) { return ''; };
		if($price === 0) { return '<span class="label__price label__price--free">'._('Zdarma') . '</span>'; };
		return "<span class=\"label__price\">$price</span>";
	}

	function render() {
		$price = $this->price() ? " ".$this->price() : "";
		$attr = [
			// TODO: code review needed: tohle upravil Mattez
			"for" => $this->attrs['id'].'_'.$this->index,
			"class" => "form-check-label",
		];
		if( $this->image() ) {
			$form_check_class = " form-check--has-image";
		}
		return '<div class="form-check form-check--with-image' . $form_check_class . '">'.$this->tag().'<label'.flatatt($attr).'>'.$this->image() . '<span class="label__name">' . $this->caption() . '</span>' . $this->hint() . $price . '</label></div>';
	}
}
